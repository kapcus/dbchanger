<?php

namespace Kapcus\DbChanger\Model\Rule;

use Kapcus\DbChanger\Model\IParsingRule;

class GlobalView implements IParsingRule {

	/**
	 *
	 * @return string
	 */
	function getKey()
	{
		return 'GLOBALVIEW';
	}

	function getParsedStatement($inputStatement)
	{

	}

	/**\
	 * @return null|string
	 */
	function getDelimiter()
	{
		return null;
	}
}