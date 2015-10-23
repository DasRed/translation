<?php
namespace DasRedTest\Translation\Locale;

use DasRed\Translation\Locale\Collection;
use DasRed\Translation\Locale;
/**
 * @coversDefaultClass \DasRed\Translation\Locale\Collection
 */
class CollectionTest extends \PHPUnit_Framework_TestCase
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

	/**
	 * @covers ::each
	 */
	public function testEachFull()
	{
		$locales = [
			new Locale('de-DE'),
			new Locale('en-GB'),
			new Locale('en-US'),
		];
		$collection = new Collection($locales);

		$this->assertSame($collection, $collection->each(function($element, $key, $index) use ($locales)
		{
			static $count = 0;

			$this->assertSame($count, $index);
			$count++;

			$this->assertGreaterThanOrEqual(0, $index);
			$this->assertLessThanOrEqual(2, $index);
			$this->assertSame($locales[$index], $element);

			switch ($index)
			{
				case 0:
					return 'nuff';

				case 1:
					return 0;

				case 2:
					return true;

				default:
					$this->fail();
			}
		}));
	}

	/**
	 * @covers ::each
	 */
	public function testEachAbort()
	{
		$locales = [
			new Locale('de-DE'),
			new Locale('en-GB'),
			new Locale('en-US'),
		];
		$collection = new Collection($locales);

		$this->assertSame($collection, $collection->each(function($element, $key, $index) use ($locales)
		{
			switch ($index)
			{
				case 0:
					break;

				case 1:
					return false;

				default:
					$this->fail();
			}
		}));
	}

	/**
	 * @covers ::filter
	 */
	public function testFilter()
	{
		$locales = [
			new Locale('de-DE'),
			new Locale('en-GB'),
			new Locale('en-US'),
		];
		$collection = new Collection($locales);

		$result = $collection->filter(function($element, $key, $index) use ($locales)
		{
			static $count = 0;

			$this->assertSame($count, $index);
			$count++;

			$this->assertGreaterThanOrEqual(0, $index);
			$this->assertLessThanOrEqual(2, $index);
			$this->assertSame($locales[$index], $element);

			switch ($index)
			{
				case 0:
					return true;

				case 1:
					return 0;

				case 2:
					return true;

				default:
					$this->fail();
			}
		});

		$this->assertInstanceOf(Collection::class, $result);
		$this->assertNotSame($collection, $result);
		$this->assertSame(2, $result->count());

		$this->assertSame(true, $result->offsetExists('de-DE'));
		$this->assertSame($locales[0], $result->offsetGet('de-DE'));

		$this->assertSame(true, $result->offsetExists('en-US'));
		$this->assertSame($locales[2], $result->offsetGet('en-US'));
	}

	/**
	 * @covers ::find
	 */
	public function testFindSuccess()
	{
		$locales = [
			new Locale('de-DE'),
			new Locale('en-GB'),
			new Locale('en-US'),
		];
		$collection = new Collection($locales);

		$result = $collection->find(function($element, $key, $index) use ($locales)
		{
			static $count = 0;

			$this->assertSame($count, $index);
			$count++;

			$this->assertGreaterThanOrEqual(0, $index);
			$this->assertLessThanOrEqual(2, $index);
			$this->assertSame($locales[$index], $element);

			switch ($index)
			{
				case 0:
					return 'nuff';

				case 1:
					return true;

				case 2:
					$this->fail();

				default:
					$this->fail();
			}
		});

		$this->assertSame($locales[1], $result);
	}

	/**
	 * @covers ::find
	 */
	public function testFindFailed()
	{
		$locales = [
			new Locale('de-DE'),
			new Locale('en-GB'),
			new Locale('en-US'),
		];
		$collection = new Collection($locales);

		$result = $collection->find(function($element, $key, $index) use ($locales)
		{
			static $count = 0;

			$this->assertSame($count, $index);
			$count++;

			$this->assertGreaterThanOrEqual(0, $index);
			$this->assertLessThanOrEqual(2, $index);
			$this->assertSame($locales[$index], $element);

			switch ($index)
			{
				case 0:
					return 'nuff';

				case 1:
					return false;

				case 2:
					return false;

				default:
					$this->fail();
			}
		});

		$this->assertNull($result);
	}

	/**
	 * @covers ::map
	 */
	public function testMap()
	{
		$locales = [
			new Locale('de-DE'),
			new Locale('en-GB'),
			new Locale('en-US'),
		];
		$collection = new Collection($locales);

		$this->assertEquals([
			'de-DE' => 11,
			'en-GB' => 12,
			'en-US' => 13
		], $collection->map(function($element, $key, $index) use ($locales)
		{
			static $count = 0;

			$this->assertSame($count, $index);
			$count++;

			$this->assertGreaterThanOrEqual(0, $index);
			$this->assertLessThanOrEqual(2, $index);
			$this->assertSame($locales[$index], $element);

			switch ($index)
			{
				case 0:
					return 11;

				case 1:
					return 12;

				case 2:
					return 13;

				default:
					$this->fail();
			}
		}));
	}

	/**
	 * @covers ::__construct
	 */
	public function testConstruct()
	{
		$entryA = new Locale('de-AT');
		$entryB = new Locale('de-CH');
		$entryC = new Locale('de-DE');

		$collection = new Collection([$entryA, $entryB, $entryC, $entryA]);

		$this->assertSame(3, $collection->count());

		$this->assertTrue($collection->offsetExists('de-AT'));
		$this->assertTrue($collection->offsetExists('de-CH'));
		$this->assertTrue($collection->offsetExists('de-DE'));

		$this->assertSame($entryA, $collection->offsetGet('de-AT'));
		$this->assertSame($entryB, $collection->offsetGet('de-CH'));
		$this->assertSame($entryC, $collection->offsetGet('de-DE'));
	}

	/**
	 * @covers ::__clone
	 */
	public function testClone()
	{
		$entryA = new Locale('de-AT');
		$entryB = new Locale('de-CH');
		$entryC = new Locale('de-DE');

		$collectionA = new Collection([$entryA, $entryB, $entryC, $entryA]);
		$collectionB = clone $collectionA;

		$this->assertSame(3, $collectionB->count());

		$this->assertTrue($collectionB->offsetExists('de-AT'));
		$this->assertTrue($collectionB->offsetExists('de-CH'));
		$this->assertTrue($collectionB->offsetExists('de-DE'));

		$this->assertNotSame($entryA, $collectionB->offsetGet('de-AT'));
		$this->assertNotSame($entryB, $collectionB->offsetGet('de-CH'));
		$this->assertNotSame($entryC, $collectionB->offsetGet('de-DE'));

		$this->assertInstanceOf(Locale::class, $collectionB->offsetGet('de-AT'));
		$this->assertInstanceOf(Locale::class, $collectionB->offsetGet('de-CH'));
		$this->assertInstanceOf(Locale::class, $collectionB->offsetGet('de-DE'));
	}

	/**
	 * @covers ::append
	 */
	public function testAppend()
	{
		$entry = new Locale('de-AT');

		$collection = $this->getMockBuilder(Collection::class)->setMethods(['validate', 'offsetSet'])->getMock();
		$collection->expects($this->once())->method('validate')->with($entry)->willReturnSelf();
		$collection->expects($this->once())->method('offsetSet')->with('de-AT', $entry)->willReturnSelf();

		$this->assertSame($collection, $collection->append($entry));
	}

	/**
	 * @covers ::offsetSet
	 */
	public function testOffsetSet()
	{
		$entry = new Locale('de-AT');

		$collection = $this->getMockBuilder(Collection::class)->setMethods(['validate'])->getMock();
		$collection->expects($this->once())->method('validate')->with($entry)->willReturnSelf();

		$this->assertNull($collection->offsetSet('nuff', $entry));
		$this->assertTrue($collection->offsetExists('de-AT'));
		$this->assertFalse($collection->offsetExists('nuff'));
		$this->assertSame($entry, $collection->offsetGet('de-AT'));
	}

	/**
	 * @covers ::validate
	 */
	public function testValidateSuccess()
	{
		$entry = new Locale('de-AT');

		$collection = new Collection();

		$this->assertSame($collection, $this->invokeMethod($collection, 'validate', [$entry]));
	}

	/**
	 * @covers ::validate
	 */
	public function testValidateFailed()
	{
		$entry = new \stdClass();

		$collection = new Collection();

		$this->setExpectedException(\InvalidArgumentException::class, '$value must be an instance of ' . Locale::class . '!');
		$this->invokeMethod($collection, 'validate', [$entry]);
	}

	/**
	 * @covers ::getEnabled
	 */
	public function testGetEnabled()
	{
		$localeDE = new Locale('de-DE', false);
		$localeGB = new Locale('en-GB', true);
		$localeAT = new Locale('de-AT');
		$localeUS = new Locale('en-US');

		$collection = new Collection([$localeDE, $localeGB, $localeAT, $localeUS]);

		$result = $collection->getEnabled();

		$this->assertInstanceOf(Collection::class, $result);
		$this->assertSame(3, $result->count());

		$this->assertTrue($result->offsetExists('en-GB'));
		$this->assertSame($localeGB, $result->offsetGet('en-GB'));

		$this->assertTrue($result->offsetExists('de-AT'));
		$this->assertSame($localeAT, $result->offsetGet('de-AT'));

		$this->assertTrue($result->offsetExists('en-US'));
		$this->assertSame($localeUS, $result->offsetGet('en-US'));
	}

	/**
	 * @covers ::offsetExists
	 */
	public function testOffsetExists()
	{
		$localeDE = new Locale('de-DE');
		$localeGB = new Locale('en-GB');
		$localeAT = new Locale('de-AT');
		$localeUS = new Locale('en-US');

		$collection = new Collection([
			$localeDE,
			$localeGB,
			$localeAT,
			$localeUS
		]);

		$this->assertTrue($collection->offsetExists('de-DE'));
		$this->assertTrue($collection->offsetExists('de_DE'));
		$this->assertTrue($collection->offsetExists('en-GB'));
		$this->assertTrue($collection->offsetExists('en_GB'));
		$this->assertTrue($collection->offsetExists('de-AT'));
		$this->assertTrue($collection->offsetExists('de_AT'));
		$this->assertTrue($collection->offsetExists('en-US'));
		$this->assertTrue($collection->offsetExists('en_US'));
	}

	/**
	 * @covers ::offsetGet
	 */
	public function testOffsetGet()
	{
		$localeDE = new Locale('de-DE');
		$localeGB = new Locale('en-GB');
		$localeAT = new Locale('de-AT');
		$localeUS = new Locale('en-US');

		$collection = new Collection([
			$localeDE,
			$localeGB,
			$localeAT,
			$localeUS
		]);

		$this->assertSame($localeDE, $collection->offsetGet('de-DE'));
		$this->assertSame($localeDE, $collection->offsetGet('de_DE'));
		$this->assertSame($localeGB, $collection->offsetGet('en-GB'));
		$this->assertSame($localeGB, $collection->offsetGet('en_GB'));
		$this->assertSame($localeAT, $collection->offsetGet('de-AT'));
		$this->assertSame($localeAT, $collection->offsetGet('de_AT'));
		$this->assertSame($localeUS, $collection->offsetGet('en-US'));
		$this->assertSame($localeUS, $collection->offsetGet('en_US'));
	}

	/**
	 * @covers ::offsetUnset
	 */
	public function testOffsetUnset()
	{
		$localeDE = new Locale('de-DE');
		$localeGB = new Locale('en-GB');
		$localeAT = new Locale('de-AT');
		$localeUS = new Locale('en-US');

		$collection = new Collection([
			$localeDE,
			$localeGB,
			$localeAT,
			$localeUS
		]);

		$collection->offsetUnset('de-DE');
		$this->assertSame(3, $collection->count());
		$this->assertFalse($collection->offsetExists('de-DE'));
		$this->assertFalse($collection->offsetExists('de_DE'));

		$collection->offsetUnset('en_GB');
		$this->assertSame(2, $collection->count());
		$this->assertFalse($collection->offsetExists('en_GB'));
		$this->assertFalse($collection->offsetExists('en_GB'));

		$collection->offsetUnset('de-AT');
		$this->assertSame(1, $collection->count());
		$this->assertFalse($collection->offsetExists('de-AT'));
		$this->assertFalse($collection->offsetExists('de-AT'));

		$collection->offsetUnset('en_US');
		$this->assertSame(0, $collection->count());
		$this->assertFalse($collection->offsetExists('en_US'));
		$this->assertFalse($collection->offsetExists('en_US'));
	}
}