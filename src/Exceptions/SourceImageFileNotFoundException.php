<?php

namespace zoibana\Images\Exceptions;

class SourceImageFileNotFoundException extends \Exception
{
	protected $message = 'Source image file is not found or not readable';
}