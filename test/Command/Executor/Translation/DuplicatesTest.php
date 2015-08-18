<?php
namespace DasRedTest\Translation\Command\Executor\Translation;

use DasRed\Translation\Command\Executor\Translation\Duplicates;
use Zend\Console\Adapter\AdapterInterface;

/**
 * @coversDefaultClass \DasRed\Translation\Command\Executor\Translation\Duplicates
 */
class DuplicatesTest extends \PHPUnit_Framework_TestCase
{

	protected $console;

	public function setUp()
	{
		parent::setUp();

		$this->console = $this->getMockBuilder(AdapterInterface::class)->setMethods(['write', 'writeLine'])->getMockForAbstractClass();
	}

	public function tearDown()
	{
		parent::tearDown();

		$this->console = null;
	}

	/**
	 * @covers ::execute
	 */
	public function testExecute()
	{
		$this->markTestIncomplete();
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
		$exec = new Duplicates($this->console, [1]);

		$reflectionMethod = new \ReflectionMethod($exec, 'validateArguments');
		$reflectionMethod->setAccessible(true);

		$this->assertSame($expected, $reflectionMethod->invoke($exec, $arguments));
	}
}