<?php
namespace DasRedTest\Translation\Exception;

use DasRed\Translation\Exception;
use DasRed\Translation\Exception\DefaultLocaleCanNotBeNull;

/**
 * @coversDefaultClass \DasRed\Translation\Exception\DefaultLocaleCanNotBeNull
 */
class DefaultLocaleCanNotBeNullTest extends \PHPUnit_Framework_TestCase
{

	public function testExtends()
	{
		$exception = new DefaultLocaleCanNotBeNull();
		$this->assertTrue($exception instanceof Exception);
	}

	/**
	 * @covers ::__construct
	 */
	public function testConstruct()
	{
		$exception = new DefaultLocaleCanNotBeNull();

		$this->assertEquals('The default locale for translations can not be null.', $exception->getMessage());
	}
}