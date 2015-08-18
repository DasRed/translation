<?php
namespace DasRedTest\Translation\Command\Executor\Log;

use DasRed\Translation\Command\Executor\Log\ToCsv;
use Zend\Console\Adapter\AdapterInterface;
use Zend\Console\ColorInterface;

/**
 * @coversDefaultClass \DasRed\Translation\Command\Executor\Log\ToCsv
 */
class ToCsvTest extends \PHPUnit_Framework_TestCase
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
	public function testExecuteWithFileExists()
	{
		file_put_contents(__DIR__ . '/csv.csv', 'abnc');

		$exec = $this->getMockBuilder(ToCsv::class)->setMethods(['write'])->setConstructorArgs([$this->console, [__DIR__ . '/translations.log', __DIR__ . '/csv.csv']])->getMock();
		$exec->expects($this->exactly(8))->method('write')->withConsecutive(
			['File', 'LineNumber', 'Key', 'Locale', 'Count of Matches'],
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
	public function testExecuteWithoutFileExists()
	{
		if (file_exists(__DIR__ . '/csv.csv') === true)
		{
			unlink(__DIR__ . '/csv.csv');
		}

		$exec = $this->getMockBuilder(ToCsv::class)->setMethods(['write'])->setConstructorArgs([$this->console, [__DIR__ . '/translations.log', __DIR__ . '/csv.csv']])->getMock();
		$exec->expects($this->exactly(8))->method('write')->withConsecutive(
			['File', 'LineNumber', 'Key', 'Locale', 'Count of Matches'],
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
	 * @covers ::write
	 */
	public function testWrite()
	{
		$exec = new ToCsv($this->console, [__DIR__ . '/nuff', __DIR__ . '/csv.csv']);

		$reflectionMethod = new \ReflectionMethod($exec, 'write');
		$reflectionMethod->setAccessible(true);

		$this->assertSame($exec, $reflectionMethod->invoke($exec, 'a', 2, 'c', 'de', 32));
		$this->assertFileExists(__DIR__ . '/csv.csv');
		$this->assertSame("a;2;c;de;32\n", file_get_contents(__DIR__ . '/csv.csv'));
		unlink(__DIR__ . '/csv.csv');
	}

	/**
	 * @covers ::getMaxCountOfArguments
	 */
	public function testGetMaxCountOfArguments()
	{
		$exec = new ToCsv($this->console, [__DIR__ . '/nuff', __DIR__ . '/nuff']);

		$reflectionMethod = new \ReflectionMethod($exec, 'getMaxCountOfArguments');
		$reflectionMethod->setAccessible(true);

		$this->assertSame(2, $reflectionMethod->invoke($exec));
	}
}