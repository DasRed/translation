<?php
namespace DasRedTest\Translation\Command\Exception;

use DasRed\Translation\Command\Exception\InvalidCommandOperation;
use DasRed\Translation\Command\Exception;

/**
 * @coversDefaultClass \DasRed\Translation\Command\Exception\InvalidCommandOperation
 */
class InvalidCommandOperationTest extends \PHPUnit_Framework_TestCase
{
	public function testExtends()
	{
		$exception = new InvalidCommandOperation('a', 'b');
		$this->assertTrue($exception instanceof Exception);
	}

	/**
	 * @covers ::__construct
	 */
	public function test__construct()
	{
		$exception = new InvalidCommandOperation('a', 'b');
		$this->assertSame('Operation "b" not found for command "a".', $exception->getMessage());
	}
}
