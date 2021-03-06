<?php
namespace DasRed\Translation;

use DasRed\Parser\BBCode;
use DasRed\Translation\Exception\LocaleCanNotBeNull;
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
	 * defines the current locale
	 *
	 * @var string
	 */
	protected $localeCurrent;

	/**
	 * defines the Default locale for TR Keys which not in current or give are found
	 *
	 * @var string
	 */
	protected $localeDefault;

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
	 *
	 * @var string
	 */
	protected $templateMissingKey = '[b][color=#F00]%%[KEY]%% ([LOCALE])[/color][/b]';

	/**
	 * holds for every locale the language file and the translation key
	 *
	 * @var string[]
	 */
	protected $translations = [];

	/**
	 *
	 * @param string $localeCurrent
	 * @param string $path
	 * @param string $localeDefault
	 * @param Logger $logger
	 * @param BBCode $markupRenderer
	 */
	public function __construct($localeCurrent, $path, $localeDefault = null, Logger $logger = null, BBCode $markupRenderer = null)
	{
		$this->setLocaleCurrent($localeCurrent)
			->setPath($path)
			->setLocaleDefault($localeDefault)
			->setLogger($logger)
			->setMarkupRenderer($markupRenderer);
	}

	/**
	 * translation function
	 *
	 * @param string $key this is the translation key WITH the translation file. Syntax "FILE.KEY". e.g.: header.pageTitle
	 * @param string[] $parameters list of key value list to replace in the content of translated string. in Translation is the syntax
	 *        "[KEY]". key is case insensitive
	 * @param string $locale if not defined, then $this->getUserLocale())
	 * @param string $default
	 * @param bool $parseBBCode
	 * @return string
	 */
	public function __($key, array $parameters = [], $locale = null, $default = null, $parseBBCode = true)
	{
		$parametersToUse = $parameters;

		if ($locale === null)
		{
			$locale = $this->getLocaleCurrent();
		}

		$translationFile = null;
		$translationKey = $key;

		// get
		try
		{
			list ($translationFile, $translationKey) = $this->parseKey($key, $locale);
			$translation = $this->get($locale, $translationFile, $translationKey);
		}
		// fallback
		catch (Exception $exception)
		{
			if ($exception instanceof InvalidTranslationKey)
			{
				$translationFile = $exception->getTranslationFile();
				$translationKey = $exception->getTranslationKey();
			}

			$translation = $default;
			if ($default === null)
			{
				// go fallback
				if ($locale !== $this->getLocaleDefault())
				{
					return $this->__($key, $parametersToUse, $this->getLocaleDefault(), $default, $parseBBCode);
				}

				// show error text
				$translation = $this->getTemplateMissingKey();
				$parametersToUse['locale'] = $locale;
				$parametersToUse['key'] = $key;
				$parametersToUse['file'] = $translationFile;
				$parametersToUse['translationKey'] = $translationKey;
			}

			$this->log($exception->getMessage(), Logger::ERR, [
				'trace' => $exception->getTrace(),
				'locale' => $locale,
				'key' => $key,
				'parameters' => $parameters
			]);
		}

		// parse parameters
		$translation = $this->parseParameters($translation, $parametersToUse);

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
	 * @param bool $parseBBCode
	 * @return string[][]
	 */
	public function getAll($locale = null, $parseBBCode = true)
	{
		if ($locale === null)
		{
			$locale = $this->getLocaleCurrent();
		}

		$path = str_replace('\\', '/', $this->getPath() . '/' . $locale);
		if (is_dir($path) === false)
		{
			return [];
		}

		$directory = new \RecursiveDirectoryIterator($path);
		$iterator = new \RecursiveIteratorIterator($directory);
		$regex = new \RegexIterator($iterator, '/^.+\.php$/i', \RecursiveRegexIterator::GET_MATCH);

		foreach ($regex as $file)
		{
			$dirName = dirname($file[0]);
			$fileName = basename($file[0], '.php');

			$dirName = trim(str_replace([
				$path,
				'\\'
			], [
				'',
				'/'
			], $dirName), '/');
			if (strlen($dirName) !== 0)
			{
				$dirName .= '/';
			}

			$this->load($locale, $dirName . $fileName);
		}

		$translations[$locale] = $this->translations[$locale];

		// parse BBCode
		if ($parseBBCode === true && $this->getMarkupRenderer() !== null)
		{
			foreach ($translations[$locale] as $file => $keys)
			{
				foreach ($keys as $trKey => $trValue)
				{
					$translations[$locale][$file][$trKey] = $this->getMarkupRenderer()->parse($trValue);
				}
			}
		}

		// just return the requested data not all
		return $translations;
	}

	/**
	 *
	 * @return string[]
	 */
	public function getAllLocales()
	{
		$locales = [];

		if (is_dir($this->getPath()) === false)
		{
			return [];
		}

		/* @var $fileinfo \DirectoryIterator */
		foreach (new \DirectoryIterator($this->getPath()) as $fileinfo)
		{
			if ($fileinfo->isDot() === true || $fileinfo->isDir() === false)
			{
				continue;
			}

			$locales[] = $fileinfo->getBasename();
		}

		natsort($locales);

		return array_values($locales);
	}

	/**
	 * returns the current locale
	 *
	 * @return string
	 */
	public function getLocaleCurrent()
	{
		return $this->localeCurrent;
	}

	/**
	 * returns the default locale
	 *
	 * @return string
	 */
	public function getLocaleDefault()
	{
		if ($this->localeDefault === null)
		{
			return $this->getLocaleCurrent();
		}

		return $this->localeDefault;
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
	 *
	 * @return string
	 */
	public function getTemplateMissingKey()
	{
		return $this->templateMissingKey;
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
	 * @param array $extra
	 * @return self
	 */
	protected function log($message, $priority = Logger::DEBUG, array $extra = [])
	{
		if ($this->getLogger() === null)
		{
			return $this;
		}

		$this->getLogger()->log($priority, $message, $extra);

		return $this;
	}

	/**
	 *
	 * @param string $key
	 * @param string $locale
	 * @throws InvalidTranslationKey
	 * @return string[0 => FILE, 1 => TRANSLATION KEY]
	 */
	protected function parseKey($key, $locale)
	{
		$pathesToTest = array_unique([
			$this->getPath() . '/' . $locale . '/',
			$this->getPath() . '/' . $this->getLocaleCurrent() . '/',
			$this->getPath() . '/' . $this->getLocaleDefault() . '/',
		]);

		$translationFile = null;
		$translationKey = $key;

		// split by dots and find existing file. test from longest possible file name to shortest file name
		$parts = explode('.', $key);
		for ($i = count($parts) - 1; $i > 0; $i--)
		{
			$translationFile = implode('.', array_slice($parts, 0, $i));
			$translationKey = implode('.', array_slice($parts, $i));
			// loop through all possible pathes
			foreach ($pathesToTest as $path)
			{
				if (file_exists($path . $translationFile . '.php') === true)
				{
					return [
						$translationFile,
						$translationKey
					];
				}
			}
		}

		throw new InvalidTranslationKey($key, $locale, $translationFile, $translationKey);
	}

	/**
	 * parse the given parameters in the $text if there is a placeholder for the parameters
	 *
	 * @param string $text
	 * @param string[] $parameters
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
	 * set the current locale
	 *
	 * @param string $localeCurrent
	 * @return self
	 * @throws LocaleCanNotBeNull
	 */
	public function setLocaleCurrent($localeCurrent)
	{
		if ($localeCurrent === null)
		{
			throw new LocaleCanNotBeNull('current');
		}

		$this->localeCurrent = $localeCurrent;

		return $this;
	}

	/**
	 * set the default locale
	 *
	 * @param string $localeDefault
	 * @return self
	 */
	public function setLocaleDefault($localeDefault)
	{
		$this->localeDefault = $localeDefault;

		return $this;
	}

	/**
	 * set the logger
	 *
	 * @param Logger $logger
	 * @return self
	 */
	public function setLogger(Logger $logger = null)
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

	/**
	 *
	 * @param string $templateMissingKey
	 * @return self
	 */
	public function setTemplateMissingKey($templateMissingKey)
	{
		$this->templateMissingKey = $templateMissingKey;

		return $this;
	}
}
