<?php

namespace zoibana\Images\Exceptions\Cache;

class CacheDirectoryNotWritableException extends \Exception
{
	protected $message = 'Cache directory is not writeable';
}