<?php

namespace zoibana\Images\Manipulate;

use zoibana\Images\ImageResource;

class Scale
{
	protected $image;

	/**
	 * Scale constructor.
	 *
	 * @param ImageResource $image
	 */
	public function __construct(ImageResource $image)
	{
		$this->image = $image;
	}

	/**
	 * @param $dest_width
	 * @param $dest_height
	 *
	 * @return ImageResource
	 */
	public function process($dest_width, $dest_height): ImageResource
	{
		[$src_width, $src_height] = $this->image->getSizes();

		if ($src_width < $dest_width || $src_height < $dest_height) {
			return $this->image;
		}

		$aspect_source = $src_width / $src_height;
		$aspect_dest = $dest_width / $dest_height;

		if ($aspect_source > $aspect_dest) {
			$width = $dest_width;
			$height = $dest_width / $aspect_source;
		} else {
			$height = $dest_height;
			$width = $dest_height * $aspect_source;
		}

		/** @var ImageResource $class */
		$class = get_class($this->image);
		$new_image = $class::create($width, $height);

		imagecopyresampled($new_image->getResource(), $this->image->getResource(), 0, 0, 0, 0, $width, $height, $src_width, $src_height);

		return $new_image;
	}
}