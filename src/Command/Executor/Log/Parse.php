<?php
namespace DasRed\Translation\Command\Executor\Log;

use Zend\Console\ColorInterface;
use DasRed\Translation\Command\Executor\LogAbstract;

class Parse extends LogAbstract
{

	/*
	 * (non-PHPdoc)
	 * @see \DasRed\Translation\Command\ExecutorAbstract::execute()
	 */
	public function execute()
	{
		$logFile = $this->getArguments()[0];

		try
		{
			$content = file($logFile);
			$result = [];
			foreach ($content as $entry)
			{
				$matches = [];
				if (preg_match('/^\[(\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}\+\d{2}:\d{2})\] ERR \(\d{1}\): Missing Key: (.*?)#(\d{1,}) (\{.*?\})/i', $entry, $matches) === 0)
				{
					continue;
				}

				$date = \DateTime::createFromFormat('Y-m-dTH:i:sP', $matches[1]);
				$file = $matches[2];
				$lineNumber = $matches[3];
				$data = json_decode($matches[4]);

				if (array_key_exists($file, $result) === false)
				{
					$result[$file] = [];
				}
				if (array_key_exists($lineNumber, $result[$file]) === false)
				{
					$result[$file][$lineNumber] = [];
				}
				if (array_key_exists($data->key, $result[$file][$lineNumber]) === false)
				{
					$result[$file][$lineNumber][$data->key] = [];
				}
				if (array_key_exists($data->locale, $result[$file][$lineNumber][$data->key]) === false)
				{
					$result[$file][$lineNumber][$data->key][$data->locale] = 0;
				}
				$result[$file][$lineNumber][$data->key][$data->locale]++;
			}

			ksort($result, SORT_NATURAL);

			foreach ($result as $file => $lineNumbers)
			{
				ksort($lineNumbers, SORT_NATURAL);

				foreach ($lineNumbers as $lineNumber => $keys)
				{
					ksort($keys, SORT_NATURAL);

					foreach ($keys as $key => $locales)
					{
						ksort($locales, SORT_NATURAL);

						foreach ($locales as $locale => $count)
						{
							$this->write($file, $lineNumber, $key, $locale, $count);
						}
					}
				}
			}

			$this->getConsole()->writeLine('Done.', ColorInterface::BLACK, ColorInterface::LIGHT_GREEN);
		}
		catch (\Exception $exception)
		{
			$this->getConsole()->writeLine('File can not be parsed. Maybe the file does not exists.', ColorInterface::LIGHT_YELLOW, ColorInterface::LIGHT_RED);
			return false;
		}

		return true;
	}

	/**
	 * @return int
	 */
	protected function getMaxCountOfArguments()
	{
		return 1;
	}

	/*
	 * (non-PHPdoc)
	 * @see \DasRed\Translation\Command\ExecutorAbstract::validateArguments()
	 */
	protected function validateArguments($arguments)
	{
		if (count($arguments) !== $this->getMaxCountOfArguments())
		{
			return false;
		}

		return parent::validateArguments($arguments);
	}

	/**
	 *
	 * @param string $file
	 * @param int $lineNumber
	 * @param string $key
	 * @param string $locale
	 * @param int $count
	 * @return self
	 */
	protected function write($file, $lineNumber, $key, $locale, $count)
	{
		$this->getConsole()->write($file, ColorInterface::LIGHT_GREEN);
		$this->getConsole()->write(' ');
		$this->getConsole()->write('#' . $lineNumber, ColorInterface::LIGHT_CYAN);
		$this->getConsole()->write(' with key ');
		$this->getConsole()->write($key, ColorInterface::LIGHT_YELLOW);
		$this->getConsole()->write(' and locale ');
		$this->getConsole()->write($locale, ColorInterface::LIGHT_MAGENTA);
		$this->getConsole()->writeLine(' (' . number_format($count, 0, '.', ',') . ' calls)');

		return $this;
	}
}