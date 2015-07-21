<?php
namespace DasRedTest\Translation\Exception;

use DasRed\Translation\Exception;
use DasRed\Translation\Exception\LocaleCanNotBeNull;

/**
 * @coversDefaultClass \DasRed\Translation\Exception\LocaleCanNotBeNull
 */
class LocaleCanNotBeNullTest extends \PHPUnit_Framework_TestCase
{

	public function testExtends()
	{
		$exception = new LocaleCanNotBeNull('a');
		$this->assertTrue($exception instanceof Exception);
	}

	/**
	 * @covers ::__construct
	 */
	public function testConstruct()
	{
		$exception = new LocaleCanNotBeNull('nuff');

		$this->assertEquals('The nuff locale for translations can not be null.', $exception->getMessage());
	}
}