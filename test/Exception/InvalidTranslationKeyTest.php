<?php
namespace DasRedTest\Translation\Exception;

use DasRed\Translation\Exception;
use DasRed\Translation\Exception\InvalidTranslationKey;

/**
 * @coversDefaultClass \DasRed\Translation\Exception\InvalidTranslationKey
 */
class InvalidTranslationKeyTest extends \PHPUnit_Framework_TestCase
{

	public function testExtends()
	{
		$exception = new InvalidTranslationKey('a', 'b');
		$this->assertTrue($exception instanceof Exception);
	}

	public function dataProviderConstruct()
	{
		return [
			['b', 'C', 'Invalid translation key "b" for locale "C"! Can not expand to file name and translation entry key.'],
			['ichBineinKey', 'de-DE', 'Invalid translation key "ichBineinKey" for locale "de-DE"! Can not expand to file name and translation entry key.'],
		];
	}

	/**
	 * @param string $key
	 * @param string $locale
	 * @param string $expectedMessage
	 * @covers ::__construct
	 * @dataProvider dataProviderConstruct
	 */
	public function testConstruct($key, $locale, $expectedMessage)
	{
		$exception = new InvalidTranslationKey($key, $locale);

		$this->assertEquals($expectedMessage, $exception->getMessage());
	}
}