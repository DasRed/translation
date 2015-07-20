<?php
namespace DasRed\Translation\Exception;

use DasRed\Translation\Exception;

class TranslationKeyNotFound extends Exception
{

	/**
	 *
	 * @param string $key
	 * @param string $path
	 * @param string $locale
	 * @param string $file
	 */
	public function __construct($key, $path, $locale, $file)
	{
		parent::__construct('Could not find the translation key for path "' . $path . '", locale "' . $locale . '", file "' . $file . '" and key "' . $key . '"!');
	}
}
