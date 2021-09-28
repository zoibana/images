<?php

namespace zoibana\Images;

use zoibana\Images\Exceptions\SourceImageFileNotFoundException;
use zoibana\Images\Formats\Gif;
use zoibana\Images\Formats\Jpg;
use zoibana\Images\Formats\Png;
use zoibana\Images\Formats\Webp;
use zoibana\Images\Manipulate\Resize;
use zoibana\Images\Manipulate\Scale;

abstract class ImageResource implements ImageFormatInterface
{
	protected $source_file;
	protected $source_imagetype;
	protected $resource;

	protected static $formats = [
		IMAGETYPE_GIF => Gif::class,
		IMAGETYPE_JPEG => Jpg::class,
		IMAGETYPE_PNG => Png::class,
		IMAGETYPE_WEBP => Webp::class,
	];

	/**
	 * @param $width
	 * @param $height
	 * @return ImageResource
	 */
	public static function create($width, $height): ImageResource
	{
		$image = new Png();
		$image->resource = imagecreatetruecolor($width, $height);
		$image->source_imagetype = IMAGETYPE_PNG;

		return $image;
	}

	/**
	 * @param string $source_file
	 * @throws SourceImageFileNotFoundException
	 */
	public function setSourceFile(string $source_file): void
	{
		if (!file_exists($source_file)) {
			throw new SourceImageFileNotFoundException();
		}

		$this->source_file = $source_file;
		$this->source_imagetype = static::imageType($this->source_file);
		$this->fromFile($this->source_file);
	}

	public function setResource($resource): void
	{
		$this->resource = $resource;
	}

	public function getResource()
	{
		return $this->resource;
	}

	public function saveAs(int $imagetype, string $dest_path, $quality = null): bool
	{
		$this->resource = $this->setImagetype($imagetype);
		return $this->save($dest_path, $quality);
	}

	/**
	 * @param int $imagetype
	 * @return ImageFormatInterface|null
	 */
	public function setImagetype(int $imagetype): ?ImageFormatInterface
	{
		$class = static::$formats[$imagetype] ?? null;
		if ($class) {
			/** @var ImageFormatInterface $imgResource */
			$imgResource = new $class();
			$imgResource->source_file = $this->source_file;
			$imgResource->setResource($this->resource);

			return $imgResource;
		}

		return null;
	}

	public static function imageType(string $file): int
	{
		return getimagesize($file)[2];
	}

	public function getImageType(): int
	{
		return $this->source_imagetype;
	}

	public function getSourceFile(): ?string
	{
		return $this->source_file;
	}

	public function display(): bool
	{
		$this->header();
		return $this->save();
	}

	/**
	 * @return array
	 */
	public function getSizes(): array
	{
		return [imagesx($this->resource), imagesy($this->resource)];
	}

	/**
	 * @param string $file
	 * @return ImageResource|null
	 * @throws SourceImageFileNotFoundException
	 */
	public static function createFromFile(string $file): ?ImageResource
	{
		$class = static::$formats[static::imageType($file)] ?? null;

		if ($class) {
			/** @var ImageResource $imgResource */
			$imgResource = new $class();
			$imgResource->setSourceFile($file);

			return $imgResource;
		}

		return null;
	}


	/**
	 * @param $width
	 * @param $height
	 * @param null $action
	 *
	 * @return ImageResource
	 */
	public function resize($width, $height, $action = null): ImageResource
	{
		$resizer = new Resize($this);
		$resizedImageResource = $resizer->process($width, $height, $action);
		$this->setResource($resizedImageResource->getResource());
		return $this;
	}

	/**
	 * @param $dest_width
	 * @param $dest_height
	 *
	 * @return ImageResource
	 */
	public function scale($dest_width, $dest_height): ImageResource
	{
		$scaler = new Scale($this);
		$scaledImageResource = $scaler->process($dest_width, $dest_height);
		$this->setResource($scaledImageResource->getResource());
		return $this;
	}

	public function destroy(): void
	{
		if (is_resource($this->resource)) {
			imagedestroy($this->resource);
		}
	}
}