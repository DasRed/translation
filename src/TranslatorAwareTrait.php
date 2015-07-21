<?php
namespace DasRed\Translation;

use DasRed\Translation\Exception\TranslatorIsNotDefined;

trait TranslatorAwareTrait
{

	/**
	 * @var Translator
	 */
	protected $translator;

	/**
	 *
	 * @param string $key
	 * @param array $parameters
	 * @param string $locale
	 * @param string $default
	 * @param string $parseBBCode
	 * @return string
	 * @throws TranslatorIsNotDefined
	 */
	public function __($key, array $parameters = [], $locale = null, $default = null, $parseBBCode = true)
	{
		if ($this->getTranslator() === null)
		{
			throw new TranslatorIsNotDefined();
		}

		return $this->getTranslator()->__($key, $parameters, $locale, $default, $parseBBCode);
	}

	/**
	 * @return Translator
	 */
	public function getTranslator()
	{
		return $this->translator;
	}

	/**
	 *
	 * @param Translator $translator
	 * @return self
	 */
	public function setTranslator(Translator $translator)
	{
		$this->translator = $translator;

		return $this;
	}
}