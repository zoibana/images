<?php

namespace zoibana\Images\Formats;

use zoibana\Images\ImageResource;

class Png extends ImageResource
{
	public function fromFile(string $src_path): void
	{
		$this->resource = imagecreatefrompng($src_path);

		imagealphablending($this->resource, false);
		imagesavealpha($this->resource, true);
	}

	public function save(string $dest_path = null, $quality = null): bool
	{
		imageinterlace($this->resource, false);
		return imagepng($this->resource, $dest_path, $quality);
	}

	public function header(): void
	{
		header('Content-Type: image/png');
	}
}