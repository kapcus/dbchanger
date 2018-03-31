<?php

namespace Kapcus\DbChanger\Model\Reporting;

class Column {

	const TYPE_NUMERIC = 'N';
	const TYPE_STRING = 'S';

	const DEFAULT_STRING_WIDTH = 15;

	/**
	 * @var string
	 */
	private $title;

	/**
	 * @var bool
	 */
	private $isVisible = true;

	/**
	 * @var string
	 */
	private $type;

	/**
	 * @var int
	 */
	private $width;

	private $types = [
		self::TYPE_NUMERIC,
		self::TYPE_STRING
	];

	public function __construct($title, $type = self::TYPE_STRING, $width = self::DEFAULT_STRING_WIDTH, $isVisible = true)
	{
		$this->setTitle($title);
		$this->setType(!in_array($type, self::getTypes()) ? self::TYPE_STRING : $type);
		$this->setWidth(!is_numeric($width) ? self::DEFAULT_STRING_WIDTH : intval($width));
		$this->setIsVisible($isVisible);
	}

	public function getTypes()
	{
		return $this->types;
	}

	/**
	 * @return bool
	 */
	public function isVisible()
	{
		return $this->isVisible;
	}

	/**
	 * @param bool $isVisible
	 */
	public function setIsVisible($isVisible)
	{
		$this->isVisible = $isVisible;
	}

	/**
	 * @return string
	 */
	public function getType()
	{
		return $this->type;
	}

	/**
	 * @param string $type
	 */
	public function setType($type)
	{
		$this->type = $type;
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
	public function setWidth($width)
	{
		$this->width = $width;
	}

	/**
	 * @return string
	 */
	public function getTitle()
	{
		return $this->title;
	}

	/**
	 * @param string $title
	 */
	public function setTitle($title)
	{
		$this->title = $title;
	}


}