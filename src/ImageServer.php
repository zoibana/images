<?php

namespace zoibana\Images;

use ReflectionClass;
use zoibana\Images\Cache\ImageCache;
use zoibana\Images\Exceptions\Cache\CouldNotSaveFileToCacheException;
use zoibana\Images\Exceptions\Cache\CacheDirectoryNotFoundException;
use zoibana\Images\Exceptions\Cache\CacheDirectoryNotWritableException;
use zoibana\Images\Exceptions\SourceImageFileNotFoundException;

class ImageServer
{
	/** @var ImageCache */
	protected $cache;

	/** @var ImageResource|null */
	protected $resource;

	/**
	 * @param string $cacheDir
	 * @throws CacheDirectoryNotFoundException
	 * @throws CacheDirectoryNotWritableException
	 */
	public function enableCache(string $cacheDir): void
	{
		$this->cache = new ImageCache($cacheDir);
	}

	public function disableCache(): void
	{
		$this->cache = null;
	}

	/**
	 * @param string $image_path
	 * @throws SourceImageFileNotFoundException
	 */
	public function fromFile(string $image_path): void
	{
		$this->resource = ImageResource::createFromFile($image_path);
	}

	/**
	 * @param string|null $dest
	 * @param null $quality
	 * @return bool
	 * @throws \ReflectionException
	 * @throws CouldNotSaveFileToCacheException
	 */
	public function save(string $dest = null, $quality = null): bool
	{
		if ($this->resource) {

			if ($this->cache) {

				$file = $this->resource->getSourceFile();
				$fileName = basename($file, '.' . pathinfo($file, PATHINFO_EXTENSION));
				$ext = strtolower((new ReflectionClass($this->resource))->getShortName());

				$cacheKey = "$fileName.q$quality.$ext";

				$this->resource->header();

				$getter = function () use ($cacheKey, $quality) {
					$this->resource->save($this->cache->filePath($cacheKey), $quality);
				};

				$image = $this->cache->get($cacheKey, $getter);

				if ($image) {
					echo $image;
					exit;
				}

				throw new CouldNotSaveFileToCacheException();
			}

			if ($dest === null) {
				$this->resource->header();
			}

			return $this->resource->save($dest, $quality);
		}

		return false;
	}

	/**
	 * @param int $imagetype
	 * @param string|null $dest
	 * @param null $quality
	 * @return bool
	 */
	public function saveAs(int $imagetype, string $dest = null, $quality = null): bool
	{
		if ($this->resource) {
			$this->resource = $this->resource->setImagetype($imagetype);
			if ($dest === null) {
				$this->resource->header();
			}
			return $this->resource->save($dest, $quality);
		}

		return false;
	}
}