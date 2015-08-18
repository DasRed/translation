<?php
namespace DasRedTest\Translation;

use DasRed\Translation\Version;

/**
 * @coversDefaultClass \DasRed\Translation\Version
 */
class VersionTest extends \PHPUnit_Framework_TestCase
{

	/**
	 * @covers ::get
	 */
	public function testGet()
	{
		$this->assertSame('1.0.13', (new Version())->get());
	}
}