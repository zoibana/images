<?php

namespace zoibana\Images;

interface ImageFormatInterface
{
	public function fromFile(string $src_path): void;

	public function save(string $dest_path = null, $quality = null): bool;

	public function header(): void;
}