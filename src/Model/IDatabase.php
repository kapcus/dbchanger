<?php

namespace Kapcus\DbChanger\Model;

interface IDatabase
{
	/**
	 * @param string $user
	 *
	 * @return string
	 */
	function getChangeUserSql($user);

}