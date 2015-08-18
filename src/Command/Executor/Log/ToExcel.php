<?php
namespace DasRed\Translation\Command\Executor\Log;

class ToExcel extends ToCsv
{

	/**
	 *
	 * @var int
	 */
	protected $currentLine = 1;

	/**
	 * @var \PHPExcel
	 */
	protected $excel;

	/**
	 * @var \PHPExcel_Writer_IWriter
	 */
	protected $writer;

	/**
	 * (non-PHPdoc)
	 * @see \DasRed\Translation\Command\Executor\Log\ToCsv::execute()
	 */
	public function execute()
	{
		parent::execute();

		$sheet = $this->getExcel()->getActiveSheet();

		$sheet->getStyle('A1')->getFont()->setBold(true);
		$sheet->getStyle('B1')->getFont()->setBold(true);
		$sheet->getStyle('C1')->getFont()->setBold(true);
		$sheet->getStyle('D1')->getFont()->setBold(true);
		$sheet->getStyle('E1')->getFont()->setBold(true);

		// auto size
		for ($column = 'A'; $column != 'F'; $column++)
		{
			$sheet->getColumnDimension($column)->setAutoSize(true);
		}

		$this->getWriter()->save($this->getArguments()[1]);

		return true;
	}

	/**
	 * @return int
	 */
	protected function getCurrentLine()
	{
		return $this->currentLine;
	}

	/**
	 * @return \PHPExcel
	 */
	protected function getExcel()
	{
		if ($this->excel === null)
		{
			$this->excel = new \PHPExcel();
			$this->excel->getProperties()->setCreator('dasred/translation');
			$this->excel->setActiveSheetIndex(0);

			$sheet = $this->excel->getActiveSheet()->setTitle(basename($this->getArguments()[0]));
		}

		return $this->excel;
	}

	/**
	 * @return PHPExcel_Writer_IWriter
	 */
	protected function getWriter()
	{
		if ($this->writer === null)
		{
			$this->writer = \PHPExcel_IOFactory::createWriter($this->getExcel(), 'Excel5');
		}

		return $this->writer;
	}

	/**
	 * @param int $currentLine
	 * @return self
	 */
	protected function setCurrentLine($currentLine)
	{
		$this->currentLine = $currentLine;

		return $this;
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
		$sheet = $this->getExcel()->getActiveSheet();

		// labels
		$sheet->setCellValue('A' . $this->getCurrentLine(), $file)
			->setCellValue('B' . $this->getCurrentLine(), $lineNumber)
			->setCellValue('C' . $this->getCurrentLine(), $key)
			->setCellValue('D' . $this->getCurrentLine(), $locale)
			->setCellValue('E' . $this->getCurrentLine(), $count);

		$sheet->getStyle('B' . $this->getCurrentLine())->getNumberFormat()->setFormatCode('#,###,###,###');
		$sheet->getStyle('E' . $this->getCurrentLine())->getNumberFormat()->setFormatCode('#,###,###,###');

		return $this->setCurrentLine($this->getCurrentLine() + 1);
	}
}