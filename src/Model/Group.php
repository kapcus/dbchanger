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
	private $isManual;

	public function __construct($name, $isManual = false)
	{
		$this->setName($name);
		$this->setIsManual($isManual);
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
	public function isManual()
	{
		return $this->isManual;
	}

	/**
	 * @param bool $isManual
	 */
	public function setIsManual($isManual)
	{
		$this->isManual = $isManual;
	}



}