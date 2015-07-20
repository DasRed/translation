<?php
namespace DasRedTest\Translation\Exception;

use DasRed\Translation\Exception;
use DasRed\Translation\Exception\FileNotFound;

/**
 * @coversDefaultClass \DasRed\Translation\Exception\FileNotFound
 */
class FileNotFoundTest extends \PHPUnit_Framework_TestCase
{

	public function testExtends()
	{
		$exception = new FileNotFound('a', 'b', 'c');
		$this->assertTrue($exception instanceof Exception);
	}

	public function dataProviderConstruct()
	{
		return [
			['A', 'b', 'C', 'Translation file "C" not found for locale "b" in "A"!'],
			['nuff/a/b', 'de-DE', 'lol.php', 'Translation file "lol.php" not found for locale "de-DE" in "nuff/a/b"!'],
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
		$exception = new FileNotFound($path, $locale, $file);

		$this->assertEquals($expectedMessage, $exception->getMessage());
	}
}