<?php

namespace Kapcus\DbChanger\Model\Rule;

use Kapcus\DbChanger\Model\IParsingRule;

class SpecialDelimiter implements IParsingRule {

	/**
	 *
	 * @return string
	 */
	function getKey()
	{
		return 'DELIMITER';
	}

	function getParsedStatement($inputStatement)
	{
		return $inputStatement;
	}

	/**\
	 * @return null|string
	 */
	function getDelimiter()
	{
		return '/';
	}
}