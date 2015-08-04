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
		$exception = new InvalidTranslationKey('a', 'b', 'c', 'd');
		$this->assertTrue($exception instanceof Exception);
	}

	public function dataProviderConstruct()
	{
		return [
			['b', 'C', 'a', 'b', 'Invalid translation key "b" for locale "C"! Can not expand to file name and translation entry key.'],
			['ichBineinKey', 'de-DE', 'a', 'b', 'Invalid translation key "ichBineinKey" for locale "de-DE"! Can not expand to file name and translation entry key.'],
		];
	}

	/**
	 * @param string $key
	 * @param string $locale
	 * @param string $expectedMessage
	 * @covers ::__construct
	 * @dataProvider dataProviderConstruct
	 */
	public function testConstruct($key, $locale, $translationFile, $translationKey, $expectedMessage)
	{
		$exception = new InvalidTranslationKey($key, $locale, $translationFile, $translationKey);

		$this->assertEquals($expectedMessage, $exception->getMessage());
		$this->assertEquals($translationFile, $exception->getTranslationFile());
		$this->assertEquals($translationKey, $exception->getTranslationKey());
	}

	/**
	 * @covers ::getTranslationFile
	 * @covers ::setTranslationFile
	 */
	public function testGetSetTranslationFile()
	{
		$exception = new InvalidTranslationKey('a', 'b', 'c', 'd');

		$reflectionMethod = new \ReflectionMethod($exception, 'setTranslationFile');
		$reflectionMethod->setAccessible(true);

		$this->assertEquals('c', $exception->getTranslationFile());
		$this->assertSame($exception, $reflectionMethod->invoke($exception, 'd'));
		$this->assertEquals('d', $exception->getTranslationFile());
	}

	/**
	 * @covers ::getTranslationKey
	 * @covers ::setTranslationKey
	 */
	public function testGetSetTranslationKey()
	{
		$exception = new InvalidTranslationKey('a', 'b', 'c', 'd');

		$reflectionMethod = new \ReflectionMethod($exception, 'setTranslationKey');
		$reflectionMethod->setAccessible(true);

		$this->assertEquals('d', $exception->getTranslationKey());
		$this->assertSame($exception, $reflectionMethod->invoke($exception, 'c'));
		$this->assertEquals('c', $exception->getTranslationKey());

	}
}