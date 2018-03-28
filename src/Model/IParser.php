<?php

namespace Kapcus\DbChanger\Model;

interface IParser
{

	//function applyOnEachStatement($sqlContent, $callback);

	/**
	 * @param string $sqlContent
	 *
	 * @return string[]
	 */
	function getStatements($sqlContent);

	/**
	 * @return string
	 */
	function getDelimiter();

}