<?php
namespace DasRedTest\Translation\Command;

use DasRed\Translation\Command\Factory;
use DasRed\Translation\Command\Executor\Translation;
use DasRed\Translation\Command\Executor\Log;
use Zend\Console\Adapter\AdapterInterface;
use DasRed\Translation\Command\Exception\InvalidCommandOperation;

/**
 * @coversDefaultClass \DasRed\Translation\Command\Factory
 */
class FactoryTest extends \PHPUnit_Framework_TestCase
{

	protected $console;

	public function setUp()
	{
		parent::setUp();

		$this->console = $this->getMockBuilder(AdapterInterface::class)->setMethods(['write', 'writeLine'])->getMockForAbstractClass();
	}

	public function tearDown()
	{
		$this->console = null;
	}

	/**
	 * @covers ::__construct
	 */
	public function test__construct()
	{
		$factory = new Factory($this->console);

		$this->assertSame($this->console, $factory->getConsole());
	}

	public function dataProviderFactorySuccess()
	{
		return [
			[Translation\Sort::class, ['translation', 'sort', '/nuff']],
			[Log\Parse::class, ['log', 'parse', 'nuff']],
		];
	}

	/**
	 * @covers ::factory
	 * @dataProvider dataProviderFactorySuccess
	 */
	public function testFactorySuccess($instance, $arguments)
	{
		$factory = new Factory($this->console);

		$exec = $factory->factory($arguments);

		$this->assertInstanceOf($instance, $exec);
		$this->assertSame($this->console, $exec->getConsole());
		$this->assertEquals(array_slice($arguments, 2), $exec->getArguments());
	}

	/**
	 * @covers ::factory
	 */
	public function testFactoryFailedByCommand()
	{
		$factory = new Factory($this->console);

		$this->setExpectedException(InvalidCommandOperation::class);
		$exec = $factory->factory(['jkgfvneswalkfnde', 'gfnjedsaonfvcda']);
	}

	/**
	 * @covers ::factory
	 */
	public function testFactoryFailedByOperation()
	{
		$factory = new Factory($this->console);

		$this->setExpectedException(InvalidCommandOperation::class);
		$exec = $factory->factory(['translation', 'gfnjedsaonfvcda']);
	}
}
