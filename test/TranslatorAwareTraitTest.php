<?php
namespace DasRedTest\Translation;

use DasRed\Translation\Translator;
use DasRed\Translation\Exception\TranslatorIsNotDefined;

/**
 * @coversDefaultClass \DasRed\Translation\TranslatorAwareTrait
 */
class TranslatorAwareTraitTest extends \PHPUnit_Framework_TestCase
{

	/**
	 * @covers ::getTranslator
	 * @covers ::setTranslator
	 */
	public function testGetSetTranslator()
	{
		$translator = new Translator('de-DE', __DIR__ . '/translation');

		$trait = $this->getMockBuilder('\DasRed\Translation\TranslatorAwareTrait')->setMethods(null)->getMockForTrait();

		$this->assertNull($trait->getTranslator());
		$this->assertSame($trait, $trait->setTranslator($translator));
		$this->assertSame($translator, $trait->getTranslator());
	}

	/**
	 * @covers ::__
	 */
	public function test__()
	{
		$translator = $this->getMockBuilder(Translator::class)->setMethods(['__'])->setConstructorArgs(['de-DE', __DIR__ . '/translation'])->getMock();
		$translator->expects($this->once())->method('__')->with('admin.a', ['a' => 1], 'en-US', 'rofl', false)->willReturn('nuff');

		$trait = $this->getMockBuilder('\DasRed\Translation\TranslatorAwareTrait')->setMethods(null)->getMockForTrait();
		$trait->setTranslator($translator);

		$this->assertSame('nuff', $trait->__('admin.a', ['a' => 1], 'en-US', 'rofl', false));
	}

	/**
	 * @covers ::__
	 */
	public function test__Failed()
	{
		$trait = $this->getMockBuilder('\DasRed\Translation\TranslatorAwareTrait')->setMethods(null)->getMockForTrait();

		$this->setExpectedException(TranslatorIsNotDefined::class);
		$trait->__('admin.a', ['a' => 1], 'en-US', 'rofl', false);
	}

	/**
	 * @covers ::translate
	 */
	public function testTranslate()
	{
		$translator = $this->getMockBuilder(Translator::class)->setMethods(['__'])->setConstructorArgs(['de-DE', __DIR__ . '/translation'])->getMock();
		$translator->expects($this->once())->method('__')->with('admin.a', ['a' => 1], 'en-US', 'rofl', false)->willReturn('nuff');

		$trait = $this->getMockBuilder('\DasRed\Translation\TranslatorAwareTrait')->setMethods(null)->getMockForTrait();
		$trait->setTranslator($translator);

		$this->assertSame('nuff', $trait->translate('admin.a', ['a' => 1], 'en-US', 'rofl', false));
	}

	/**
	 * @covers ::translate
	 */
	public function testTranslateFailed()
	{
		$trait = $this->getMockBuilder('\DasRed\Translation\TranslatorAwareTrait')->setMethods(null)->getMockForTrait();

		$this->setExpectedException(TranslatorIsNotDefined::class);
		$trait->translate('admin.a', ['a' => 1], 'en-US', 'rofl', false);
	}
}