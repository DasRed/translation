<?php
namespace DasRed\Translation\Exception;

use DasRed\Translation\Exception;

class FileNotFound extends Exception
{

	/**
	 *
	 * @param string $path
	 * @param string $locale
	 * @param string $file
	 */
	public function __construct($path, $locale, $file)
	{
		parent::__construct('Translation file "' . $file . '" not found for locale "' . $locale . '" in "' . $path . '"!');
	}
}
