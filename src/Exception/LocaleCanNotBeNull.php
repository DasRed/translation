<?php
namespace DasRed\Translation\Exception;

use DasRed\Translation\Exception;

class LocaleCanNotBeNull extends Exception
{

	/**
	 * constructor
	 *
	 * @param string $type
	 */
	public function __construct($type)
	{
		parent::__construct('The ' . $type . ' locale for translations can not be null.');
	}
}
