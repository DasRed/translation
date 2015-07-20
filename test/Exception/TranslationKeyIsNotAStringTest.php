<?php
namespace DasRedTest\Translation\Exception;

use DasRed\Translation\Exception;
use DasRed\Translation\Exception\TranslationKeyIsNotAString;

/**
 * @coversDefaultClass \DasRed\Translation\Exception\TranslationKeyIsNotAString
 */
class TranslationKeyIsNotAStringTest extends \PHPUnit_Framework_TestCase
{

	public function testExtends()
	{
		$exception = new TranslationKeyIsNotAString(null, 'a', 'b', 'c', 'd');
		$this->assertTrue($exception instanceof Exception);
	}

	public function dataProviderConstruct()
	{
		return [
			[1, 'd', 'A', 'b', 'C', 'Translation keys must be type of string. In path "A", locale "b", file "C" is the key "d" type of integer!'],
			['text', 'd', 'A', 'b', 'C', 'Translation keys must be type of string. In path "A", locale "b", file "C" is the key "d" type of string!'],
			[null, 'd', 'A', 'b', 'C', 'Translation keys must be type of string. In path "A", locale "b", file "C" is the key "d" type of NULL!'],
			[1.1, 'd', 'A', 'b', 'C', 'Translation keys must be type of string. In path "A", locale "b", file "C" is the key "d" type of double!'],
			[true, 'd', 'A', 'b', 'C', 'Translation keys must be type of string. In path "A", locale "b", file "C" is the key "d" type of boolean!'],
			[false, 'd', 'A', 'b', 'C', 'Translation keys must be type of string. In path "A", locale "b", file "C" is the key "d" type of boolean!'],
			[new \stdClass(), 'd', 'A', 'b', 'C', 'Translation keys must be type of string. In path "A", locale "b", file "C" is the key "d" type of object!'],
		];
	}

	/**
	 * @param mixed $value
	 * @param string $key
	 * @param string $path
	 * @param string $locale
	 * @param string $file
	 * @param string $expectedMessage
	 * @covers ::__construct
	 * @dataProvider dataProviderConstruct
	 */
	public function testConstruct($value, $key, $path, $locale, $file, $expectedMessage)
	{
		$exception = new TranslationKeyIsNotAString($value, $key, $path, $locale, $file);

		$this->assertEquals($expectedMessage, $exception->getMessage());
	}
}