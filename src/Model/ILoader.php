<?php

namespace Kapcus\DbChanger\Model;

interface ILoader
{
	/**
	 * @return \Kapcus\DbChanger\Model\DbChange[]
	 */
	function loadDbChanges();
}