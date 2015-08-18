<?php
namespace DasRedTest\Translation\Command\Executor\Log;

use DasRed\Translation\Command\Executor\Log\ToExcel;
use Zend\Console\Adapter\AdapterInterface;

/**
 * @coversDefaultClass \DasRed\Translation\Command\Executor\Log\ToExcel
 */
class ToExcelTest extends \PHPUnit_Framework_TestCase
{

	protected $console;

	protected $excel;

	protected $sheet;

	public function setUp()
	{
		parent::setUp();

		$this->console = $this->getMockBuilder(AdapterInterface::class)->setMethods(['write', 'writeLine'])->getMockForAbstractClass();

		$this->excel = $this->getMockBuilder(\PHPExcel::class)->setMethods(['getActiveSheet'])->getMock();
		$this->sheet = $this->getMockBuilder(\PHPExcel_Worksheet::class)->setMethods(['getStyle', 'setCellValue', 'getColumnDimension'])->setConstructorArgs([$this->excel])->getMock();
		$this->excel->expects($this->any())->method('getActiveSheet')->willReturn($this->sheet);
	}

	public function tearDown()
	{
		parent::tearDown();

		$this->console = null;
		$this->excel = null;
		$this->sheet = null;
	}

	/**
	 * @covers ::execute
	 */
	public function testExecute()
	{
		$writer = $this->getMockBuilder(\PHPExcel_Writer_IWriter::class)->setMethods(['save'])->getMock();
		$writer->expects($this->once())->method('save')->with(__DIR__ . '/xls.xls')->willReturnSelf();

		$exec = $this->getMockBuilder(ToExcel::class)->setMethods(['getExcel', 'write', 'getWriter'])->setConstructorArgs([$this->console, [__DIR__ . '/translations.log', __DIR__ . '/xls.xls']])->getMock();
		$exec->expects($this->any())->method('getExcel')->willReturn($this->excel);
		$exec->expects($this->any())->method('write')->willReturnSelf();
		$exec->expects($this->any())->method('getWriter')->willReturn($writer);

		$font = $this->getMockBuilder(\PHPExcel_Style_Font::class)->setMethods(['setBold'])->getMock();
		$font->expects($this->exactly(5))->method('setBold')->with(true)->willReturnSelf();

		$style = $this->getMockBuilder(\PHPExcel_Style::class)->setMethods(['getFont'])->getMock();
		$style->expects($this->exactly(5))->method('getFont')->with()->willReturn($font);

		$columnDimension = $this->getMockBuilder(\PHPExcel_Worksheet_ColumnDimension::class)->setMethods(['setAutoSize'])->getMock();
		$columnDimension->expects($this->exactly(5))->method('setAutoSize')->with(true)->willReturnSelf();

		$this->sheet->expects($this->exactly(5))->method('getStyle')->withConsecutive(['A1'], ['B1'], ['C1'], ['D1'], ['E1'])->willReturn($style);
		$this->sheet->expects($this->exactly(5))->method('getColumnDimension')->withConsecutive(['A'], ['B'], ['C'], ['D'], ['E'])->willReturn($columnDimension);

		$this->assertTrue($exec->execute());
	}

	/**
	 * @covers ::write
	 */
	public function testWrite()
	{
		$exec = $this->getMockBuilder(ToExcel::class)->setMethods(['getExcel', 'getCurrentLine', 'setCurrentLine'])->setConstructorArgs([$this->console, [__DIR__ . '/translations.log', __DIR__ . '/xls.xls']])->getMock();
		$exec->expects($this->any())->method('getExcel')->willReturn($this->excel);
		$exec->expects($this->any())->method('getCurrentLine')->with()->willReturn(51);
		$exec->expects($this->any())->method('setCurrentLine')->with(52)->willReturnSelf();

		$numberFormat = $this->getMockBuilder(\PHPExcel_Style_NumberFormat::class)->setMethods(['setFormatCode'])->getMock();
		$numberFormat->expects($this->exactly(2))->method('setFormatCode')->with('#,###,###,###')->willReturnSelf();

		$style = $this->getMockBuilder(\PHPExcel_Style::class)->setMethods(['getNumberFormat'])->getMock();
		$style->expects($this->exactly(2))->method('getNumberFormat')->with()->willReturn($numberFormat);

		$this->sheet->expects($this->exactly(5))->method('setCellValue')->withConsecutive(['A51', 'a'], ['B51', 2], ['C51', 'c'], ['D51', 'de'], ['E51', 32])->willReturnSelf();
		$this->sheet->expects($this->exactly(2))->method('getStyle')->withConsecutive(['B51'], ['E51'])->willReturn($style);

		$reflectionMethod = new \ReflectionMethod($exec, 'write');
		$reflectionMethod->setAccessible(true);

		$this->assertSame($exec, $reflectionMethod->invoke($exec, 'a', 2, 'c', 'de', 32));
	}

	/**
	 * @covers ::getCurrentLine
	 * @covers ::setCurrentLine
	 */
	public function testGetSetCurrentLine()
	{
		$exec = new ToExcel($this->console, [__DIR__ . '/nuff', __DIR__ . '/nuff']);

		$reflectionMethodGet = new \ReflectionMethod($exec, 'getCurrentLine');
		$reflectionMethodGet->setAccessible(true);

		$reflectionMethodSet = new \ReflectionMethod($exec, 'setCurrentLine');
		$reflectionMethodSet->setAccessible(true);

		$this->assertSame(1, $reflectionMethodGet->invoke($exec));
		$this->assertSame($exec, $reflectionMethodSet->invoke($exec, 45));
		$this->assertSame(45, $reflectionMethodGet->invoke($exec));
	}

	/**
	 * @covers ::getExcel
	 */
	public function testGetExcel()
	{
		$exec = new ToExcel($this->console, [__DIR__ . '/nuff', __DIR__ . '/xls.xls']);

		$reflectionMethod = new \ReflectionMethod($exec, 'getExcel');
		$reflectionMethod->setAccessible(true);

		/* @var $excel \PHPExcel */
		$excel = $reflectionMethod->invoke($exec);

		$this->assertInstanceOf(\PHPExcel::class, $excel);
		$this->assertSame($excel, $reflectionMethod->invoke($exec));
		$this->assertSame('dasred/translation', $excel->getProperties()->getCreator());
		$this->assertSame(0, $excel->getActiveSheetIndex());
		$this->assertSame('nuff', $excel->getActiveSheet()->getTitle());
	}

	/**
	 * @covers ::getWriter
	 */
	public function testGetWriter()
	{
		$exec = new ToExcel($this->console, [__DIR__ . '/nuff', __DIR__ . '/xls.xls']);

		$reflectionMethod = new \ReflectionMethod($exec, 'getWriter');
		$reflectionMethod->setAccessible(true);

		/* @var $excel \PHPExcel_Writer_IWriter */
		$excel = $reflectionMethod->invoke($exec);

		$this->assertInstanceOf(\PHPExcel_Writer_IWriter::class, $excel);
		$this->assertSame($excel, $reflectionMethod->invoke($exec));
	}
}