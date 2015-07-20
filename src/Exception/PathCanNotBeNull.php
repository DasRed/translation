<?php
namespace DasRed\Translation\Exception;

use DasRed\Translation\Exception;

class PathCanNotBeNull extends Exception
{

	/**
	 * constructor
	 */
	public function __construct()
	{
		parent::__construct('The path for translations can not be null.');
	}
}
