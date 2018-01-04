<?php

namespace Kapcus\DbChanger\Model;

class Placeholder
{
	/**
	 * @var string
	 */
	private $code;

	/**
	 * @var string
	 */
	private $value;

	public function __construct($code, $value)
	{
		$this->setCode($code);
		$this->setValue($value);
	}

	/**
	 * @return string
	 */
	public function getCode()
	{
		return $this->code;
	}

	/**
	 * @param string $code
	 */
	public function setCode($code)
	{
		$this->code = $code;
	}

	/**
	 * @return string
	 */
	public function getValue()
	{
		return $this->value;
	}

	/**
	 * @param string $value
	 */
	public function setValue($value)
	{
		$this->value = $value;
	}


}