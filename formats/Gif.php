<?php

namespace images\manipulate\formats;

use images\manipulate\ImageResource;

class Gif extends ImageResource
{
	public function fromFile(string $src_path): void
	{
		$this->resource = imagecreatefromgif($src_path);
	}

	public function save(string $dest_path = null, $quality = null): bool
	{
		return imagegif($this->resource, $dest_path);
	}

	public function header(): void
	{
		header('Content-Type: image/gif');
	}
}