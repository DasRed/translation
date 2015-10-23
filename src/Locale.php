<?php
namespace DasRed\Translation;

class Locale
{

	/**
	 *
	 * @var bool
	 */
	protected $enabled = true;

	/**
	 *
	 * @var string
	 */
	protected $name;

	/**
	 *
	 * @param string $name
	 * @param bool $enabled
	 */
	public function __construct($name, $enabled = true)
	{
		$this->setName($name)->setEnabled($enabled);
	}

	/**
	 *
	 * @return string
	 */
	public function __toString()
	{
		return $this->getName();
	}

	/**
	 *
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * Returns the language part of the locale
	 *
	 * @return string
	 */
	public function getLanguage()
	{
		$locale = explode('-', $this->getName());
		return strtolower($locale[0]);
	}

	/**
	 * Returns the region part of the locale if available
	 *
	 * @return string|null - Regionstring
	 */
	public function getRegion()
	{
		$locale = explode('-', $this->getName());
		if (isset($locale[1]) === true)
		{
			return strtoupper($locale[1]);
		}

		return null;
	}

	/**
	 *
	 * @return bool
	 */
	public function isEnabled()
	{
		return $this->enabled;
	}

	/**
	 *
	 * @param bool $enabled
	 * @return $this
	 */
	protected function setEnabled($enabled)
	{
		$this->enabled = (bool)$enabled;

		return $this;
	}

	/**
	 *
	 * @param string $name
	 * @return $this
	 */
	protected function setName($name)
	{
		$this->name = str_replace('_', '-', $name);

		return $this;
	}
}