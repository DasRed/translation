<?php
namespace DasRed\Translation\Logger;

use Zend\Log\Formatter\Base;
use Zend\Log\Exception;

class Formatter extends Base
{

	const DEFAULT_FORMAT = '[%timestamp%] %priorityName% (%priority%): %message% %trace% %extra% %parameters%';

	/**
	 * Format specifier for log messages
	 *
	 * @var string
	 */
	protected $format;

	/**
	 * Class constructor
	 *
	 * @see http://php.net/manual/en/function.date.php
	 * @param null|string $format Format specifier for log messages
	 * @param null|string $dateTimeFormat Format specifier for DateTime objects in event data
	 * @throws Exception\InvalidArgumentException
	 */
	public function __construct($format = null, $dateTimeFormat = null)
	{
		if ($format instanceof \Traversable)
		{
			$format = iterator_to_array($format);
		}

		if (is_array($format))
		{
			$dateTimeFormat = isset($format['dateTimeFormat']) ? $format['dateTimeFormat'] : null;
			$format = isset($format['format']) ? $format['format'] : null;
		}

		if (isset($format) && ! is_string($format))
		{
			throw new Exception\InvalidArgumentException('Format must be a string');
		}

		$this->format = isset($format) ? $format : static::DEFAULT_FORMAT;

		parent::__construct($dateTimeFormat);
	}

	/**
	 * Formats data into a single line to be written by the writer.
	 *
	 * @param array $event event data
	 * @return string formatted line to write to the log
	 */
	public function format($event)
	{
		$output = $this->format;

		if (array_key_exists('extra', $event) === true)
		{
			$this->formatExtra($event, $event['extra']);
			$event['message'] = 'Missing Key:';
		}
		else
		{
			$event['extra'] = [];
		}

		foreach ($event as $name => $value)
		{
			if (is_array($value) === true && count($value) === 0)
			{
				$value = '';
			}
			else
			{
				$value = $this->normalize($value);
			}

			$output = str_replace('%' . $name . '%', $value, $output);
		}

		return trim($output);
	}

	/**
	 *
	 * @param array $event
	 * @param array $extra
	 * @return self
	 */
	protected function formatExtra(array &$event, array &$extra)
	{
		// trace
		if (array_key_exists('trace', $extra) === true)
		{
			$event['trace'] = $this->formatTrace($extra['trace']);
			unset($extra['trace']);
		}

		// parameters
		if (array_key_exists('parameters', $extra) === true)
		{
			$event['parameters'] = $extra['parameters'];
			unset($extra['parameters']);
		}

		return $this;
	}

	/**
	 *
	 * @param array $trace
	 * @return string
	 */
	protected function formatTrace(array $trace)
	{
		$trace = array_filter($trace, function ($trace)
		{
			if (array_key_exists('file', $trace) === false || array_key_exists('line', $trace) === false)
			{
				return false;
			}

			$file = str_replace([
				'\\',
				'/'
			], '/', $trace['file']);
			if (strpos($file, 'dasred/translation/src/') !== false)
			{
				return false;
			}
			if (strpos($file, 'vendor/') !== false)
			{
				return false;
			}

			return true;
		});

		if (count($trace) == 0)
		{
			return '';
		}

		$first = array_shift($trace);

		return $first['file'] . '#' . $first['line'];
	}
}