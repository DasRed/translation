<?php
namespace DasRedTest\Translation\Exception;

use DasRed\Translation\Exception;
use DasRed\Translation\Exception\TranslationKeyNotFound;

/**
 * @coversDefaultClass \DasRed\Translation\Exception\TranslationKeyNotFound
 */
class TranslationKeyNotFoundTest extends \PHPUnit_Framework_TestCase
{

	public function testExtends()
	{
		$exception = new TranslationKeyNotFound('a', 'b', 'c', 'd');
		$this->assertTrue($exception instanceof Exception);
	}

	public function dataProviderConstruct()
	{
		return [
			['d', 'A', 'b', 'C', 'Could not find the translation key for path "A", locale "b", file "C" and key "d"!'],
			['ichBinEinKey', 'nuff/a/b', 'de-DE', 'lol.php', 'Could not find the translation key for path "nuff/a/b", locale "de-DE", file "lol.php" and key "ichBinEinKey"!'],
		];
	}

	/**
	 * @param string $key
	 * @param string $path
	 * @param string $locale
	 * @param string $file
	 * @param string $expectedMessage
	 * @covers ::__construct
	 * @dataProvider dataProviderConstruct
	 */
	public function testConstruct($key, $path, $locale, $file, $expectedMessage)
	{
		$exception = new TranslationKeyNotFound($key, $path, $locale, $file);

		$this->assertEquals($expectedMessage, $exception->getMessage());
	}
}