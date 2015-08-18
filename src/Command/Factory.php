<?php
namespace DasRed\Translation\Command;

use Zend\Console\Adapter\AdapterInterface;
use DasRed\Zend\Console\ConsoleAwareInterface;
use DasRed\Zend\Console\ConsoleAwareTrait;
use DasRed\Translation\Command\Exception\InvalidCommandOperation;

class Factory implements ConsoleAwareInterface
{
	use ConsoleAwareTrait;

	/**
	 *
	 * @param AdapterInterface $console
	 */
	public function __construct(AdapterInterface $console)
	{
		$this->setConsole($console);
	}

	/**
	 *
	 * @param array $arguments
	 * @return ExecutorAbstract
	 */
	public function factory(array $arguments)
	{
		$command = ucfirst(array_shift($arguments));
		$operation = ucfirst(array_shift($arguments));

		$className = '\\DasRed\\Translation\\Command\\Executor\\' . $command . '\\' . $operation;
		// find
		if (class_exists($className) === false)
		{
			throw new InvalidCommandOperation(lcfirst($command), lcfirst($operation));
		}

		return new $className($this->getConsole(), $arguments);
	}
}