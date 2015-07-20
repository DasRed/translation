<?php
namespace DasRed\Translation\Exception;

use DasRed\Translation\Exception;

class InvalidTranslationFile extends Exception
{

	/**
	 *
	 * @param string $path
	 * @param string $locale
	 * @param string $file
	 */
	public function __construct($path, $locale, $file)
	{
		parent::__construct('Unable to load or retrieve data from the requested language file: "' . $file . '" for locale "' . $locale . '" in "' . $path . '"!');
	}
}
