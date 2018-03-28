<?php

namespace Kapcus\DbChanger\Model;

class Util
{
	const FULL_CODE_SEPARATOR = '-';

	/**
	 * @param \Kapcus\DbChanger\Entity\Group[] $groups
	 * @param string $groupName
	 *
	 * @return \Kapcus\DbChanger\Entity\Group|null
	 */
	public static function getGroupByName(array $groups, $groupName)
	{
		foreach($groups as $group) {
			if ($group->getName() == $groupName) {
				return $group;
			}
		}
		return null;
	}

	/**
	 * @param \Kapcus\DbChanger\Entity\UserGroup[] $userGroups
	 * @param string $groupName
	 *
	 * @return \Kapcus\DbChanger\Entity\User[]
	 */
	public static function getUserGroupUsersByGroupName($userGroups, $groupName)
	{
		$users = [];
		foreach($userGroups as $userGroup) {
			if ($userGroup->getGroup()->getName() == $groupName) {
				$users[] = $userGroup->getUser();
			}
		}
		return $users;
	}

	/**
	 * @param \Kapcus\DbChanger\Model\ConnectionConfiguration[] $connectionConfigurations
	 * @param string $username
	 *
	 * @return \Kapcus\DbChanger\Model\ConnectionConfiguration|null
	 */
	public static function getConnectionConfigurationByUserName(array $connectionConfigurations, $username)
	{
		foreach($connectionConfigurations as $connectionConfiguration) {
			if ($connectionConfiguration->getUsername() == $username) {
				return $connectionConfiguration;
			}
		}
		return null;
	}

	/**
	 * @param \Kapcus\DbChanger\Entity\Environment[] $environments
	 * @param string $code
	 *
	 * @return \Kapcus\DbChanger\Entity\Environment|null
	 */
	public static function getEnvironmentByCode(array $environments, $code)
	{
		foreach ($environments as $environment) {
			if ($environment->getCode() === $code) {
				return $environment;
			}
		}

		return null;
	}

	/**
	 * @param string $fragmentCode
	 *
	 * @return bool
	 */
	public static function isFullCode($fragmentCode)
	{
		$chunks = explode(self::FULL_CODE_SEPARATOR, $fragmentCode);
		return count($chunks) == 4;
	}

	/**
	 * @param $fragmentFullCode
	 *
	 * @return array
	 */
	public static function getFullCodeParts($fragmentFullCode)
	{
		return explode(self::FULL_CODE_SEPARATOR, $fragmentFullCode);
	}

	public static function getFullCode($environmentCode, $dbChangeCode, $fragmentIndex, $userName) {
		return sprintf('%2$s%1$s%3$s%1$s%4$s%1$s%5$s', self::FULL_CODE_SEPARATOR, $environmentCode, $dbChangeCode, $fragmentIndex, $userName);
	}
}