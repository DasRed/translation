<?php
namespace DasRed\Translation\Command\Executor\Translation;

use Zend\Console\ColorInterface;
use DasRed\Translation\Command\Executor\TranslationAbstract;

class Duplicates extends TranslationAbstract
{

	/*
	 * (non-PHPdoc)
	 * @see \DasRed\Translation\Command\ExecutorAbstract::execute()
	 */
	public function execute()
	{
		$entries = [];
		$path = rtrim(str_replace('\\', '/', $this->getArguments()[0]), '/') . '/';

		try
		{
			$directory = new \RecursiveDirectoryIterator($path);
			$iterator = new \RecursiveIteratorIterator($directory);
			$regex = new \RegexIterator($iterator, '/^.+\.php$/i', \RecursiveRegexIterator::GET_MATCH);

			foreach ($regex as $matches)
			{
				$file = str_replace('\\', '/', $matches[0]);
				$translations = require $matches[0];

				foreach ($translations as $key => $content)
				{
					if (array_key_exists($content, $entries) === false)
					{
						$entries[$content] = [];
					}

					$entries[$content][] = [
						'file' => str_replace([$path, '.php'], '', $file),
						'key' => $key
					];
				}
			}

			$entries = array_filter($entries, function($files)
			{
				return count($files) > 1;
			});

			foreach ($entries as $content => $files)
			{
				for ($i = 1; $i < count($files); $i++)
				{
					$this->write($files[0], $files[$i]);
				}
			}

			$this->getConsole()->writeLine('Done.', ColorInterface::BLACK, ColorInterface::LIGHT_GREEN);
		}
		catch (\Exception $exception)
		{
			$this->getConsole()->writeLine('Can not search for duplicates. Maybe the path is wrong.', ColorInterface::LIGHT_YELLOW, ColorInterface::LIGHT_RED);
			return false;
		}

		return true;
	}

	/*
	 * (non-PHPdoc)
	 * @see \DasRed\Translation\Command\ExecutorAbstract::validateArguments()
	 */
	protected function validateArguments($arguments)
	{
		if (count($arguments) !== 1)
		{
			return false;
		}

		return parent::validateArguments($arguments);
	}

	/**
	 *
	 * @param array $fileA
	 * @param array $fileB
	 * @return self
	 */
	protected function write(array $fileA, array $fileB)
	{
		$this->getConsole()->write($fileA['file'], ColorInterface::GREEN);
		$this->getConsole()->write('.');
		$this->getConsole()->write($fileA['key'], ColorInterface::LIGHT_GREEN);
		$this->getConsole()->write(' <-> ');
		$this->getConsole()->write($fileB['file'], ColorInterface::RED);
		$this->getConsole()->write('.');
		$this->getConsole()->writeLine($fileB['key'], ColorInterface::LIGHT_RED);

		return $this;
	}
}