<?php

namespace zoibana\Images\Exceptions\Cache;

class CacheDirectoryNotFoundException extends \Exception
{
	protected $message = 'Cache directory is not found';
}