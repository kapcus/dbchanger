<?php

namespace Kapcus\DbChanger\Model;

class ParsedStatement {
	/**
	 * @var \Kapcus\DbChanger\Model\IParsingRule
	 */
	private $rule = null;

	/**
	 * @var string
	 */
	private $content;

	public function __construct(IParsingRule $rule = null)
	{
		$this->setRule($rule);
	}

	/**
	 * @return \Kapcus\DbChanger\Model\IParsingRule
	 */
	public function getRule()
	{
		return $this->rule;
	}

	/**
	 * @param \Kapcus\DbChanger\Model\IParsingRule $rule
	 */
	public function setRule($rule)
	{
		$this->rule = $rule;
	}

	/**
	 * @return string
	 */
	public function getContent()
	{
		return $this->content;
	}

	/**
	 * @return null|string
	 */
	public function getUpdatedContent() {
		return ($this->getRule() == null) ? $this->content : $this->getRule()->getParsedStatement($this->content);
	}

	/**
	 * @param string $content
	 */
	public function setContent($content)
	{
		$this->content = $content;
	}

	public function getDelimiter() {
		return ($this->getRule() == null) ? Parser::DELIMITER : $this->getRule()->getDelimiter();
	}
}