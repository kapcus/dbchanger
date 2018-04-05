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
	 * @param string $fragmentId
	 *
	 * @return bool
	 */
	public static function isFragmentId($fragmentId)
	{
		if (!is_string($fragmentId)) {
			return false;
		}
		if ($fragmentId[0] != 'F') {
			return false;
		}
		return is_numeric(substr($fragmentId, 1));
	}

	/**
	 * @param string $fragmentIndex
	 *
	 * @return bool
	 */
	public static function isFragmentIndex($fragmentIndex)
	{
		if (!is_string($fragmentIndex)) {
			return false;
		}
		if ($fragmentIndex[0] != 'I') {
			return false;
		}
		return is_numeric(substr($fragmentIndex, 1));
	}

	/**
	 * @param string $fragmentId
	 *
	 * @return int|null
	 */
	public static function getIdFromFragmentId($fragmentId) {
		if (self::isFragmentId($fragmentId)) {
			return intval(substr($fragmentId, 1));
		} else {
			return null;
		}
	}

	/**
	 * @param int $id
	 *
	 * @return string
	 */
	public static function getFragmentId($id) {
		return sprintf('F%s', $id);
	}

	/**
	 * @param int $index
	 *
	 * @return string
	 */
	public static function getFragmentIndex($index) {
		return sprintf('I%s', $index);
	}

	/**
	 * @param string $fragmentIndex
	 *
	 * @return int|null
	 */
	public static function getIndexFromFragmentIndex($fragmentIndex) {
		if (self::isFragmentIndex($fragmentIndex)) {
			return intval(substr($fragmentIndex, 1));
		} else {
			return null;
		}
	}
}