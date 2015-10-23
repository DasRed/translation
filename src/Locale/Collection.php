<?php
namespace DasRed\Translation\Locale;

use DasRed\Translation\Locale;

class Collection extends \ArrayObject
{

	/**
	 *
	 * @param array $array
	 */
	public function __construct($array = [])
	{
		parent::__construct([]);

		/** @var $entry Locale */
		foreach ($array as $entry)
		{
			$this->append($entry);
		}
	}

	/**
	 * cloning
	 */
	public function __clone()
	{
		$entries = $this->getArrayCopy();
		$this->exchangeArray([]);

		/** @var $entry Locale */
		foreach ($entries as $entry)
		{
			$this->append(clone $entry);
		}
	}

	/**
	 * (non-PHPdoc)
	 *
	 * @see ArrayObject::append()
	 * @param Locale $value
	 */
	public function append($value)
	{
		$this->validate($value)->offsetSet($value->getName(), $value);

		return $this;
	}

	/**
	 * Applies the given predicate p to all elements of this collection,
	 * returning false to abort iteration in the callback
	 *
	 * @param Closure $p The predicate.
	 * @return $this
	 */
	public function each(\Closure $p)
	{
		$index = 0;
		foreach ($this as $key => $element)
		{
			if ($p($element, $key, $index) === false)
			{
				return $this;
			}
			$index++;
		}

		return $this;
	}

	/**
	 *
	 * @param \Closure $p
	 * @return static
	 */
	public function filter(\Closure $p)
	{
		$collection = new static();

		$this->each(function ($element, $key, $index) use($collection, $p)
		{
			if ($p($element, $key, $index) === true)
			{
				$collection->offsetSet($key, $element);
			}
		});

		return $collection;
	}

	/**
	 *
	 * @param \Closure $p
	 * @return mixed
	 */
	public function find(\Closure $p)
	{
		$index = 0;
		foreach ($this as $key => $element)
		{
			if ($p($element, $key, $index) === true)
			{
				return $element;
			}
			$index++;
		}

		return null;
	}

	/**
	 *
	 * @return static
	 */
	public function getEnabled()
	{
		return $this->filter(function (Locale $locale)
		{
			return $locale->isEnabled();
		});
	}

	/**
	 *
	 * @param \Closure $p
	 * @return mixed[]
	 */
	public function map(\Closure $p)
	{
		$result = [];
		$index = 0;
		foreach ($this as $key => $element)
		{
			$result[$key] = $p($element, $key, $index);
			$index++;
		}

		return $result;
	}

	/**
	 *
	 * {@inheritDoc}
	 * @see ArrayObject::offsetExists()
	 */
	public function offsetExists($index)
	{
		return parent::offsetExists(str_replace('_', '-', $index));
	}

	/**
	 *
	 * {@inheritDoc}
	 * @see ArrayObject::offsetGet()
	 */
	public function offsetGet($index)
	{
		return parent::offsetGet(str_replace('_', '-', $index));
	}

	/**
	 * (non-PHPdoc)
	 *
	 * @see ArrayObject::offsetSet()
	 * @param Locale $value
	 */
	public function offsetSet($index, $value)
	{
		$this->validate($value);

		parent::offsetSet($value->getName(), $value);
	}

	/**
	 *
	 * {@inheritDoc}
	 * @see ArrayObject::offsetUnset()
	 */
	public function offsetUnset($index)
	{
		return parent::offsetUnset(str_replace('_', '-', $index));
	}

	/**
	 *
	 * @param Locale $value
	 * @throws \InvalidArgumentException
	 * @return $this
	 */
	protected function validate($value)
	{
		if (($value instanceof Locale) === false)
		{
			throw new \InvalidArgumentException('$value must be an instance of ' . Locale::class . '!');
		}

		return $this;
	}
}