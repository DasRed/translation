<?php
namespace DasRed\Translation\Exception;

use DasRed\Translation\Exception;

class InvalidTranslationKey extends Exception
{

	/**
	 *
	 * @param string $key
	 * @param string $locale
	 */
	public function __construct($key, $locale)
	{
		parent::__construct('Invalid translation key "' . $key . '" for locale "' . $locale . '"! Can not expand to file name and translation entry key.');
	}
}
