<?php
namespace DasRedTest\Translation;

use DasRed\Translation\Exception;

/**
 * @coversDefaultClass \DasRed\Translation\Exception
 */
class ExceptionTest extends \PHPUnit_Framework_TestCase
{

	public function testExtends()
	{
		$exception = new Exception();
		$this->assertTrue($exception instanceof \Exception);
	}
}