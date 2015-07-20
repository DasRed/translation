<?php
namespace DasRedTest\Translation;

use Zend\Log\Logger;
use Zend\Log\Writer\Mock;
use DasRed\Parser\BBCode;
use DasRed\Translation\Translator;
use DasRed\Translation\Exception\PathCanNotBeNull;
use DasRed\Translation\Exception\DefaultLocaleCanNotBeNull;
use DasRed\Translation\Exception\FileNotFound;
use DasRed\Translation\Exception\InvalidTranslationFile;
use DasRed\Translation\Exception\TranslationKeyNotFound;
use DasRed\Translation\Exception\TranslationKeyIsNotAString;
/**
 * @coversDefaultClass \DasRed\Translation\Translator
 */
class TranslatorTest extends \PHPUnit_Framework_TestCase
{

	protected $logger;

	protected $logWriter;

	protected $markupRenderer;

	protected $path;

	public function setUp()
	{
		parent::setUp();

		$this->logWriter = new Mock();
		$this->logger = new Logger();
		$this->logger->addWriter($this->logWriter);
		$this->markupRenderer = new BBCode(__DIR__ . '/config/bbcode.php');
		$this->path = __DIR__ . '/translation';
	}

	/**
	 * (non-PHPdoc)
	 * @see PHPUnit_Framework_TestCase::tearDown()
	 */
	public function tearDown()
	{
		parent::tearDown();

		$this->logger = null;
		$this->logWriter = null;
		$this->markupRenderer = null;
		$this->path = null;
	}

	/**
	 * @covers ::__construct
	 */
	public function testConstructor()
	{
		$translator = new Translator('de-DE', $this->path, $this->logger, $this->markupRenderer);
		$this->assertSame('de-DE', $translator->getDefaultLocale());
		$this->assertSame($this->path . '/', $translator->getPath());
		$this->assertSame($this->logger, $translator->getLogger());
		$this->assertSame($this->markupRenderer, $translator->getMarkupRenderer());

		$translator = new Translator('de-DE', $this->path);
		$this->assertSame('de-DE', $translator->getDefaultLocale());
		$this->assertSame($this->path . '/', $translator->getPath());
		$this->assertNull($translator->getLogger());
		$this->assertNull($translator->getMarkupRenderer());
	}

	public function dataProvider__()
	{
		return [
			['de-DE', 'test.a', [], null, null, true, true, 'c'],
			['de-DE', 'test.key', [], null, null, true, true, 'value'],
			['de-DE', 'test.param1', [], null, null, true, true, '[p1] und [PARAMeter] und [PARAMETER] & [PARAM]'],
			['de-DE', 'test.param2', [], null, null, true, true, '[p1] und {PARAMeter} und {PARAMETER} & {PARAM}'],
			['de-DE', 'other.a', [], null, null, true, true, 'cother'],
			['de-DE', 'other.key', [], null, null, true, true, 'valueother'],
			['de-DE', 'other.a.b.c', [], null, null, true, true, 'gkjreqwbgukie'],

			['de-DE', 'other.bb', [], null, null, true, true, '<strong>bbcode</strong> bb'],
			['de-DE', 'other.bb', [], null, null, false, true, '[b]bbcode[/b] bb'],
			['de-DE', 'other.bb', [], null, null, true, false, '[b]bbcode[/b] bb'],
			['de-DE', 'other.bb', [], null, null, false, false, '[b]bbcode[/b] bb'],

			['de-DE', 'other.nuff', [], 'en-US', null, true, true, 'narf'],
			['de-DE', 'other.lol', [], 'en-US', null, true, true, 'rofl'],

			['de-DE', 'test.a', ['c' => 'd'], null, null, true, true, 'c'],
			['de-DE', 'test.param1', ['p1' => 'jo'], null, null, true, true, 'jo und [PARAMeter] und [PARAMETER] & [PARAM]'],

			['de-DE', 'testparam1', ['p1' => 'jo'], null, null, true, false, '[b][color=#F00]%%testparam1%% (de-DE)[/color][/b]'],
			['de-DE', 'testparam1', ['p1' => 'jo'], null, 'narfnarfnarfnarf', true, false, 'narfnarfnarfnarf'],

			['de-DE', 'other.a', [], 'en-US', null, false, false, 'cother'],
		];
	}

	/**
	 * @covers ::__
	 * @dataProvider dataProvider__
	 */
	public function test__($defaultLocale, $key, array $parameters, $locale, $default, $parserInjected, $parseBBCode, $expected)
	{
		$translation = new Translator($defaultLocale, $this->path, $this->logger);
		if ($parserInjected)
		{
			$translation->setMarkupRenderer($this->markupRenderer);
		}

		$this->assertEquals($expected, $translation->__($key, $parameters, $locale, $default, $parseBBCode));
	}

	/**
	 * @covers ::get
	 */
	public function testGet()
	{
		$translator = new Translator('de-DE', $this->path);

		$reflectionMethod = new \ReflectionMethod($translator, 'get');
		$reflectionMethod->setAccessible(true);

		$this->assertSame('[p1] und {PARAMeter} und {PARAMETER} & {PARAM}', $reflectionMethod->invoke($translator, 'de-DE', 'test', 'param2'));
	}

	/**
	 * @covers ::get
	 */
	public function testGetFailedKeyNotFound()
	{
		$translator = new Translator('de-DE', $this->path);

		$reflectionMethod = new \ReflectionMethod($translator, 'get');
		$reflectionMethod->setAccessible(true);

		$this->setExpectedException(TranslationKeyNotFound::class);
		$reflectionMethod->invoke($translator, 'de-CH', 'foo', 'assfdsagfdsaztg54rwzhg564ezh65rte');
	}

	/**
	 * @covers ::get
	 */
	public function testGetFailedKeyNotString()
	{
		$translator = new Translator('de-DE', $this->path);

		$reflectionMethod = new \ReflectionMethod($translator, 'get');
		$reflectionMethod->setAccessible(true);

		$this->setExpectedException(TranslationKeyIsNotAString::class);
		$reflectionMethod->invoke($translator, 'de-CH', 'foo', 'a');
	}

	/**
	 * @covers ::getAll
	 */
	public function testGetAll()
	{
		$translator = new Translator('de-DE', $this->path);

		// with default locale
		$translations = $translator->getAll();

		$this->assertCount(1, $translations);
		$this->assertArrayHasKey('de-DE', $translations);

		$this->assertCount(2, $translations['de-DE']);
		$this->assertArrayHasKey('test', $translations['de-DE']);
		$this->assertArrayHasKey('other', $translations['de-DE']);

		$this->assertEquals([
			'a' => 'c',
			'key' => 'value',
			'param1' => '[p1] und [PARAMeter] und [PARAMETER] & [PARAM]',
			'param2' => '[p1] und {PARAMeter} und {PARAMETER} & {PARAM}'
		], $translations['de-DE']['test']);
		$this->assertEquals([
			'a' => 'cother',
			'key' => 'valueother',
			'a.b.c' => 'gkjreqwbgukie',
			'bb' => '[b]bbcode[/b] bb'
		], $translations['de-DE']['other']);

		// with none default locale after default
		$translations = $translator->getAll('en-US');

		$this->assertCount(2, $translations);
		$this->assertArrayHasKey('de-DE', $translations);
		$this->assertArrayHasKey('en-US', $translations);

		// de-DE
		$this->assertCount(2, $translations['de-DE']);
		$this->assertArrayHasKey('test', $translations['de-DE']);
		$this->assertArrayHasKey('other', $translations['de-DE']);

		// de-DE test
		$this->assertEquals([
			'a' => 'c',
			'key' => 'value',
			'param1' => '[p1] und [PARAMeter] und [PARAMETER] & [PARAM]',
			'param2' => '[p1] und {PARAMeter} und {PARAMETER} & {PARAM}'
		], $translations['de-DE']['test']);

		// de-DE other
		$this->assertEquals([
			'a' => 'cother',
			'key' => 'valueother',
			'a.b.c' => 'gkjreqwbgukie',
			'bb' => '[b]bbcode[/b] bb'
		], $translations['de-DE']['other']);

		// en-US
		$this->assertCount(2, $translations['en-US']);
		$this->assertArrayHasKey('cookie', $translations['en-US']);
		$this->assertArrayHasKey('other', $translations['en-US']);

		// en-US cookie
		$this->assertEquals(['roflcopter' => 'wtf'], $translations['en-US']['cookie']);

		// en-US other
		$this->assertEquals([
			'nuff' => 'narf',
			'lol' => 'rofl'
		], $translations['en-US']['other']);
	}

	/**
	 * @covers ::getDefaultLocale
	 * @covers ::setDefaultLocale
	 */
	public function testGetSetDefaultLocale()
	{
		$translator = new Translator('de-DE', $this->path);

		$this->assertSame('de-DE', $translator->getDefaultLocale());
		$this->assertSame($translator, $translator->setDefaultLocale('en'));
		$this->assertSame('en', $translator->getDefaultLocale());
	}

	/**
	 * @covers ::setDefaultLocale
	 */
	public function testSetDefaultLocaleFailed()
	{
		$translator = new Translator('de-DE', $this->path);

		$this->setExpectedException(DefaultLocaleCanNotBeNull::class);
		$translator->setDefaultLocale(null);
	}

	/**
	 * @covers ::getLogger
	 * @covers ::setLogger
	 */
	public function testGetSetLogger()
	{
		$logger1 = (new Logger())->addWriter($this->logWriter);
		$logger2 = (new Logger())->addWriter($this->logWriter);

		$translator = new Translator('de-DE', $this->path);

		$this->assertNull($translator->getLogger());
		$this->assertSame($translator, $translator->setLogger($logger1));
		$this->assertSame($logger1, $translator->getLogger());
		$this->assertSame($translator, $translator->setLogger($logger2));
		$this->assertSame($logger2, $translator->getLogger());
		$this->assertSame($translator, $translator->setLogger(null));
		$this->assertNull($translator->getLogger());
	}

	/**
	 * @covers ::getMarkupRenderer
	 * @covers ::setMarkupRenderer
	 */
	public function testGetSetMarkupRenderer()
	{
		$markupRenderer1 = new BBCode(null);
		$markupRenderer2 = new BBCode(null);

		$translator = new Translator('de-DE', $this->path);

		$this->assertNull($translator->getMarkupRenderer());
		$this->assertSame($translator, $translator->setMarkupRenderer($markupRenderer1));
		$this->assertSame($markupRenderer1, $translator->getMarkupRenderer());
		$this->assertSame($translator, $translator->setMarkupRenderer($markupRenderer2));
		$this->assertSame($markupRenderer2, $translator->getMarkupRenderer());
		$this->assertSame($translator, $translator->setMarkupRenderer(null));
		$this->assertNull($translator->getMarkupRenderer());
	}

	/**
	 * @covers ::getPath
	 * @covers ::setPath
	 */
	public function testGetSetPath()
	{
		$translator = new Translator('de-DE', $this->path);

		$this->assertSame($this->path . '/', $translator->getPath());
		$this->assertSame($translator, $translator->setPath(__DIR__));
		$this->assertSame(__DIR__ . '/', $translator->getPath());
		$this->assertSame($translator, $translator->setPath($this->path . '/'));
		$this->assertSame($this->path . '/', $translator->getPath());
	}

	/**
	 * @covers ::setPath
	 */
	public function testSetPathFailed()
	{
		$translator = new Translator('de-DE', $this->path);

		$this->setExpectedException(PathCanNotBeNull::class);
		$translator->setPath(null);
	}

	/**
	 * @covers ::getPlaceholderPrefix
	 * @covers ::setPlaceholderPrefix
	 */
	public function testGetSetPlaceholderPrefix()
	{
		$translator = new Translator('de-DE', $this->path);

		$this->assertSame('[', $translator->getPlaceholderPrefix());
		$this->assertSame($translator, $translator->setPlaceholderPrefix('{'));
		$this->assertSame('{', $translator->getPlaceholderPrefix());
	}

	/**
	 * @covers ::getPlaceholderSuffix
	 * @covers ::setPlaceholderSuffix
	 */
	public function testGetSetPlaceholderSuffix()
	{
		$translator = new Translator('de-DE', $this->path);

		$this->assertSame(']', $translator->getPlaceholderSuffix());
		$this->assertSame($translator, $translator->setPlaceholderSuffix('}'));
		$this->assertSame('}', $translator->getPlaceholderSuffix());
	}

	/**
	 * @covers ::isFileLoaded
	 */
	public function testIsFileLoaded()
	{
		$translator = new Translator('de-DE', $this->path);

		$reflectionMethod = new \ReflectionMethod($translator, 'isFileLoaded');
		$reflectionMethod->setAccessible(true);

		$this->assertFalse($reflectionMethod->invoke($translator, 'de-DE', 'nuff'));

		$translator->__('test.a');
		$this->assertFalse($reflectionMethod->invoke($translator, 'de-DE', 'nuff'));

		$this->assertTrue($reflectionMethod->invoke($translator, 'de-DE', 'test'));
	}

	/**
	 * @covers ::load
	 */
	public function testLoad()
	{
		$translator = new Translator('de-DE', $this->path, $this->logger);

		$reflectionMethod = new \ReflectionMethod($translator, 'load');
		$reflectionMethod->setAccessible(true);

		$reflectionProperty = new \ReflectionProperty($translator, 'translations');
		$reflectionProperty->setAccessible(true);

		$this->assertTrue($reflectionMethod->invoke($translator, 'de-DE', 'test'));
		$this->assertCount(1, $this->logWriter->events);
		$this->assertStringStartsWith('Language loaded: de-DE/test (', $this->logWriter->events[0]['message']);
		$this->assertSame(Logger::DEBUG, $this->logWriter->events[0]['priority']);
		$this->assertEquals([
			'de-DE' => [
				'test' => [
					'a' => 'c',
					'key' => 'value',
					'param1' => '[p1] und [PARAMeter] und [PARAMETER] & [PARAM]',
					'param2' => '[p1] und {PARAMeter} und {PARAMETER} & {PARAM}',
				]
			]
		], $reflectionProperty->getValue($translator));

		$this->assertTrue($reflectionMethod->invoke($translator, 'de-DE', 'test'));
		$this->assertCount(1, $this->logWriter->events);
		$this->assertStringStartsWith('Language loaded: de-DE/test (', $this->logWriter->events[0]['message']);
		$this->assertSame(Logger::DEBUG, $this->logWriter->events[0]['priority']);
		$this->assertEquals([
			'de-DE' => [
				'test' => [
					'a' => 'c',
					'key' => 'value',
					'param1' => '[p1] und [PARAMeter] und [PARAMETER] & [PARAM]',
					'param2' => '[p1] und {PARAMeter} und {PARAMETER} & {PARAM}',
				]
			]
		], $reflectionProperty->getValue($translator));
	}

	/**
	 * @covers ::load
	 */
	public function testLoadFailedNotFound()
	{
		$translator = new Translator('de-DE', $this->path, $this->logger);

		$reflectionMethod = new \ReflectionMethod($translator, 'load');
		$reflectionMethod->setAccessible(true);

		$this->setExpectedException(FileNotFound::class);
		$reflectionMethod->invoke($translator, 'zh-CN-Hans', 'test');

		$this->assertCount(0, $this->logWriter->events);
	}

	/**
	 * @covers ::load
	 */
	public function testLoadFailedInvalidFile()
	{
		$translator = new Translator('de-DE', $this->path, $this->logger);

		$reflectionMethod = new \ReflectionMethod($translator, 'load');
		$reflectionMethod->setAccessible(true);

		$this->setExpectedException(InvalidTranslationFile::class);
		$reflectionMethod->invoke($translator, 'de-CH', 'test');

		$this->assertCount(0, $this->logWriter->events);
	}

	/**
	 * @covers ::log
	 */
	public function testLog()
	{
		$translator = new Translator('de-DE', $this->path, $this->logger);

		$reflectionMethod = new \ReflectionMethod($translator, 'log');
		$reflectionMethod->setAccessible(true);

		$this->assertSame($translator, $reflectionMethod->invoke($translator, 'test', Logger::ALERT));
		$this->assertCount(1, $this->logWriter->events);
		$this->assertSame('test', $this->logWriter->events[0]['message']);
		$this->assertSame(Logger::ALERT, $this->logWriter->events[0]['priority']);

		$translator->setLogger(null);

		$this->assertSame($translator, $reflectionMethod->invoke($translator, 'nuff', Logger::DEBUG));
		$this->assertCount(1, $this->logWriter->events);
		$this->assertSame('test', $this->logWriter->events[0]['message']);
		$this->assertSame(Logger::ALERT, $this->logWriter->events[0]['priority']);
	}

	public function dataProviderParseParameters()
	{
		return [
			['[', ']', 'c', [], 'c'],
			['[', ']', 'c', ['nuff' => 'rofl'], 'c'],
			['[', ']', 'c', ['c' => 'rofl'], 'c'],

			['[', ']', '[p1] und [PARAMeter] und [PARAMETER] & [PARAM]', [], '[p1] und [PARAMeter] und [PARAMETER] & [PARAM]'],
			['[', ']', '[p1] und [PARAMeter] und [PARAMETER] & [PARAM]', ['P1' => 'p1 value', 'parameter' => 'parameter Value', 'PARAM' => 'param value'], 'p1 value und parameter Value und parameter Value & param value'],
			['[', ']', '[p1] und [PARAMeter] und [PARAMETER] & [PARAM]', ['p1' => 'p1 value', 'paramETER' => 'parameter Value', 'param' => 'param value'], 'p1 value und parameter Value und parameter Value & param value'],
			['[', ']', '[p1] und [PARAMeter] und [PARAMETER] & [PARAM]', ['p1' => 'p1 value', 'PARAMETER' => 'parameter Value', 'Param' => 'param value'], 'p1 value und parameter Value und parameter Value & param value'],

			['[', ']', '[p1] und {PARAMeter} und {PARAMETER} & {PARAM}', [], '[p1] und {PARAMeter} und {PARAMETER} & {PARAM}'],
			['[', ']', '[p1] und {PARAMeter} und {PARAMETER} & {PARAM}', ['p1' => 'p1 value', 'PARAMETER' => 'parameter Value', 'Param' => 'param value'], 'p1 value und {PARAMeter} und {PARAMETER} & {PARAM}'],
			['{', '}', '[p1] und {PARAMeter} und {PARAMETER} & {PARAM}', ['p1' => 'p1 value', 'PARAMETER' => 'parameter Value', 'Param' => 'param value'], '[p1] und parameter Value und parameter Value & param value'],
		];
	}

	/**
	 * @covers ::parseParameters
	 * @dataProvider dataProviderParseParameters
	 */
	public function testParseParameters($prefix, $suffix, $text, array $parameters, $exptected)
	{
		$translator = (new Translator('de-DE', $this->path))->setPlaceholderPrefix($prefix)->setPlaceholderSuffix($suffix);

		$reflectionMethod = new \ReflectionMethod($translator, 'parseParameters');
		$reflectionMethod->setAccessible(true);

		$this->assertSame($exptected, $reflectionMethod->invoke($translator, $text, $parameters));
	}
}