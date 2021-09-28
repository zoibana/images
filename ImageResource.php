<?php

namespace images\manipulate;

use Exception;
use images\manipulate\formats\Gif;
use images\manipulate\formats\Jpg;
use images\manipulate\formats\Png;
use images\manipulate\formats\Webp;
use images\manipulate\resize\Resize;
use images\manipulate\resize\Scale;

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
	 * @return \images\manipulate\ImageResource
	 */
	public static function create($width, $height): ImageResource
	{
		$image = new static;
		$image->resource = imagecreatetruecolor($width, $height);
		$image->source_imagetype = IMAGETYPE_PNG;

		return $image;
	}

	/**
	 * @throws \Exception
	 */
	public function setSourceFile(string $source_file): void
	{
		if (!file_exists($source_file)) {
			throw new Exception('Image file is not found');
		}

		$this->source_file = $source_file;
		$this->source_imagetype = static::getImageType($this->source_file);
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

	public static function getImageType(string $file): int
	{
		return getimagesize($file)[2];
	}

	public function imageType(): int
	{
		return $this->source_imagetype;
	}

	public function getSourceFile(): ?string
	{
		return $this->source_file;
	}

	public function display(): bool
	{
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
	 * @return \images\manipulate\ImageResource|null
	 * @throws \Exception
	 */
	public static function createFromFile(string $file): ?ImageResource
	{
		$class = static::$formats[static::getImageType($file)] ?? null;

		if ($class) {
			/** @var \images\manipulate\ImageResource $imgResource */
			$imgResource = new $class();
			$imgResource->setSourceFile($file);

			return $imgResource;
		}

		return null;
	}

	/**
	 * Меняем формат изображения
	 *
	 * @param int $imagetype
	 * @return \images\manipulate\ImageFormatInterface|null
	 */
	public function setImagetype(int $imagetype): ?ImageFormatInterface
	{
		$class = static::$formats[$imagetype] ?? null;
		if ($class) {
			/** @var \images\manipulate\ImageFormatInterface $imgResource */
			$imgResource = new $class();
			$imgResource->source_file = $this->source_file;
			$imgResource->setResource($this->resource);

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
		return (new Resize($this))->process($width, $height, $action);
	}

	/**
	 * @param $dest_width
	 * @param $dest_height
	 *
	 * @return ImageResource
	 */
	public function scale($dest_width, $dest_height): ImageResource
	{
		return (new Scale($this))->process($dest_width, $dest_height);
	}

	public function __destruct()
	{
		$this->destroy();
	}

	public function destroy(): void
	{
		if (is_resource($this->resource)) {
			imagedestroy($this->resource);
		}
	}
}