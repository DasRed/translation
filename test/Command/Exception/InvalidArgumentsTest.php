<?php
namespace DasRedTest\Translation\Command\Exception;

use DasRed\Translation\Command\Exception\InvalidArguments;
use DasRed\Translation\Command\Exception;

/**
 * @coversDefaultClass \DasRed\Translation\Command\Exception\InvalidArguments
 */
class InvalidArgumentsTest extends \PHPUnit_Framework_TestCase
{
	public function testExtends()
	{
		$exception = new InvalidArguments();
		$this->assertTrue($exception instanceof Exception);
	}
}
