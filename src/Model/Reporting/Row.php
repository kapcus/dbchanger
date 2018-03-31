<?php

namespace Kapcus\DbChanger\Model\Reporting;

class Row {

	/**
	 * @var int
	 */
	private $width = 0;

	/**
	 * @var bool
	 */
	private $isHeader = false;

	/**
	 * @var \Kapcus\DbChanger\Model\Reporting\Cell[]
	 */
	private $cells = [];

	/**
	 * @var \Kapcus\DbChanger\Model\Reporting\Column[]
	 */
	private $columns = [];

	/**
	 * @var int
	 */
	private $index;

	private $pointer = -1;

	/**
	 * @return bool
	 */
	public function isHeader()
	{
		return $this->isHeader;
	}

	public function reset() {
		$this->pointer = -1;
	}

	/**
	 * @param bool $isHeader
	 */
	public function setIsHeader($isHeader)
	{
		$this->isHeader = $isHeader;
	}

	/**
	 * @param \Kapcus\DbChanger\Model\Reporting\Cell $cell
	 */
	public function addCell(Cell $cell)
	{
		$this->cells[$cell->getColumn()->getTitle()] = $cell;
	}

	public function getCurrentColumn() {
		if (!isset($this->columns[$this->pointer])) {
			throw new \RuntimeException('Undefined column.');
		}
		return $this->columns[$this->pointer];
	}

	/**
	 * @param string $title
	 *
	 * @return \Kapcus\DbChanger\Model\Reporting\Column|null
	 */
	public function getColumnByTitle($title) {
		foreach ($this->getColumns() as $column) {
			if ($column->getTitle() == $title) {
				return $column;
			}
		}
		return null;
	}

	public function getNextColumn() {
		$this->pointer++;
		return $this->getCurrentColumn();
	}

	/**
	 * @return \Kapcus\DbChanger\Model\Reporting\Cell[]
	 */
	public function getCells() {
		return $this->cells;
	}

	/**
	 * @return int
	 */
	public function getIndex()
	{
		return $this->index;
	}

	/**
	 * @param int $index
	 */
	public function setIndex($index)
	{
		$this->index = $index;
	}

	/**
	 * @return \Kapcus\DbChanger\Model\Reporting\Column[]
	 */
	public function getColumns()
	{
		return $this->columns;
	}

	/**
	 * @param \Kapcus\DbChanger\Model\Reporting\Column $column
	 */
	public function addColumn(Column $column)
	{
		$this->columns[] = $column;
		$this->cells[$column->getTitle()] = new Cell($column, $column->getTitle());
		$this->addWidth($column->getWidth());
	}

	/**
	 * @return int
	 */
	public function getWidth()
	{
		return $this->width;
	}

	/**
	 * @param int $width
	 */
	public function addWidth($width)
	{
		$this->width += $width;
	}

}