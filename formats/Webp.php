<?php

namespace images\manipulate\formats;

use images\manipulate\ImageResource;

class Webp extends ImageResource
{
	public function fromFile(string $src_path): void
	{
		$this->resource = imagecreatefromwebp($src_path);
	}

	public function save(string $dest_path = null, $quality = 70): bool
	{
		return imagewebp($this->resource, $dest_path, $quality);
	}

	public function header(): void
	{
		header('Content-Type: image/webp');
	}
}