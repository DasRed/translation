<?php
namespace DasRedTest\Translation\Exception;

use DasRed\Translation\Exception;
use DasRed\Translation\Exception\InvalidTranslationFile;

/**
 * @coversDefaultClass \DasRed\Translation\Exception\InvalidTranslationFile
 */
class InvalidTranslationFileTest extends \PHPUnit_Framework_TestCase
{

	public function testExtends()
	{
		$exception = new InvalidTranslationFile('a', 'b', 'c');
		$this->assertTrue($exception instanceof Exception);
	}

	public function dataProviderConstruct()
	{
		return [
			['A', 'b', 'C', 'Unable to load or retrieve data from the requested language file: "C" for locale "b" in "A"!'],
			['nuff/a/b', 'de-DE', 'lol.php', 'Unable to load or retrieve data from the requested language file: "lol.php" for locale "de-DE" in "nuff/a/b"!'],
		];
	}

	/**
	 * @param string $path
	 * @param string $locale
	 * @param string $file
	 * @param string $expectedMessage
	 * @covers ::__construct
	 * @dataProvider dataProviderConstruct
	 */
	public function testConstruct($path, $locale, $file, $expectedMessage)
	{
		$exception = new InvalidTranslationFile($path, $locale, $file);

		$this->assertEquals($expectedMessage, $exception->getMessage());
	}
}