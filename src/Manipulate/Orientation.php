<?php

namespace zoibana\Images\Manipulate;

use zoibana\Images\ImageResource;

/**
 * Fix orientation
 */
class Orientation
{
	private $sourceImage;
	private $exif;

	public function __construct(ImageResource $image)
	{
		$this->sourceImage = $image;
		$this->exif = function_exists('exif_read_data') && $image->getImageType() === IMAGETYPE_JPEG ?
			exif_read_data($image->getResource()) : [];
	}

	public function rotate()
	{
		$orientation = $this->exif['Orientation'] ?? $this->exif['COMPUTED']['Orientation'] ?? 0;
		$resource = $this->sourceImage->getResource();

		if ($orientation) {
			switch ($orientation) {
				case 3:
					$resource = imagerotate($resource, 180, 0);
					break;
				case 6:
					$resource = imagerotate($resource, -90, 0);
					break;
				case 8:
					$resource = imagerotate($resource, 90, 0);
					break;
			}
		}

		return $resource;
	}
}