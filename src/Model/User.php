<?php

namespace Kapcus\DbChanger\Model;

class User
{
	/**
	 * @var string
	 */
	private $name;

	public function __construct($name)
	{
		$this->setName($name);
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

}