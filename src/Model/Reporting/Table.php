<?php

namespace Kapcus\DbChanger\Model\Reporting;

class Table {

	/**
	 * @var \Kapcus\DbChanger\Model\Reporting\Row[]
	 */
	private $rows;

	/**
	 * @var int
	 */
	private $rowIndex = -1;

	public function __construct()
	{
		$headerRow = new Row();
		$headerRow->setIsHeader(true);
		$this->incrementRowIndex();
		$this->rows[$this->getRowIndex()] = $headerRow;
	}

	/**
	 * @param \Kapcus\DbChanger\Model\Reporting\Column $column
	 */
	public function addColumn(Column $column)
	{
		$this->getHeader()->addColumn($column);
	}

	/**
	 * @return \Kapcus\DbChanger\Model\Reporting\Row[]
	 */
	public function getRows()
	{
		return $this->rows;
	}

	/**
	 * @return \Kapcus\DbChanger\Model\Reporting\Row
	 */
	public function getCurrentRow() {
		return $this->rows[$this->getRowIndex()];
	}

	public function reset() {
		$this->resetIndex();
	}

	public function addRow()
	{
		$this->incrementRowIndex();
		$row = $this->cloneNewRow();
		$row->setIndex($this->getRowIndex());

		$this->rows[$this->getRowIndex()] = $row;
	}

	/**
	 * @return int
	 */
	public function getRowIndex()
	{
		return $this->rowIndex;
	}

	/**
	 * @return int
	 */
	public function resetIndex()
	{
		return $this->rowIndex = 0;
	}

	public function incrementRowIndex()
	{
		$this->rowIndex++;
	}

	/**
	 * @param mixed $value
	 * @param string $title
	 */
	public function addField($value, $title = null)
	{
		$row = $this->getCurrentRow();
		if ($title == null) {
			$column = $row->getNextColumn();
		} else {
			$column = $row->getColumnByTitle($title);
		}
		if ($column == null) {
			throw new \RuntimeException('Undefined column');
		}
		$this->getCurrentRow()->addCell(new Cell($column, $value));
	}

	/**
	 * @return \Kapcus\DbChanger\Model\Reporting\Row
	 */
	public function getHeader()
	{
		return $this->rows[0];
	}

	/**
	 * @return \Kapcus\DbChanger\Model\Reporting\Column[]
	 */
	public function getHeaderColumns()
	{
		return $this->getHeader()->getColumns();
	}

	/**
	 * @return int
	 */
	public function getWidth()
	{
		return $this->getHeader()->getWidth()+3*(count($this->getHeader()->getColumns())-1)+1;
	}

	private function cloneNewRow()
	{
		$row = new Row();
		foreach ($this->getHeader()->getColumns() as $column) {
			$row->addColumn($column);
		}
		return $row;
	}
}