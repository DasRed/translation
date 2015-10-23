<?php
namespace DasRedTest\Translation;

use DasRed\Translation\Locale;
/**
 * @coversDefaultClass \DasRed\Translation\Locale
 */
class LocaleTest extends \PHPUnit_Framework_TestCase
{

	/**
	 *
	 * @param object $object
	 * @param string $name
	 * @param array $args
	 */
	protected function invokeMethod($object, $name, $args = array())
	{
		$reflectionMethod = new \ReflectionMethod($object, $name);
		$reflectionMethod->setAccessible(true);

		return $reflectionMethod->invokeArgs($object, $args);
	}

	public function dataProviderConstruct()
	{
		return [
			['de-DE', 'de-DE', null, true],
			['de_DE', 'de-DE', null, true],
			['ru_RU', 'ru-RU', false, false],
			['ru_RU', 'ru-RU', true, true],
			['ru_RU', 'ru-RU', false, false],
			['ru_RU', 'ru-RU', true, true],
			['ru_RU', 'ru-RU', false, false],
			['ru_RU', 'ru-RU', true, true],
		];
	}

	/**
	 * @covers ::__construct
	 * @dataProvider dataProviderConstruct
	 */
	public function testConstruct($name, $nameExpected, $enabled, $enabledExcpected)
	{
		if ($enabled === null)
		{
			$locale = new Locale($name);
		}
		else
		{
			$locale = new Locale($name, $enabled);
		}

		$this->assertSame($nameExpected, $locale->getName());
		$this->assertSame($enabledExcpected, $locale->isEnabled());
	}

	/**
	 * @covers ::getName
	 * @covers ::setName
	 */
	public function testGetSetName()
	{
		$locale = new Locale('de-DE');

		$this->assertSame('de-DE', $locale->getName());
		$this->assertSame($locale, $this->invokeMethod($locale, 'setName', ['ru-RU']));
		$this->assertSame('ru-RU', $locale->getName());
		$this->assertSame($locale, $this->invokeMethod($locale, 'setName', ['de_DE']));
		$this->assertSame('de-DE', $locale->getName());
	}

	public function dataProviderGetLanguage()
	{
		return [
			['de-DE', 'de'],
			['ru-RU', 'ru'],
			['fr-RU', 'fr'],
			['fr_FR', 'fr'],
			['FR-RU', 'fr'],
			['FR_FR', 'fr'],
		];
	}

	/**
	 * @covers ::getLanguage
	 * @dataProvider dataProviderGetLanguage
	 */
	public function testGetLanguage($name, $expected)
	{
		$locale = new Locale($name);

		$this->assertSame($expected, $locale->getLanguage());
	}

	public function dataProviderGetRegion()
	{
		return [
			['de-DE', 'DE'],
			['ru-RU', 'RU'],
			['fr-RU', 'RU'],
			['fr_FR', 'FR'],
			['fr', null],
			['fr-ru', 'RU'],
			['fr_fr', 'FR'],
			['fr', null],
		];
	}

	/**
	 * @covers ::getRegion
	 * @dataProvider dataProviderGetRegion
	 */
	public function testGetRegion($name, $expected)
	{
		$locale = new Locale($name);

		$this->assertSame($expected, $locale->getRegion());
	}

	/**
	 * @covers ::isEnabled
	 * @covers ::setEnabled
	 */
	public function testIsSetEnabled()
	{
		$locale = new Locale('de-DE', false, false);

		$this->assertSame(false, $locale->isEnabled());
		$this->assertSame($locale, $this->invokeMethod($locale, 'setEnabled', [true]));
		$this->assertSame(true, $locale->isEnabled());
		$this->assertSame($locale, $this->invokeMethod($locale, 'setEnabled', [0]));
		$this->assertSame(false, $locale->isEnabled());
	}

	/**
	 * @covers ::__toString
	 */
	public function testToString()
	{
		$locale = new Locale('de-DE');

		$this->assertSame('de-DE', $locale->getName());
		$this->assertSame('de-DE', $locale->__toString());
		$this->assertSame('de-DE', (string)$locale);

		$this->invokeMethod($locale, 'setName', ['ru-RU']);
		$this->assertSame('ru-RU', $locale->getName());
		$this->assertSame('ru-RU', $locale->__toString());
		$this->assertSame('ru-RU', (string)$locale);

		$this->invokeMethod($locale, 'setName', ['de_DE']);
		$this->assertSame('de-DE', $locale->getName());
		$this->assertSame('de-DE', $locale->__toString());
		$this->assertSame('de-DE', (string)$locale);
	}
}
