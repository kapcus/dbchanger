<?php

namespace Kapcus\DbChanger\Model;

use Doctrine\Common\Util\Debug;
use Kapcus\DbChanger\Model\Rule\GlobalView;
use Kapcus\DbChanger\Model\Rule\SpecialDelimiter;

class Parser implements IParser
{
	const DELIMITER = ';';

	/**
	 * @var \Kapcus\DbChanger\Model\IParsingRule[]
	 */
	private $rules = [];

	/**
	 * @var \Kapcus\DbChanger\Model\ParsedStatement[]
	 */
	private $statements = [];

	/**
	 * @var string[]
	 */
	private $statementLines = [];

	public function __construct()
	{
		$this->loadRules();
	}

	/*function applyOnEachStatement($sqlContent, $callback)
	{
		$lines = preg_split('/$\R?^/m', $sqlContent);
		$sqlQuery = '';
		foreach ($lines as $line) {
			if (strpos(ltrim($line), '--') === 0 || strpos(ltrim($line), '//') === 0) {
				continue;
			}
			$sqlQuery .= $line;
			if (substr(rtrim($sqlQuery), -1) === ';') {
				//$callback(rtrim(rtrim($sqlQuery), ';'));
				call_user_func($callback, rtrim(rtrim($sqlQuery), ';'));
				$sqlQuery = '';
			}
		}
		// missing delimiter at the end of the script file
		if (trim($sqlQuery) !== '') {
			call_user_func($callback, $sqlQuery);
			//$callback($sqlQuery);
		}
	}*/

	private function loadRules() {
		$this->addRule(new GlobalView());
		$this->addRule(new SpecialDelimiter());
	}

	/**
	 * @param string $sqlContent
	 *
	 * @return \Kapcus\DbChanger\Model\ParsedStatement[]
	 */
	public function parseContent($sqlContent)
	{
		$this->resetStatements();
		$lines = preg_split('/$\R?^/m', $sqlContent);
		//$sqlQuery = '';
		$statementLines = [];
		$ruleKey = null;
		foreach ($lines as $line) {
			$line = rtrim($line);
			if ($line == '') {
				continue;
			}
			if (strpos(ltrim($line), '--') === 0 || strpos(ltrim($line), '//') === 0) {
				$comment = strtoupper(trim(substr($line, 2)));
				if ($this->getRuleByKey($comment) !== null) {
					$ruleKey = $comment;
					$statementLines[] = $line;
				}
				continue;
			}

			if (substr($line, -1) === $this->getDelimiterForParsing($ruleKey)) {
				$line = rtrim($line, $this->getDelimiterForParsing($ruleKey));
				if ($this->getDelimiterForParsing($ruleKey) == self::DELIMITER) {
				}
				if ($line != '') {
					$statementLines[] = $line;
				}
				$this->addStatement($statementLines, $ruleKey);
				$statementLines = [];
				$ruleKey = null;
			} else {
				$statementLines[] = $line;
			}
		}
		// missing delimiter at the end of the script file
		if (!empty($statementLines)) {
			$this->addStatement($statementLines, $ruleKey);
		}

		return $this->getStatements();
	}

	private function getDelimiterForParsing($ruleKey) {
		if ($ruleKey == null) {
			return $this->getDelimiter();
		}
		$rule = $this->getRuleByKey($ruleKey);
		return ($rule == null || $rule->getDelimiter() == null) ? $this->getDelimiter() : $rule->getDelimiter();
	}

	/**
	 * @return \Kapcus\DbChanger\Model\ParsedStatement[]
	 */
	public function getStatements() {
		return $this->statements;
	}

	private function resetStatements() {
		$this->statements = [];
		$this->resetStatementLines();
	}

	/**
	 * @param string[] $statementLines
	 * @param string $ruleKey
	 */
	public function addStatement($statementLines, $ruleKey) {
		$rule = $this->getRuleByKey($ruleKey);
		$statement = new ParsedStatement($rule);
		$statement->setContent(implode("\n", $statementLines));
		$this->statements[] = $statement;
	}

	public function getDelimiter() {
		return self::DELIMITER;
	}

	/**
	 * @return \Kapcus\DbChanger\Model\IParsingRule[]
	 */
	public function getRules()
	{
		return $this->rules;
	}

	/**
	 * @param string $key
	 *
	 * @return \Kapcus\DbChanger\Model\IParsingRule|null
	 */
	public function getRuleByKey($key) {
		$key = trim($key);
		foreach($this->getRules() as $rule) {
			if ($key == $rule->getKey()) {
				return $rule;
			}
		}
		return null;
	}

	/**
	 * @param \Kapcus\DbChanger\Model\IParsingRule $rule
	 */
	public function addRule(IParsingRule $rule)
	{
		$this->rules[] = $rule;
	}

	/**
	 * @return string[]
	 */
	public function getStatementLines()
	{
		return $this->statementLines;
	}

	/**
	 * @param string $statementLine
	 */
	public function addStatementLine($statementLine)
	{
		$this->statementLines[] = $statementLine;
	}

	/**
	 *
	 */
	public function resetStatementLines()
	{
		$this->statementLines = [];
	}

}