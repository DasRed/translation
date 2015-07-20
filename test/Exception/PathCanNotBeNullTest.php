<?php
namespace DasRedTest\Translation\Exception;

use DasRed\Translation\Exception;
use DasRed\Translation\Exception\PathCanNotBeNull;

/**
 * @coversDefaultClass \DasRed\Translation\Exception\PathCanNotBeNull
 */
class PathCanNotBeNullTest extends \PHPUnit_Framework_TestCase
{

	public function testExtends()
	{
		$exception = new PathCanNotBeNull();
		$this->assertTrue($exception instanceof Exception);
	}

	/**
	 * @covers ::__construct
	 */
	public function testConstruct()
	{
		$exception = new PathCanNotBeNull();

		$this->assertEquals('The path for translations can not be null.', $exception->getMessage());
	}
}