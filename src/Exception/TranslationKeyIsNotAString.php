<?php
namespace DasRed\Translation\Exception;

use DasRed\Translation\Exception;

class TranslationKeyIsNotAString extends Exception
{

	/**
	 *
	 * @param mixed $type
	 * @param string $key
	 * @param string $path
	 * @param string $locale
	 * @param string $file
	 */
	public function __construct($value, $key, $path, $locale, $file)
	{
		parent::__construct('Translation keys must be type of string. In path "' . $path . '", locale "' . $locale . '", file "' . $file . '" is the key "' . $key . '" type of ' . gettype($value) . '!');
	}
}
