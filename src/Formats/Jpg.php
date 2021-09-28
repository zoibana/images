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
		return imagejpeg($this->resource, $dest_path, $quality);
	}

	public function header(): void
	{
		header('Content-Type: image/jpeg');
	}
}