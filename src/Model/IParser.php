<?php

namespace Kapcus\DbChanger\Model;

interface IParser
{

	//function applyOnEachStatement($sqlContent, $callback);

	/**
	 * @param string $sqlContent
	 *
	 * @return \Kapcus\DbChanger\Model\ParsedStatement[]
	 */
	function parseContent($sqlContent);

	/**
	 * @return string
	 */
	function getDelimiter();

	/**
	 * @param \Kapcus\DbChanger\Model\IParsingRule $rule
	 *
	 * @return void
	 */
	function addRule(IParsingRule $rule);

}