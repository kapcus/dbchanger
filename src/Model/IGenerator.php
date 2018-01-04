<?php

namespace Kapcus\DbChanger\Model;

interface IGenerator
{
	/**
	 * @param \Kapcus\DbChanger\Model\Environment $environment
	 * @param \Kapcus\DbChanger\Model\DbChange $dbChange
	 *
	 * @return bool
	 */
	function generateDbChange(Environment $environment, DbChange $dbChange);
}