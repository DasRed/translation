<?php
namespace DasRed\Translation\Command\Exception;

use DasRed\Translation\Command\Exception;

class InvalidCommandOperation extends Exception
{
	/**
	 *
	 * @param string $command
	 */
	public function __construct($command, $operation)
	{
		parent::__construct('Operation "' . $operation . '" not found for command "' . $command . '".');
	}
}