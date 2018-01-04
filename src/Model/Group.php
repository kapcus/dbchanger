<?php

namespace Kapcus\DbChanger\Model;

class Group
{
	/**
	 * @var string
	 */
	private $name;

	/**
	 * @var boolean
	 */
	private $isAutomatic;

	public function __construct($name, $isAutomatic = true)
	{
		$this->setName($name);
		$this->setIsAutomatic($isAutomatic);
	}

	/**
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * @param string $name
	 */
	public function setName($name)
	{
		$this->name = $name;
	}

	/**
	 * @return bool
	 */
	public function isAutomatic()
	{
		return $this->isAutomatic;
	}

	/**
	 * @param bool $isAutomatic
	 */
	public function setIsAutomatic($isAutomatic)
	{
		$this->isAutomatic = $isAutomatic;
	}



}