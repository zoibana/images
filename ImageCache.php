<?php

namespace images\manipulate;

use Closure;
use Exception;

class ImageCache
{
	protected $dir;

	/**
	 * @param string $cacheDir
	 * @throws \Exception
	 */
	public function __construct(string $cacheDir)
	{
		$this->dir = $cacheDir;

		if (!is_dir($this->dir)) {
			throw new Exception('Cache directory is not found');
		}

		if (!is_writable($this->dir)) {
			throw new Exception('Cache directory is not writable');
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