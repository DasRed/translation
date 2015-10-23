<?php
namespace DasRed\Translation;

use DasRed\Parser\BBCode;
use DasRed\Translation\Exception\FileNotFound;
use DasRed\Translation\Exception\InvalidTranslationFile;
use DasRed\Translation\Exception\InvalidTranslationKey;
use DasRed\Translation\Exception\PathCanNotBeNull;
use DasRed\Translation\Exception\TranslationKeyNotFound;
use DasRed\Translation\Exception\TranslationKeyIsNotAString;
use Zend\Log\Logger;
use DasRed\Translation\Locale\Collection;

/**
 * Translator Class
 */
class Translator
{

	/**
	 * defines the locales to use. first locale with result will return content
	 *
	 * @var Collection|Locales[]
	 */
	protected $locales;

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
	 * @param Collection|Locale[] $locales
	 * @param string $path
	 * @param Logger $logger
	 * @param BBCode $markupRenderer
	 */
	public function __construct(Collection $locales, $path, Logger $logger = null, BBCode $markupRenderer = null)
	{
		$this->setLocales($locales)->setPath($path)->setLogger($logger)->setMarkupRenderer($markupRenderer);
	}

	/**
	 * translation function
	 *
	 * @param string $key this is the translation key WITH the translation file. Syntax "FILE.KEY". e.g.: header.pageTitle
	 * @param string[] $parameters list of key value list to replace in the content of translated string. in Translation is the syntax
	 *        "[KEY]". key is case insensitive
	 * @param Locale $locale if not defined, then $this->getUserLocale())
	 * @param bool $parseBBCode
	 * @return string
	 */
	public function __($key, array $parameters = [], Locale $locale = null, $parseBBCode = true)
	{
		$parametersToUse = $parameters;

		$locales = [];
		if ($locale !== null)
		{
			$locales[] = $locale;
		}
		$locales = array_merge($locales, $this->getLocales()->getEnabled()->getArrayCopy());

		$translationFile = null;
		$translationKey = null;

		$translation = null;

		/** @var $localeToUse Locale */
		foreach ($locales as $localeToUse)
		{
			// get
			try
			{
				list($translationFile, $translationKey) = $this->parseKey($key, $localeToUse);
				$translation = $this->get($localeToUse, $translationFile, $translationKey);
				return $this->handleTranslation($translation, $parametersToUse, $parseBBCode);
			}
			// fallback
			catch (Exception $exception)
			{
				if ($exception instanceof InvalidTranslationKey)
				{
					if ($translationFile === null)
					{
						$translationFile = $exception->getTranslationFile();
					}
					if ($translationKey === null)
					{
						$translationKey = $exception->getTranslationKey();
					}
				}

				$this->log($exception->getMessage(), Logger::ERR, [
					'trace' => $exception->getTrace(),
					'locale' => $locale->getName(),
					'localeToUse' => $localeToUse->getName(),
					'key' => $key,
					'parameters' => $parameters
				]);
			}
		}

		// failed.. can not find anything
		$translation = $this->getTemplateMissingKey();

		// show error text
		$parametersToUse['locale'] = $locale->getName();
		$parametersToUse['key'] = $key;
		$parametersToUse['file'] = $translationFile;
		$parametersToUse['translationKey'] = $translationKey;

		return $this->handleTranslation($translation, $parametersToUse, $parseBBCode);
	}

	/**
	 *
	 * @param string $translation
	 * @param array $parameters
	 * @param bool $parseBBCode
	 * @return string
	 */
	protected function handleTranslation($translation, array $parameters = [], $parseBBCode = true)
	{
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
	 * @param Locale $locale
	 * @param string $file
	 * @param string $key
	 * @return string
	 * @throws TranslationKeyNotFound
	 * @throws TranslationKeyIsNotAString
	 */
	protected function get(Locale $locale, $file, $key)
	{
		$localeName = $locale->getName();

		// load it
		$this->load($locale, $file);

		if (array_key_exists($key, $this->translations[$localeName][$file]) === false)
		{
			throw new TranslationKeyNotFound($key, $this->getPath(), $localeName, $file);
		}

		$result = $this->translations[$localeName][$file][$key];

		if (is_string($result) === false)
		{
			throw new TranslationKeyIsNotAString($result, $key, $this->getPath(), $localeName, $file);
		}

		return $result;
	}

	/**
	 *
	 * @param Locale $locale
	 * @param bool $parseBBCode
	 * @return string[][]
	 */
	public function getAll(Locale $locale = null, $parseBBCode = true)
	{
		if ($locale === null)
		{
			$locale = $this->getLocales()->find(function (Locale $locale)
			{
				return $locale->isEnabled();
			});
			if ($locale === null)
			{
				return [];
			}
		}
		$localeName = $locale->getName();

		$path = str_replace('\\', '/', $this->getPath() . '/' . $localeName);
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

		$translations[$localeName] = $this->translations[$localeName];

		// parse BBCode
		if ($parseBBCode === true && $this->getMarkupRenderer() !== null)
		{
			foreach ($translations[$localeName] as $file => $keys)
			{
				foreach ($keys as $trKey => $trValue)
				{
					$translations[$localeName][$file][$trKey] = $this->getMarkupRenderer()->parse($trValue);
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
	 * returns the locales
	 *
	 * @return Collection|Locales[]
	 */
	public function getLocales()
	{
		return $this->locales;
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
	 * @param Locale $locale
	 * @param string $file
	 * @return boolean
	 */
	protected function isFileLoaded(Locale $locale, $file)
	{
		if (array_key_exists($locale->getName(), $this->translations) === false)
		{
			return false;
		}

		if (array_key_exists($file, $this->translations[$locale->getName()]) === false)
		{
			return false;
		}

		return true;
	}

	/**
	 * loads translations from file
	 *
	 * @param Locale $locale
	 * @param string $fileName
	 * @throws FileNotFound
	 * @throws InvalidTranslationFile
	 * @return boolean
	 */
	protected function load(Locale $locale, $fileName)
	{
		// check for already loading
		if ($this->isFileLoaded($locale, $fileName) === true)
		{
			return true;
		}

		$startTime = microtime(true);

		$file = $this->getPath() . '/' . $locale->getName() . '/' . $fileName . '.php';
		if (file_exists($file) === false)
		{
			throw new FileNotFound($this->getPath(), $locale->getName(), $fileName);
		}

		$translationKeys = include $file;

		// not found
		if ($translationKeys === null || is_array($translationKeys) === false)
		{
			throw new InvalidTranslationFile($this->getPath(), $locale->getName(), $fileName);
		}

		// create array index locale
		if (array_key_exists($locale->getName(), $this->translations) === false)
		{
			$this->translations[$locale->getName()] = [];
		}

		// create array index file with the translations keys
		$this->translations[$locale->getName()][$fileName] = $translationKeys;

		// log da shit
		$this->log('Language loaded: ' . $locale->getName() . '/' . $fileName . ' (' . number_format(microtime(true) - $startTime, 2, ',', '.') . ')');

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
	 * @param Locale $locale
	 * @throws InvalidTranslationKey
	 * @return string[0 => FILE, 1 => TRANSLATION KEY]
	 */
	protected function parseKey($key, Locale $locale)
	{
		$pathesToTest = array_unique(array_map(function (Locale $locale)
		{
			$this->getPath() . '/' . $locale->getName() . '/';
		}, array_merge([
			$locale
		], $this->getLocales()->getEnabled()->getArrayCopy())));

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
	 * set the locales
	 *
	 * @param Collection|Locales[] $locales
	 * @return self
	 */
	public function setLocales(Collection $locales)
	{
		$this->locales = $locales;

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
