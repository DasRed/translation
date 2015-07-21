<?php
namespace DasRed\Translation;

interface TranslatorAwareInterface
{

	/**
	 *
	 * @param string $key
	 * @param array $parameters
	 * @param string $locale
	 * @param string $default
	 * @param string $parseBBCode
	 * @return string
	 */
	public function __($key, array $parameters = [], $locale = null, $default = null, $parseBBCode = true);

	/**
	 *
	 * @return Translator
	 */
	public function getTranslator();

	/**
	 *
	 * @param Translator $translator
	 * @return self
	 */
	public function setTranslator(Translator $translator);
}