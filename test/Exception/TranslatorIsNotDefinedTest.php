<?php
namespace DasRedTest\Translation\Exception;

use DasRed\Translation\Exception;
use DasRed\Translation\Exception\TranslatorIsNotDefined;

/**
 * @coversDefaultClass \DasRed\Translation\Exception\TranslatorIsNotDefined
 */
class TranslatorIsNotDefinedTest extends \PHPUnit_Framework_TestCase
{

	public function testExtends()
	{
		$exception = new TranslatorIsNotDefined();
		$this->assertTrue($exception instanceof Exception);
	}

	/**
	 * @covers ::__construct
	 */
	public function testConstruct()
	{
		$exception = new TranslatorIsNotDefined();

		$this->assertEquals('The translator is not defined!', $exception->getMessage());
	}
}