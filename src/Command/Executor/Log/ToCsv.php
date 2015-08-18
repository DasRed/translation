<?php
namespace DasRed\Translation\Command\Executor\Log;

class ToCsv extends Parse
{

	/**
	 * (non-PHPdoc)
	 * @see \DasRed\Translation\Command\Executor\Log\Parse::execute()
	 */
	public function execute()
	{
		$file = $this->getArguments()[1];
		if (file_exists($file) === true)
		{
			unlink($file);
			$this->write('File', 'LineNumber', 'Key', 'Locale', 'Count of Matches');
		}

		return parent::execute();
	}

	/**
	 * @return int
	 */
	protected function getMaxCountOfArguments()
	{
		return 2;
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
		$fHandle = fopen($this->getArguments()[1], 'a');
		fputcsv($fHandle, [
			$file,
			$lineNumber,
			$key,
			$locale,
			$count
		], ';', '"');
		fclose($fHandle);

		return $this;
	}
}