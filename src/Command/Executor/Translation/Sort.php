<?php
namespace DasRed\Translation\Command\Executor\Translation;

use Zend\Console\ColorInterface;
use DasRed\Translation\Command\Executor\TranslationAbstract;

class Sort extends TranslationAbstract
{

	/*
	 * (non-PHPdoc)
	 * @see \DasRed\Translation\Command\ExecutorAbstract::execute()
	 */
	public function execute()
	{
		try
		{

			$directory = new \RecursiveDirectoryIterator($this->getArguments()[0]);
			$iterator = new \RecursiveIteratorIterator($directory);
			$regex = new \RegexIterator($iterator, '/^.+\.php$/i', \RecursiveRegexIterator::GET_MATCH);

			foreach ($regex as $file)
			{
				$translations = require $file[0];
				ksort($translations, SORT_NATURAL);

				$fHandle = fopen($file[0], 'w');
				fwrite($fHandle, "<?php\n");
				fwrite($fHandle, "\n");
				fwrite($fHandle, "return [\n");

				foreach ($translations as $key => $entry)
				{
					fwrite($fHandle, sprintf('	\'%s\' => \'%s\',', $key, addcslashes($entry, '\'')) . "\n");
				}

				fwrite($fHandle, "];\n");
				fclose($fHandle);
			}

			$this->getConsole()->writeLine('Translations sorted.', ColorInterface::BLACK, ColorInterface::LIGHT_GREEN);
		}
		catch (\Exception $exception)
		{
			$this->getConsole()->writeLine('Translations can not be sorted. Maybe the path is wrong.', ColorInterface::LIGHT_YELLOW, ColorInterface::LIGHT_RED);
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
}