<?php

namespace zoibana\Images\Cache;

use Closure;
use zoibana\Images\Exceptions\Cache\CacheDirectoryNotFoundException;
use zoibana\Images\Exceptions\Cache\CacheDirectoryNotWritableException;

class ImageCache
{
	protected $dir;

	/**
	 * @param string $cacheDir
	 */
	public function __construct(string $cacheDir)
	{
		$this->dir = $cacheDir;

		if (!is_dir($this->dir)) {
			throw new CacheDirectoryNotFoundException();
		}

		if (!is_writable($this->dir)) {
			throw new CacheDirectoryNotWritableException();
		}
	}

	public function get(string $key, Closure $getter = null): ?string
	{
		$file = $this->filePath($key);

		if ($getter && !file_exists($file)) {
			$getter();
		}

		if (file_exists($file)) {
			return file_get_contents($file);
		}

		return null;
	}

	public function set($key, string $content): void
	{
		file_put_contents($this->filePath($key), $content);
	}

	public function filePath($key): string
	{
		return $this->dir . $key;
	}
}