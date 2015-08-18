<?php
namespace DasRedTest\Translation\Command;

use DasRed\Translation\Command\Exception;
use DasRed\Translation\Exception as ExceptionBase;

/**
 * @coversDefaultClass \DasRed\Translation\Command\Exception
 */
class ExceptionTest extends \PHPUnit_Framework_TestCase
{

	public function testExtends()
	{
		$exception = new Exception();
		$this->assertTrue($exception instanceof ExceptionBase);
	}
}
