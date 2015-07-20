<?php
namespace DasRed\Translation;

use DasRed\Parser\BBCode;
use DasRed\Translation\Exception\DefaultLocaleCanNotBeNull;
use DasRed\Translation\Exception\FileNotFound;
use DasRed\Translation\Exception\InvalidTranslationFile;
use DasRed\Translation\Exception\InvalidTranslationKey;
use DasRed\Translation\Exception\PathCanNotBeNull;
use DasRed\Translation\Exception\TranslationKeyNotFound;
use DasRed\Translation\Exception\TranslationKeyIsNotAString;
use Zend\Log\Logger;

/**
 * Translator Class
 */
class Translator
{

	/**
	 * defines the default locale
	 *
	 * @var string
	 */
	protected $defaultLocale;

	/**
	 *
	 * @var Logger
	 */
	protected $logger;

	/**
	 *
	 * @var BBCode
	 */
	protected $markupRenderer;

	/**
	 * path to the translations
	 *
	 * @var string
	 */
	protected $path;

	/**
	 *
	 * @var string
	 */
	protected $placeholderPrefix = '[';

	/**
	 *
	 * @var string
	 */
	protected $placeholderSuffix = ']';

	/**
	 * holds for every locale the language file and the translation key
	 *
	 * @var array
	 */
	protected $translations = [];

	/**
	 *
	 * @param string $defaultLocale
	 * @param string $path
	 * @param Logger $logger
	 * @param BBCode $markupRenderer
	 */
	public function __construct($defaultLocale, $path, Logger $logger = null, BBCode $markupRenderer = null)
	{
		$this->setDefaultLocale($defaultLocale)->setPath($path)->setLogger($logger)->setMarkupRenderer($markupRenderer);
	}

	/**
	 * translation function
	 *
	 * @param string $key this is the translation key WITH the translation file. Syntax "FILE.KEY". e.g.: header.pageTitle
	 * @param array $parameters list of key value list to replace in the content of translated string. in Translation is the syntax
	 *        "[KEY]". key is case insensitive
	 * @param string $locale if not defined, then $this->getUserLocale())
	 * @param string $default
	 * @param bool $parseBBCode
	 * @return string
	 */
	public function __($key, array $parameters = [], $locale = null, $default = null, $parseBBCode = true)
	{
		if ($locale === null)
		{
			$locale = $this->getDefaultLocale();
		}

		// get
		try
		{
			$keyData = explode('.', $key);
			if (count($keyData) <= 1)
			{
				throw new InvalidTranslationKey($key, $locale);
			}
			$file = array_shift($keyData);
			$translationKey = implode('.', $keyData);

			$translation = $this->get($locale, $file, $translationKey);
		}
		// fallback
		catch (Exception $exception)
		{
			$translation = $default;
			if ($default === null)
			{
				// go fallback
				if ($locale !== $this->getDefaultLocale())
				{
					return $this->__($key, $parameters, $this->getDefaultLocale(), $default, $parseBBCode);
				}

				// show error text
				$translation = '[b][color=#F00]%%' . $key . '%% (' . $locale . ')[/color][/b]';
			}
			$this->log($exception->getMessage(), Logger::ERR);
		}

		// parse parameters
		$translation = $this->parseParameters($translation, $parameters);

		// parse BB Code
		if ($parseBBCode === true && empty($translation) === false && $this->getMarkupRenderer() !== null)
		{
			$translation = $this->getMarkupRenderer()->parse($translation);
		}

		return $translation;
	}

	/**
	 * retrieves a translation key from file for a locale
	 *
	 * @param string $locale
	 * @param string $file
	 * @param string $key
	 * @return string
	 * @throws TranslationKeyNotFound
	 * @throws TranslationKeyIsNotAString
	 */
	protected function get($locale, $file, $key)
	{
		// load it
		$this->load($locale, $file);

		if (array_key_exists($key, $this->translations[$locale][$file]) === false)
		{
			throw new TranslationKeyNotFound($key, $this->getPath(), $locale, $file);
		}

		$result = $this->translations[$locale][$file][$key];

		if (is_string($result) === false)
		{
			throw new TranslationKeyIsNotAString($result, $key, $this->getPath(), $locale, $file);
		}

		return $result;
	}

	/**
	 *
	 * @param string $locale
	 * @return array
	 */
	public function getAll($locale = null)
	{
		if ($locale === null)
		{
			$locale = $this->getDefaultLocale();
		}

		$files = glob($this->getPath() . '/' . $locale . '/*.php');
		foreach ($files as $file)
		{
			$this->load($locale, basename($file, '.php'));
		}

		return $this->translations;
	}

	/**
	 * returns the default locale
	 *
	 * @return string
	 */
	public function getDefaultLocale()
	{
		return $this->defaultLocale;
	}

	/**
	 *
	 * @return BBCode
	 */
	public function getMarkupRenderer()
	{
		return $this->markupRenderer;
	}

	/**
	 * returns the logger
	 *
	 * @return Logger
	 */
	public function getLogger()
	{
		return $this->logger;
	}

	/**
	 * returns the path to the translations
	 *
	 * @return string
	 */
	public function getPath()
	{
		return $this->path;
	}

	/**
	 *
	 * @return the $placeholderPrefix
	 */
	public function getPlaceholderPrefix()
	{
		return $this->placeholderPrefix;
	}

	/**
	 *
	 * @return the $placeholderSuffix
	 */
	public function getPlaceholderSuffix()
	{
		return $this->placeholderSuffix;
	}

	/**
	 * checks if a file for a given locale loaded
	 *
	 * @param string $locale
	 * @param string $file
	 * @return boolean
	 */
	protected function isFileLoaded($locale, $file)
	{
		if (array_key_exists($locale, $this->translations) === false)
		{
			return false;
		}

		if (array_key_exists($file, $this->translations[$locale]) === false)
		{
			return false;
		}

		return true;
	}

	/**
	 * loads translations from file
	 *
	 * @param string $locale
	 * @param string $fileName
	 * @throws FileNotFound
	 * @throws InvalidTranslationFile
	 * @return boolean
	 */
	protected function load($locale, $fileName)
	{
		// check for already loading
		if ($this->isFileLoaded($locale, $fileName) === true)
		{
			return true;
		}

		$startTime = microtime(true);

		$file = $this->getPath() . '/' . $locale . '/' . $fileName . '.php';
		if (file_exists($file) === false)
		{
			throw new FileNotFound($this->getPath(), $locale, $fileName);
		}

		$translationKeys = include $file;

		// not found
		if ($translationKeys === null || is_array($translationKeys) === false)
		{
			throw new InvalidTranslationFile($this->getPath(), $locale, $fileName);
		}

		// create array index locale
		if (array_key_exists($locale, $this->translations) === false)
		{
			$this->translations[$locale] = [];
		}

		// create array index file with the translations keys
		$this->translations[$locale][$fileName] = $translationKeys;

		// log da shit
		$this->log('Language loaded: ' . $locale . '/' . $fileName . ' (' . number_format(microtime(true) - $startTime, 2, ',', '.') . ')');

		return true;
	}

	/**
	 * logs a message
	 *
	 * @param string $message
	 * @param string $priority
	 * @return self
	 */
	protected function log($message, $priority = Logger::DEBUG)
	{
		if ($this->getLogger() === null)
		{
			return $this;
		}

		$this->getLogger()->log($priority, $message);

		return $this;
	}

	/**
	 * parse the given parameters in the $text if there is a placeholder for the parameters
	 *
	 * @param string $text
	 * @param array $parameters
	 * @return string
	 */
	protected function parseParameters($text, array $parameters = [])
	{
		// no params no replacement
		if ($parameters === null || count($parameters) == 0)
		{
			return $text;
		}

		$parameterNames = [];
		$parameterValues = [];
		foreach ($parameters as $parameterName => $parameterValue)
		{
			$parameterNames[] = $this->getPlaceholderPrefix() . $parameterName . $this->getPlaceholderSuffix();
			$parameterValues[] = $parameterValue;
		}

		return str_ireplace($parameterNames, $parameterValues, $text);
	}

	/**
	 * set the default locale
	 *
	 * @param string $defaultLocale
	 * @return self
	 * @throws DefaultLocaleCanNotBeNull
	 */
	public function setDefaultLocale($defaultLocale)
	{
		if ($defaultLocale === null)
		{
			throw new DefaultLocaleCanNotBeNull();
		}

		$this->defaultLocale = $defaultLocale;

		return $this;
	}

	/**
	 * set the logger
	 *
	 * @param Logger $logger
	 * @return self
	 */
	public function setLogger(Logger $logger= null)
	{
		$this->logger = $logger;

		return $this;
	}

	/**
	 *
	 * @param Code $markupRenderer
	 * @return self
	 */
	public function setMarkupRenderer(BBCode $markupRenderer = null)
	{
		$this->markupRenderer = $markupRenderer;

		return $this;
	}

	/**
	 * set the path to the translations
	 *
	 * @param string $path
	 * @return self
	 * @throws PathCanNotBeNull
	 */
	public function setPath($path)
	{
		if ($path === null)
		{
			throw new PathCanNotBeNull();
		}
		$this->path = rtrim($path, '\\/') . '/';

		return $this;
	}

	/**
	 *
	 * @param string $placeholderPrefix
	 * @return self
	 */
	public function setPlaceholderPrefix($placeholderPrefix)
	{
		$this->placeholderPrefix = $placeholderPrefix;

		return $this;
	}

	/**
	 *
	 * @param string $placeholderSuffix
	 * @return self
	 */
	public function setPlaceholderSuffix($placeholderSuffix)
	{
		$this->placeholderSuffix = $placeholderSuffix;

		return $this;
	}
}
