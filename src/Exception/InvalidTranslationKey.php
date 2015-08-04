<?php
namespace DasRed\Translation\Exception;

use DasRed\Translation\Exception;

class InvalidTranslationKey extends Exception
{

	/**
	 * @var string
	 */
	protected $translationFile;

	/**
	 *
	 * @var string
	 */
	protected $translationKey;


	/**
	 *
	 * @param string $key
	 * @param string $locale
	 * @param string $translationFile
	 * @param string $translationKey
	 */
	public function __construct($key, $locale, $translationFile, $translationKey)
	{
		parent::__construct('Invalid translation key "' . $key . '" for locale "' . $locale . '"! Can not expand to file name and translation entry key.');

		$this->setTranslationFile($translationFile)->setTranslationKey($translationKey);
	}


	/**
	 * @return the $translationFile
	 */
	public function getTranslationFile()
	{
		return $this->translationFile;
	}

	/**
	 * @return the $translationKey
	 */
	public function getTranslationKey()
	{
		return $this->translationKey;
	}

	/**
	 * @param string $translationFile
	 * @return self
	 */
	protected function setTranslationFile($translationFile)
	{
		$this->translationFile = $translationFile;

		return $this;
	}

	/**
	 * @param string $translationKey
	 * @return self
	 */
	protected function setTranslationKey($translationKey)
	{
		$this->translationKey = $translationKey;

		return $this;
	}

}
