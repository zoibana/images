<?php

namespace zoibana\Images\Manipulate;

use zoibana\Images\ImageResource;

/**
 *
 *                   $sourceImage                                   $destImage
 *      +------------+---------------------------------+   +------------+--------------------+
 *      |            |                                 |   |            |                    |
 *      |            |                                 |   |         $destY                  |
 *      |            |                                 |   |            |                    |
 *      |          $srcY                               |   +-- $destX --+----$destWidth----+ |
 *      |            |                                 |   |            |                  | |
 *      |            |                                 |   |            |    Resampled     | |
 *      |            |                                 |   |            |                  | |
 *      +--- $srcX --+------ $srcWidth -------+        |   |       $destHeight             | |
 *      |            |                        |        |   |            |                  | |
 *      |            |                        |        |   |            |                  | |
 *      |            |                        |        |   |            |                  | |
 *      |            |                        |        |   |            +------------------+ |
 *      |            |        Sample          |        |   |                                 |
 *      |            |                        |        |   |                                 |
 *      |            |                        |        |   |                                 |
 *      |       $srcHeight                    |        |   |                                 |
 *      |            |                        |        |   +---------------------------------+
 *      |            |                        |        |
 *      |            |                        |        |
 *      |            +------------------------+        |
 *      |                                              |
 *      |                                              |
 *      +----------------------------------------------+
 *
 */
class Resize
{
	public const ACTION_NONE = 0;
	public const ACTION_CROP = 1;
	public const ACTION_SCALE = 2;
	public const ACTION_SCALE_WIDTH = 3;
	public const ACTION_SCALE_HEIGHT = 4;

	public static $actionMap = [
		self::ACTION_NONE => 'actionNone',
		self::ACTION_CROP => 'actionCrop',
		self::ACTION_SCALE => 'actionScale',
		self::ACTION_SCALE_WIDTH => 'actionScaleWidth',
		self::ACTION_SCALE_HEIGHT => 'actionScaleHeight',
	];

	/** @var ImageResource */
	protected $sourceImage;

	/** @var ImageResource */
	protected $destImage;
	protected $srcWidth;
	protected $srcHeight;
	protected $srcX = 0;
	protected $srcY = 0;
	protected $destWidth;
	protected $destHeight;
	protected $destX = 0;
	protected $destY = 0;
	protected $thumbWidth;
	protected $thumbHeight;
	protected $outWidth;
	protected $outHeight;

	/**
	 * Resize constructor.
	 *
	 * @param ImageResource $image
	 */
	public function __construct(ImageResource $image)
	{
		$this->sourceImage = $image;
		[$this->srcWidth, $this->srcHeight] = $this->sourceImage->getSizes();
	}

	/**
	 * @param $dest_width
	 * @param $dest_height
	 * @param null $action
	 *
	 * @return ImageResource
	 */
	public function process($dest_width, $dest_height, $action = null): ImageResource
	{
		$this->destWidth = $dest_width;
		$this->destHeight = $dest_height;

		$this->thumbWidth = $dest_width;
		$this->thumbHeight = $dest_height;

		$this->outWidth = $this->srcWidth;
		$this->outHeight = $this->srcHeight;

		$action = $action ?: self::ACTION_CROP;

		/** @var ImageResource $class */
		$class = get_class($this->sourceImage);
		$actionCallback = self::$actionMap[$action];
		$this->{$actionCallback}();

		$this->destImage = $class::create($this->thumbWidth, $this->thumbHeight);

		// preserve transparency
		$this->preserveTransparency();

		$white = imagecolorallocate($this->destImage->getResource(), 255, 255, 255);
		imagefilledrectangle($this->destImage->getResource(), 0, 0, $dest_width, $dest_height, $white);

		imagecopyresampled(
			$this->destImage->getResource(),
			$this->sourceImage->getResource(),
			$this->destX,
			$this->destY,
			$this->srcX,
			$this->srcY,
			$this->thumbWidth,
			$this->thumbHeight,
			$this->outWidth,
			$this->outHeight
		);

		return $this->destImage;
	}

	/**
	 * Keep transparency if image supports it
	 *
	 * @return void
	 */
	protected function preserveTransparency(): void
	{
		// Transparency for PNG images
		if ($this->destImage->getImageType() === IMAGETYPE_PNG) {
			imagealphablending($this->destImage->getResource(), false);
			imagesavealpha($this->destImage->getResource(), true);
		}

		// Transparency for GIF images
		if ($this->destImage->getImageType() === IMAGETYPE_GIF) {
			$transparent_color = null;
			$transparent_index = imagecolortransparent($this->destImage->getResource());
			if ($transparent_index >= 0 && $transparent_index < imagecolorstotal($this->destImage->getResource())) {
				$transparent_color = imagecolorsforindex($this->destImage->getResource(), $transparent_index);
			}

			if (null !== $transparent_color) {
				$transparent_new_color = imagecolorallocate($this->destImage->getResource(), $transparent_color['red'], $transparent_color['green'], $transparent_color['blue']);
				$transparent_new_index = imagecolortransparent($this->destImage->getResource(), $transparent_new_color);
				imagefill($this->destImage->getResource(), 0, 0, $transparent_new_index);  // fill the new image with the transparent color
			}
		}
	}

	protected function actionNone(): void
	{
		$this->thumbWidth = $this->srcWidth;
		$this->thumbHeight = $this->srcHeight;
	}

	protected function actionCrop(): void
	{
		$aspect_src = $this->srcWidth / $this->srcHeight;
		$aspect_dest = $this->destWidth / $this->destHeight;

		if ($aspect_src >= $aspect_dest) {
			$this->outHeight = $this->srcHeight;
			$this->outWidth = ceil(($this->outHeight * $this->destWidth) / $this->destHeight);
			$this->srcX = ceil(($this->srcWidth - $this->outWidth) / 2);
			$this->srcY = 0;
		} else {
			$this->outWidth = $this->srcWidth;
			$this->outHeight = ceil(($this->outWidth * $this->destHeight) / $this->destWidth);
			$this->srcY = ceil(($this->srcHeight - $this->outHeight) / 2);
			$this->srcX = 0;
		}
	}

	protected function actionScale(): void
	{
		$aspect_source = $this->srcWidth / $this->srcHeight;
		$aspect_dest = $this->destWidth / $this->destHeight;

		if ($aspect_source > $aspect_dest) {
			$this->thumbWidth = $this->destWidth;
			$this->thumbHeight = $this->destWidth / $aspect_source;
		} else {
			$this->thumbHeight = $this->destHeight;
			$this->thumbWidth = $this->destHeight * $aspect_source;
		}
	}

	protected function actionScaleWidth(): void
	{
		$this->thumbWidth = $this->destWidth;
		$this->thumbHeight = ($this->srcHeight * $this->destWidth) / $this->srcWidth;

		// If image less than passed width don't change dimensions
		if ($this->srcWidth < $this->destWidth) {
			$this->thumbWidth = $this->srcWidth;
			$this->thumbHeight = $this->srcHeight;
		}
	}

	protected function actionScaleHeight(): void
	{
		$this->thumbWidth = ($this->srcWidth * $this->destHeight) / $this->srcHeight;
		$this->thumbHeight = $this->destHeight;

		// If image less than passed height don't change dimensions
		if ($this->srcHeight < $this->destHeight) {
			$this->thumbWidth = $this->srcWidth;
			$this->thumbHeight = $this->srcHeight;
		}
	}

}