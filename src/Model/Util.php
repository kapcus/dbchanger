<?php

namespace Kapcus\DbChanger\Model;

class Util
{
	/**
	 * @param \Kapcus\DbChanger\Entity\Group[] $groups
	 * @param $groupName
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
	 *
	 * @return \Kapcus\DbChanger\Entity\Group[]
	 */
	public static function getGroupsFromUserGroups($userGroupObj)
	{
		$groups = [];
		foreach($userGroupObj as $userGroup) {
			$groups[] = $userGroup->getGroup();
		}
		return $groups;
	}

	/**
	 *
	 * @return \Kapcus\DbChanger\Entity\User[]
	 */
	public static function getUsersFromUserGroup($userGroupObj, $groupName)
	{
		$users = [];
		foreach($userGroupObj as $userGroup) {
			if ($userGroup->getGroup()->getName() == $groupName) {
				$users[] = $userGroup->getUser();
			}
		}
		return $users;
	}
}