<?php

namespace Kapcus\DbChanger\Model\Reporting;

class Cell {

	/**
	 * @var \Kapcus\DbChanger\Model\Reporting\Column
	 */
	private $column;

	/**
	 * @var mixed
	 */
	private $value;

	/**
	 * Cell constructor.
	 *
	 * @param \Kapcus\DbChanger\Model\Reporting\Column $column
	 * @param mixed $value
	 */
	public function __construct(Column $column, $value)
	{
		$this->setColumn($column);
		$this->setValue($value);
	}

	/**
	 * @return \Kapcus\DbChanger\Model\Reporting\Column
	 */
	public function getColumn()
	{
		return $this->column;
	}

	/**
	 * @param \Kapcus\DbChanger\Model\Reporting\Column $column
	 */
	public function setColumn($column)
	{
		$this->column = $column;
	}

	/**
	 * @return mixed
	 */
	public function getValue()
	{
		return $this->value;
	}

	/**
	 * @param mixed $value
	 */
	public function setValue($value)
	{
		$this->value = $value;
	}


}