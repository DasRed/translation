<?php
namespace DasRedTest\Translation\Command\Executor\Translation;

use DasRed\Translation\Command\Executor\Translation\Sort;
use Zend\Console\Adapter\AdapterInterface;
use Zend\Console\ColorInterface;

/**
 * @coversDefaultClass \DasRed\Translation\Command\Executor\Translation\Sort
 */
class StoreTest extends \PHPUnit_Framework_TestCase
{

	protected $console;

	protected $path;

	public function setUp()
	{
		parent::setUp();

		$this->console = $this->getMockBuilder(AdapterInterface::class)->setMethods(['write', 'writeLine'])->getMockForAbstractClass();

		$this->path = __DIR__ . '/translations';
		$this->clearPath();
		mkdir($this->path, 0777, true);
		mkdir($this->path . '/de-DE', 0777, true);
		file_put_contents($this->path . '/de-DE/nuff.php', "<?php\n\nreturn [\n'd' => 'c',\n\t'a' => 'f',\n];\n");
	}

	public function tearDown()
	{
		parent::tearDown();

		$this->console = null;
		$this->clearPath();
	}

	protected function clearPath()
	{
		if (file_exists($this->path) === true)
		{
			$iterator = new \RecursiveDirectoryIterator($this->path);
			foreach (new \RecursiveIteratorIterator($iterator, \RecursiveIteratorIterator::CHILD_FIRST) as $file)
			{
				if ($file->isDir() === true)
				{
					if (in_array($file->getBasename(), ['.', '..']) === false)
					{
						rmdir($file->getPathname());
					}
				}
				else
				{
					unlink($file->getPathname());
				}
			}
			rmdir($this->path);
		}
	}

	public function dataProviderExecute()
	{
		return [
			[[__DIR__ . '/translations'], true],
			[[__DIR__ . '/nuff'], false],
		];
	}

	/**
	 * @covers ::execute
	 * @dataProvider dataProviderExecute
	 */
	public function testExecute($arguments, $expected)
	{
		$exec = new Sort($this->console, $arguments);

		$this->console->expects($this->never())->method('write');
		if ($expected === true)
		{
			$this->console->expects($this->once())->method('writeLine')->with('Translations sorted.', ColorInterface::BLACK, ColorInterface::LIGHT_GREEN);
		}
		else
		{
			$this->console->expects($this->once())->method('writeLine')->with('Translations can not be sorted. Maybe the path is wrong.', ColorInterface::LIGHT_YELLOW, ColorInterface::LIGHT_RED);
		}

		$this->assertSame($expected, $exec->execute());

		if ($expected === true)
		{
			$this->assertSame("<?php\n\nreturn [\n\t'a' => 'f',\n\t'd' => 'c',\n];\n", file_get_contents($this->path . '/de-DE/nuff.php'));
		}
		else
		{
			$this->assertSame("<?php\n\nreturn [\n'd' => 'c',\n\t'a' => 'f',\n];\n", file_get_contents($this->path . '/de-DE/nuff.php'));
		}
	}

	public function dataProviderValidateArguments()
	{
		return [
			[['a', 'b', 'c'], false],
			[['a', 'b', ''], false],
			[['a', 'b'], false],
			[['', 'b'], false],
			[['a', ''], false],
			[[null, 'b'], false],
			[['b', null], false],
			[[], false],
			[[1], true],
			[[1, 2, 3, 4, 45], false],
		];
	}

	/**
	 * @covers ::validateArguments
	 * @dataProvider dataProviderValidateArguments
	 */
	public function testValidateArguments($arguments, $expected)
	{
		$exec = new Sort($this->console, [1]);

		$reflectionMethod = new \ReflectionMethod($exec, 'validateArguments');
		$reflectionMethod->setAccessible(true);

		$this->assertSame($expected, $reflectionMethod->invoke($exec, $arguments));
	}
}