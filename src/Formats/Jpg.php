<?php

namespace zoibana\Images\Formats;

use zoibana\Images\ImageResource;

class Jpg extends ImageResource
{
	public function fromFile(string $src_path): void
	{
		$this->resource = imagecreatefromjpeg($src_path);
	}

	public function save(string $dest_path = null, $quality = null): bool
	{
		// quality could be integer in range 0-100. default = -1 ~= 75
		return imagejpeg($this->resource, $dest_path, $quality ?? -1);
	}

	public function header(): void
	{
		header('Content-Type: image/jpeg');
	}
}