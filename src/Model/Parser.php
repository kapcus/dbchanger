<?php

namespace Kapcus\DbChanger\Model;

class Parser implements IParser
{
	const DELIMITER = ';';

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

	public function getStatements($sqlContent)
	{
		$statements = [];
		$lines = preg_split('/$\R?^/m', $sqlContent);
		$sqlQuery = '';
		foreach ($lines as $line) {
			if (strpos(ltrim($line), '--') === 0 || strpos(ltrim($line), '//') === 0) {
				continue;
			}
			$sqlQuery .= $line;
			if (substr(rtrim($sqlQuery), -1) === self::DELIMITER) {
				$statements[] = rtrim(rtrim($sqlQuery), self::DELIMITER);
				$sqlQuery = '';
			}
		}
		// missing delimiter at the end of the script file
		if (trim($sqlQuery) !== '') {
			$statements[] = $sqlQuery;
		}

		return $statements;
	}

	public function getDelimiter() {
		return self::DELIMITER;
	}
}