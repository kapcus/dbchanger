<?php

namespace Kapcus\DbChanger\Model\Database;

use Kapcus\DbChanger\Model\IDatabase;

class Oracle implements IDatabase
{
	/**
	 * @return string
	 */
	function getChangeUserSql($user)
	{
		return sprintf('ALTER SESSION SET CURRENT_SCHEMA = %1$s;', $user);
	}
}