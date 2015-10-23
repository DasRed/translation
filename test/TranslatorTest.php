<?php
namespace DasRedTest\Translation;

use Zend\Log\Logger;
use Zend\Log\Writer\Mock;
use DasRed\Parser\BBCode;
use DasRed\Translation\Translator;
use DasRed\Translation\Exception\PathCanNotBeNull;
use DasRed\Translation\Exception\FileNotFound;
use DasRed\Translation\Exception\InvalidTranslationFile;
use DasRed\Translation\Exception\TranslationKeyNotFound;
use DasRed\Translation\Exception\TranslationKeyIsNotAString;
use DasRed\Translation\Exception\InvalidTranslationKey;
use DasRed\Translation\Locale;
use DasRed\Translation\Locale\Collection;

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
		$locales = new Collection([new Locale('de-DE'), new Locale('de-CH')]);

		$translator = new Translator($locales, $this->path, $this->logger, $this->markupRenderer);
		$this->assertSame($locales, $translator->getLocales());
		$this->assertSame($this->path . '/', $translator->getPath());
		$this->assertSame($this->logger, $translator->getLogger());
		$this->assertSame($this->markupRenderer, $translator->getMarkupRenderer());

		$locales = new Collection([new Locale('de-DE'), new Locale('de-CH')]);
		$translator = new Translator($locales, $this->path);
		$this->assertSame($locales, $translator->getLocale());
		$this->assertSame($this->path . '/', $translator->getPath());
		$this->assertNull($translator->getLogger());
		$this->assertNull($translator->getMarkupRenderer());
	}

	public function dataProvider__()
	{
		return [
			['de-DE', 'de-DE', 'test.a', [], null, true, true, 'c'],
			['de-DE', 'de-DE', 'test.key', [], null, true, true, 'value'],
			['de-DE', 'de-DE', 'test.param1', [], null, true, true, '[p1] und [PARAMeter] und [PARAMETER] & [PARAM]'],
			['de-DE', 'de-DE', 'test.param2', [], null, true, true, '[p1] und {PARAMeter} und {PARAMETER} & {PARAM}'],
			['de-DE', 'de-DE', 'other.a', [], null, true, true, 'cother'],
			['de-DE', 'de-DE', 'other.key', [], null, true, true, 'valueother'],
			['de-DE', 'de-DE', 'other.a.b.c', [], null, true, true, 'gkjreqwbgukie'],

			['de-DE', 'de-DE', 'other.bb', [], null, true, true, '<strong>bbcode</strong> bb'],
			['de-DE', 'de-DE', 'other.bb', [], null, false, true, '[b]bbcode[/b] bb'],
			['de-DE', 'de-DE', 'other.bb', [], null, true, false, '[b]bbcode[/b] bb'],
			['de-DE', 'de-DE', 'other.bb', [], null, false, false, '[b]bbcode[/b] bb'],

			['de-DE', 'de-DE', 'other.nuff', [], 'en-US', true, true, 'narf'],
			['de-DE', 'de-DE', 'other.lol', [], 'en-US', true, true, 'rofl'],

			['de-DE', 'de-DE', 'test.a', ['c' => 'd'], null, true, true, 'c'],
			['de-DE', 'de-DE', 'test.param1', ['p1' => 'jo'], null, true, true, 'jo und [PARAMeter] und [PARAMETER] & [PARAM]'],

			['de-DE', 'de-DE', 'testparam1', ['p1' => 'jo'], null, true, false, '[b][color=#F00]%%testparam1%% (de-DE)[/color][/b]'],

			['de-DE', 'de-DE', 'other.a', [], 'en-US', false, false, 'cother'],

			['fr-FR', 'fr-CH', 'nuff.narf', [], null, false, false, 'nein'],
			['fr-FR', 'fr-CH', 'nuff.lol', [], null, false, false, 'lachen!'],
			['fr-FR', 'fr-CH', 'nuff.haha', [], null, false, false, '[b][color=#F00]%%nuff.haha%% (fr-CH)[/color][/b]'],
			['fr-FR', 'fr-CH', 'nuff.lol', [], 'de-DE', false, false, 'lachen!'],

			['fr-FR', 'fr-CH', 'other.key', [], 'de-DE', false, false, 'valueother'],

			['ru-RU', 'ru-RU', 'test/a/nuff/module.nuff', [], null, false, false, 'narf'],

			['it-IT', 'it-IT', 'file/test_0/text_0.1.narf', [], null, false, false, 'nuff'],
			['it-IT', 'it-IT', 'file/test.1/text.1.1.lol', [], null, false, false, 'fluffig'],
		];
	}

	/**
	 * @covers ::__
	 * @covers ::handleTranslation
	 * @dataProvider dataProvider__
	 */
	public function test__AndHandleTranslation($localeCurrent, $localeDefault, $key, array $parameters, $locale, $parserInjected, $parseBBCode, $expected)
	{
		$locales = new Collection(new Locale($localeCurrent), new Locale($localeDefault));
		$translation = new Translator($locales, $this->path, $this->logger);
		if ($parserInjected)
		{
			$translation->setMarkupRenderer($this->markupRenderer);
		}

		if ($locale !== null)
		{
			$locale = new Locale($locale);
		}
		$this->assertEquals($expected, $translation->__($key, $parameters, $locale, $parseBBCode));
	}

	/**
	 * @covers ::get
	 */
	public function testGet()
	{
		$locales = new Collection([new Locale('de-DE')]);
		$translator = new Translator($locales, $this->path);

		$reflectionMethod = new \ReflectionMethod($translator, 'get');
		$reflectionMethod->setAccessible(true);

		$this->assertSame('[p1] und {PARAMeter} und {PARAMETER} & {PARAM}', $reflectionMethod->invoke($translator, new Locale('de-DE'), 'test', 'param2'));
	}

	/**
	 * @covers ::get
	 */
	public function testGetFailedKeyNotFound()
	{
		$locales = new Collection([new Locale('de-DE')]);
		$translator = new Translator($locales, $this->path);

		$reflectionMethod = new \ReflectionMethod($translator, 'get');
		$reflectionMethod->setAccessible(true);

		$this->setExpectedException(TranslationKeyNotFound::class);
		$reflectionMethod->invoke($translator, new Locale('de-CH'), 'foo', 'assfdsagfdsaztg54rwzhg564ezh65rte');
	}

	/**
	 * @covers ::get
	 */
	public function testGetFailedKeyNotString()
	{
		$locales = new Collection([new Locale('de-DE')]);
		$translator = new Translator($locales, $this->path);

		$reflectionMethod = new \ReflectionMethod($translator, 'get');
		$reflectionMethod->setAccessible(true);

		$this->setExpectedException(TranslationKeyIsNotAString::class);
		$reflectionMethod->invoke($translator, new Locale('de-CH'), 'foo', 'a');
	}

	/**
	 * @covers ::getAll
	 */
	public function testGetAllWithoutParsedBBCode()
	{
		$locales = new Collection([new Locale('de-DE', new Locale('en-US'))]);
		$translator = new Translator($locales, $this->path, null, $this->markupRenderer);

		// with default locale
		$translations = $translator->getAll(null, false);

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
		$translations = $translator->getAll(new Locale('en-US'), false);

		$this->assertCount(1, $translations);
		$this->assertArrayHasKey('en-US', $translations);

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
	 * @covers ::getAll
	 */
	public function testGetAllWithoutParsedBBCodeWithoutMarkupRenderer()
	{
		$locales = new Collection([new Locale('de-DE', new Locale('en-US'))]);
		$translator = new Translator($locales, $this->path);

		// with default locale
		$translations = $translator->getAll(null, true);

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
		$translations = $translator->getAll(new Locale('en-US'), true);

		$this->assertCount(1, $translations);
		$this->assertArrayHasKey('en-US', $translations);

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
	 * @covers ::getAll
	 */
	public function testGetAllWithParsedBBCode()
	{
		$locales = new Collection([new Locale('de-DE', new Locale('en-US'))]);
		$translator = new Translator($locales, $this->path, null, $this->markupRenderer);

		// with default locale
		$translations = $translator->getAll(null, true);

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
			'bb' => '<strong>bbcode</strong> bb'
		], $translations['de-DE']['other']);

		// with none default locale after default
		$translations = $translator->getAll('en-US', true);

		$this->assertCount(1, $translations);
		$this->assertArrayHasKey('en-US', $translations);

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
	 * @covers ::getAll
	 */
	public function testGetAllWithSubPath()
	{
		$locales = new Collection([new Locale('ru-RU')]);
		$translator = new Translator($locales, $this->path);

		// with default locale
		$translations = $translator->getAll();

		$this->assertCount(1, $translations);
		$this->assertArrayHasKey('ru-RU', $translations);

		$this->assertCount(1, $translations['ru-RU']);
		$this->assertArrayHasKey('test/a/nuff/module', $translations['ru-RU']);

		$this->assertEquals([
			'nuff' => 'narf'
		], $translations['ru-RU']['test/a/nuff/module']);
	}

	/**
	 * @covers ::getAll
	 */
	public function testGetAllWithNotExistingLocalePath()
	{
		$locales = new Collection([new Locale('fr-RU')]);
		$translator = new Translator($locales, $this->path);
		$translations = $translator->getAll();

		$this->assertCount(0, $translations);
	}

	/**
	 * @covers ::getAll
	 */
	public function testGetAllWithDisabledLocales()
	{
		$locales = new Collection([new Locale('ru-RU', false)]);
		$translator = new Translator($locales, $this->path);

		$this->assertCount(0, $translator->getAll());
	}

	/**
	 * @covers ::getLocales
	 * @covers ::setLocales
	 */
	public function testGetSetLocales()
	{
		$locales = new Collection([new Locale('de-DE'), new Locale('de-CH')]);
		$localesOther = new Collection([new Locale('de-DE'), new Locale('de-CH')]);
		$translator = new Translator($locales, $this->path);

		$this->assertSame($locales, $translator->getLocales());
		$this->assertSame($translator, $translator->setLocales($localesOther));
		$this->assertSame($localesOther, $translator->getLocales());
	}

	/**
	 * @covers ::getLogger
	 * @covers ::setLogger
	 */
	public function testGetSetLogger()
	{
		$logger1 = (new Logger())->addWriter($this->logWriter);
		$logger2 = (new Logger())->addWriter($this->logWriter);

		$locales = new Collection([new Locale('de-DE')]);
		$translator = new Translator($locales, $this->path);

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

		$locales = new Collection([new Locale('de-DE')]);
		$translator = new Translator($locales, $this->path);

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
		$locales = new Collection([new Locale('de-DE')]);
		$translator = new Translator($locales, $this->path);

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
		$locales = new Collection([new Locale('de-DE')]);
		$translator = new Translator($locales, $this->path);

		$this->setExpectedException(PathCanNotBeNull::class);
		$translator->setPath(null);
	}

	/**
	 * @covers ::getPlaceholderPrefix
	 * @covers ::setPlaceholderPrefix
	 */
	public function testGetSetPlaceholderPrefix()
	{
		$locales = new Collection([new Locale('de-DE')]);
		$translator = new Translator($locales, $this->path);

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
		$locales = new Collection([new Locale('de-DE')]);
		$translator = new Translator($locales, $this->path);

		$this->assertSame(']', $translator->getPlaceholderSuffix());
		$this->assertSame($translator, $translator->setPlaceholderSuffix('}'));
		$this->assertSame('}', $translator->getPlaceholderSuffix());
	}

	/**
	 * @covers ::isFileLoaded
	 */
	public function testIsFileLoaded()
	{
		$locales = new Collection([new Locale('de-DE')]);
		$translator = new Translator($locales, $this->path);

		$reflectionMethod = new \ReflectionMethod($translator, 'isFileLoaded');
		$reflectionMethod->setAccessible(true);

		$this->assertFalse($reflectionMethod->invoke($translator, new Locale('de-DE'), 'nuff'));

		$translator->__('test.a');
		$this->assertFalse($reflectionMethod->invoke($translator, new Locale('de-DE'), 'nuff'));

		$this->assertTrue($reflectionMethod->invoke($translator, new Locale('de-DE'), 'test'));
	}

	/**
	 * @covers ::load
	 */
	public function testLoad()
	{
		$locales = new Collection([new Locale('de-DE')]);
		$translator = new Translator($locales, $this->path, $this->logger);

		$reflectionMethod = new \ReflectionMethod($translator, 'load');
		$reflectionMethod->setAccessible(true);

		$reflectionProperty = new \ReflectionProperty($translator, 'translations');
		$reflectionProperty->setAccessible(true);

		$this->assertTrue($reflectionMethod->invoke($translator, new Locale('de-DE'), 'test'));
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

		$this->assertTrue($reflectionMethod->invoke($translator, new Locale('de-DE'), 'test'));
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
		$locales = new Collection([new Locale('de-DE')]);
		$translator = new Translator($locales, $this->path, $this->logger);

		$reflectionMethod = new \ReflectionMethod($translator, 'load');
		$reflectionMethod->setAccessible(true);

		$this->setExpectedException(FileNotFound::class);
		$reflectionMethod->invoke($translator, new Locale('zh-CH'), 'test');

		$this->assertCount(0, $this->logWriter->events);
	}

	/**
	 * @covers ::load
	 */
	public function testLoadFailedInvalidFile()
	{
		$locales = new Collection([new Locale('de-DE')]);
		$translator = new Translator($locales, $this->path, $this->logger);

		$reflectionMethod = new \ReflectionMethod($translator, 'load');
		$reflectionMethod->setAccessible(true);

		$this->setExpectedException(InvalidTranslationFile::class);
		$reflectionMethod->invoke($translator, new Locale('de-CH'), 'test');

		$this->assertCount(0, $this->logWriter->events);
	}

	/**
	 * @covers ::log
	 */
	public function testLog()
	{
		$locales = new Collection([new Locale('de-DE')]);
		$translator = new Translator($locales, $this->path, $this->logger);

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
		$locales = new Collection([new Locale('de-DE')]);
		$translator = new Translator($locales, $this->path);
		$translator->setPlaceholderPrefix($prefix)->setPlaceholderSuffix($suffix);

		$reflectionMethod = new \ReflectionMethod($translator, 'parseParameters');
		$reflectionMethod->setAccessible(true);

		$this->assertSame($exptected, $reflectionMethod->invoke($translator, $text, $parameters));
	}

	/**
	 *  @covers ::getAllLocales
	 */
	public function testGetAllLocales()
	{
		$locales = new Collection([new Locale('de-DE')]);
		$translator = new Translator($locales, $this->path);

		$this->assertEquals([
			'de-CH',
			'de-DE',
			'en-US',
			'fr-CH',
			'fr-FR',
			'it-IT',
			'ru-RU'
		], $translator->getAllLocales());
	}

	/**
	 * @covers ::getAllLocales
	 */
	public function testGetAllLocalesWithNotExistingTranslationPath()
	{
		$locales = new Collection([new Locale('fr-RU')]);
		$translator = new Translator($locales, $this->path . '/vfghjdksljgnfjda');
		$translations = $translator->getAllLocales();

		$this->assertCount(0, $translations);
	}

	/**
	 * @covers ::getTemplateMissingKey
	 * @covers ::setTemplateMissingKey
	 */
	public function testGetSetTemplateMissingKey()
	{
		$locales = new Collection([new Locale('fr-RU')]);
		$translator = new Translator($locales, $this->path, $this->logger);

		$this->assertSame('[b][color=#F00]%%[KEY]%% ([LOCALE])[/color][/b]', $translator->getTemplateMissingKey());
		$this->assertSame($translator, $translator->setTemplateMissingKey('nuff'));
		$this->assertSame('nuff', $translator->getTemplateMissingKey());
	}

	/**
	 * @covers ::__
	 */
	public function test__WithLog()
	{
		$locales = new Collection([new Locale('fr-RU')]);
		$translator = new Translator($locales, $this->path, $this->logger);

		$translator->__('abc/def.geh', ['a' => 1, 'key' => 'narf']);
		$this->assertCount(1, $this->logWriter->events);
		$this->assertArrayHasKey('priority', $this->logWriter->events[0]);
		$this->assertEquals(Logger::ERR, $this->logWriter->events[0]['priority']);
		$this->assertArrayHasKey('extra', $this->logWriter->events[0]);
		$this->assertArrayHasKey('trace', $this->logWriter->events[0]['extra']);
		$this->assertArrayHasKey('key', $this->logWriter->events[0]['extra']);
		$this->assertEquals('abc/def.geh', $this->logWriter->events[0]['extra']['key']);
		$this->assertArrayHasKey('locale', $this->logWriter->events[0]['extra']);
		$this->assertEquals('fr-RU', $this->logWriter->events[0]['extra']['locale']);
		$this->assertArrayHasKey('parameters', $this->logWriter->events[0]['extra']);
		$this->assertEquals(['a' => 1, 'key' => 'narf'], $this->logWriter->events[0]['extra']['parameters']);
	}

	/**
	 * @covers ::__
	 */
	public function test__WithTemplateMissingKeyModifingParameters()
	{
		$locales = new Collection([new Locale('fr-RU')]);

		$translator = $this->getMockBuilder(Translator::class)->setMethods(['parseParameters'])->setConstructorArgs([$locales, $this->path])->getMock();
		$translator->expects($this->once())->method('parseParameters')->with('[b][color=#F00]%%[KEY]%% ([LOCALE])[/color][/b]', $this->equalTo(
		[
			'a' => 1,
			'locale' => 'fr-RU',
			'key' => 'abc/def.geh',
			'file' => 'abc/def',
			'translationKey' => 'geh',
		]));
		$translator->__('abc/def.geh', ['a' => 1, 'key' => 'narf']);
	}

	/**
	 * @covers ::__
	 */
	public function test__WithTemplateMissingKey()
	{
		$locales = new Collection([new Locale('fr-RU')]);
		$translator = new Translator($locales, $this->path);

		$this->assertSame('[b][color=#F00]%%abc/def.geh%% (fr-RU)[/color][/b]', $translator->__('abc/def.geh', ['a' => 1, 'key' => 'narf', 'c' => 'every']));

		$translator->setTemplateMissingKey('[KEY]');
		$this->assertSame('abc/def.geh', $translator->__('abc/def.geh', ['a' => 1, 'key' => 'narf', 'c' => 'every']));

		$translator->setTemplateMissingKey('[KEY]  ([LOCALE]/[C])');
		$this->assertSame('abc/def.geh  (fr-RU/every)', $translator->__('abc/def.geh', ['a' => 1, 'key' => 'narf', 'c' => 'every']));
	}

	public function dataProviderParseKey()
	{
		return [

			['de-DE', 'test.a', 'test', 'a', false],
			['de-DE', 'test.key', 'test', 'key', false],
			['de-DE', 'other.a', 'other', 'a', false],
			['de-DE', 'other.key', 'other', 'key', false],
			['de-DE', 'other.a.b.c', 'other', 'a.b.c', false],

			['de-DE', 'testparam1', null, null, true],
			['de-DE', 'testparam1', null, null, true],

			['ru-RU', 'test/a/nuff/module.nuff', 'test/a/nuff/module', 'nuff', true],

			['de-DE', 'file/test_0/text_0.1.narf', null, null, true],
			['de-DE', 'file/test.1/text.1.1.lol', null, null, true],

			['it-IT', 'file/test_0/text_0.1.narf', 'file/test_0/text_0.1', 'narf', false],
			['it-IT', 'file/test.1/text.1.1.lol', 'file/test.1/text.1.1', 'lol', false],
		];
	}

	/**
	 *
	 * @param string $locale
	 * @param string $key
	 * @param string $expectedFile
	 * @param string $expectedKey
	 * @covers ::parseKey
	 * @dataProvider dataProviderParseKey
	 */
	public function testParseKey($locale, $key, $expectedFile, $expectedKey, $exception)
	{
		$translator = new Translator(new Collection([new Locale('de-DE')]), $this->path);

		$reflectionMethod = new \ReflectionMethod($translator, 'parseKey');
		$reflectionMethod->setAccessible(true);

		if ($exception === false)
		{
			$this->assertEquals([$expectedFile, $expectedKey], $reflectionMethod->invoke($translator, $key, new Locale($locale)));
		}
		else
		{
			$this->setExpectedException(InvalidTranslationKey::class);
			$reflectionMethod->invoke($translator, $key, new Locale('de-DE'));
		}
	}
}