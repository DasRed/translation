<?php
namespace DasRedTest\Translation\Command\Executor\Log;

use DasRed\Translation\Command\Executor\Log\Parse;
use Zend\Console\Adapter\AdapterInterface;
use Zend\Console\ColorInterface;

/**
 * @coversDefaultClass \DasRed\Translation\Command\Executor\Log\Parse
 */
class ParseTest extends \PHPUnit_Framework_TestCase
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
	public function testExecuteSuccess()
	{
		$exec = $this->getMockBuilder(Parse::class)->setMethods(['write'])->setConstructorArgs([$this->console, [__DIR__ . '/translations.log']])->getMock();
		$exec->expects($this->exactly(7))->method('write')->withConsecutive(
			['/var/www/myposter/web/application/helpers/Accessories.php', 37, 'alu-galerie-aufhaengung-description', 'de-DE', 1],
			['/var/www/myposter/web/application/helpers/ProductOverview.php', 37, 'landschaften', 'de-DE', 2],
			['/var/www/myposter/web/application/helpers/ProductOverview.php', 37, 'praemierte-fotos', 'de-DE', 1],
			['/var/www/myposter/web/application/helpers/ProductOverview.php', 37, 'staedte', 'de-DE', 1],
			['/var/www/myposter/web/application/modules/admin/forms/Search.php', 45, 'customer-email', 'de-DE', 1],
			['/var/www/myposter/web/application/modules/admin/forms/Search.php', 45, 'customer-name', 'de-DE', 1],
			['/var/www/myposter/web/application/modules/admin/views/scripts/order/index.phtml', 34, 'Übersicht der Bestellungen', 'de-DE', 1]
		)->willReturnSelf();

		$this->console->expects($this->never())->method('write');
		$this->console->expects($this->once())->method('writeLine')->with('Done.', ColorInterface::BLACK, ColorInterface::LIGHT_GREEN)->willReturnSelf();

		$this->assertTrue($exec->execute());
	}

	/**
	 * @covers ::execute
	 */
	public function testExecuteFailed()
	{
		$exec = new Parse($this->console, [__DIR__ . '/nuff']);

		$this->console->expects($this->never())->method('write');
		$this->console->expects($this->once())->method('writeLine')->with('File can not be parsed. Maybe the file does not exists.', ColorInterface::LIGHT_YELLOW, ColorInterface::LIGHT_RED);
		$this->assertFalse($exec->execute());
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
		$exec = new Parse($this->console, [1]);

		$reflectionMethod = new \ReflectionMethod($exec, 'validateArguments');
		$reflectionMethod->setAccessible(true);

		$this->assertSame($expected, $reflectionMethod->invoke($exec, $arguments));
	}

	/**
	 * @covers ::write
	 */
	public function testWrite()
	{
		$exec = new Parse($this->console, [__DIR__ . '/nuff']);

		$this->console->expects($this->exactly(7))->method('write')->withConsecutive(
			['a', ColorInterface::LIGHT_GREEN],
			[' '],
			['#2', ColorInterface::LIGHT_CYAN],
			[' with key '],
			['c', ColorInterface::LIGHT_YELLOW],
			[' and locale '],
			['de', ColorInterface::LIGHT_MAGENTA]
		)->willReturnSelf();

		$this->console->expects($this->once())->method('writeLine')->with(' (32 calls)')->willReturnSelf();

		$reflectionMethod = new \ReflectionMethod($exec, 'write');
		$reflectionMethod->setAccessible(true);

		$this->assertSame($exec, $reflectionMethod->invoke($exec, 'a', 2, 'c', 'de', 32));
	}

	/**
	 * @covers ::getMaxCountOfArguments
	 */
	public function testGetMaxCountOfArguments()
	{
		$exec = new Parse($this->console, [__DIR__ . '/nuff']);

		$reflectionMethod = new \ReflectionMethod($exec, 'getMaxCountOfArguments');
		$reflectionMethod->setAccessible(true);

		$this->assertSame(1, $reflectionMethod->invoke($exec));
	}
}