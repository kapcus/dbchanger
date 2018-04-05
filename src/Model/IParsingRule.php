<?php

namespace Kapcus\DbChanger\Model;

interface IParsingRule
{

	/**
	 *
	 * @return string
	 */
	function getKey();

	/**
	 * @param $inputStatement
	 *
	 * @return mixed
	 */
	function getParsedStatement($inputStatement);

	/**\
	 * @return null|string
	 */
	function getDelimiter();

}