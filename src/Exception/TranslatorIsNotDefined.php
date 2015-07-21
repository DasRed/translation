<?php
namespace DasRed\Translation\Exception;

use DasRed\Translation\Exception;

class TranslatorIsNotDefined extends Exception
{

	/**
	 * constructor
	 */
	public function __construct()
	{
		parent::__construct('The translator is not defined!');
	}
}
