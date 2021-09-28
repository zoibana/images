<?php

namespace images\manipulate;

use Exception;
use ReflectionClass;

class ImageServer
{
	/** @var \images\manipulate\ImageCache */
	protected $cache;

	/** @var \images\manipulate\ImageResource|null */
	protected $resource;

	/**
	 * @param string $cacheDir
	 * @throws \Exception
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
	 * @throws \Exception
	 */
	public function fromFile(string $image_path): void
	{
		$this->resource = ImageResource::createFromFile($image_path);
	}

	/**
	 * @param string|null $dest
	 * @param null $quality
	 * @return bool
	 * @throws \Exception
	 */
	public function save(string $dest = null, $quality = null): bool
	{
		if ($this->resource) {

			// ���� ������� ���
			if ($this->cache) {

				$file = $this->resource->getSourceFile();
				$fileName = basename($file, '.' . pathinfo($file, PATHINFO_EXTENSION));
				$ext = strtolower((new ReflectionClass($this->resource))->getShortName());

				// ��������� ���������� ���� ��� ����� ������ ����������
				$cacheKey = "$fileName.q$quality.$ext";

				// ���������� �������� ��������� �������� ������� �����������
				$this->resource->header();

				$getter = function () use ($cacheKey, $quality) {
					// ���� � ���� ��� - ���� ��� ���� ���������
					// ��������� ���� ����� ��������� �������� ����� � ����� ����
					$this->resource->save($this->cache->filePath($cacheKey), $quality);
				};

				// ��������� ������� � ����. ���� ���� - ������
				$image = $this->cache->get($cacheKey, $getter);

				if ($image) {
					echo $image;
					exit;
				}

				throw new Exception('Could not save image to cache');
			}

			// ���� �� ������� ����� ��� ���������� - �������� ������ �������� - ����� ������ ���������� ���������
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
	 * @throws \Exception
	 */
	public function saveAs(int $imagetype, string $dest = null, $quality = null): bool
	{
		if ($this->resource) {
			// ������ ������ �����������
			$this->resource = $this->resource->setImagetype($imagetype);

			return $this->save($dest, $quality);
		}

		return false;
	}
}