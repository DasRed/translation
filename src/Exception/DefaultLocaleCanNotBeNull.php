<?php
namespace DasRed\Translation\Exception;

use DasRed\Translation\Exception;

class DefaultLocaleCanNotBeNull extends Exception
{

	/**
	 * constructor
	 */
	public function __construct()
	{
		parent::__construct('The default locale for translations can not be null.');
	}
}
