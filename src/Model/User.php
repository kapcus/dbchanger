<?php

namespace Kapcus\DbChanger\Model;

class User
{
	/**
	 * @var int
	 */
	private $id;

	/**
	 * @var string
	 */
	private $name;

	/**
	 * @var \Kapcus\DbChanger\Model\Environment
	 */
	private $environment;

	public function __construct($id, Environment $environment, $name)
	{
		$this->setId($id);
		$this->setName($name);
		$this->setEnvironment($environment);
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
	 * @return \Kapcus\DbChanger\Model\Environment
	 */
	public function getEnvironment()
	{
		return $this->environment;
	}

	/**
	 * @param \Kapcus\DbChanger\Model\Environment $environment
	 */
	public function setEnvironment($environment)
	{
		$this->environment = $environment;
	}

	/**
	 * @return int
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * @param int $id
	 */
	public function setId($id)
	{
		$this->id = $id;
	}

	/**
	 * @return bool
	 */
	public function isPersistent() {
		return $this->getId() !== null;
	}




}