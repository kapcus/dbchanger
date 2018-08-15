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
		foreach ($groups as $group) {
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
		foreach ($userGroups as $userGroup) {
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
		foreach ($connectionConfigurations as $connectionConfiguration) {
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
	 * Parse fragment index, in case input is not valid, null is returned.
	 *
	 * @param string $input
	 *
	 * @return int|null
	 */
	public static function parseFragmentIndex($input)
	{
		return self::parseId('X', $input);
	}

	/**
	 * Parse fragment id, in case input is not valid, null is returned.
	 *
	 * @param string $input
	 *
	 * @return int|null
	 */
	public static function parseFragmentId($input)
	{
		return self::parseId('F', $input);
	}

	/**
 * Parse installation id, in case input is not valid, null is returned.
 *
 * @param string $input
 *
 * @return int|null
 */
	public static function parseInstallationId($input)
	{
		return self::parseId('I', $input);
	}

	/**
	 * Parse log id, in case input is not valid, null is returned.
	 *
	 * @param string $input
	 *
	 * @return int|null
	 */
	public static function parseLogId($input)
	{
		return self::parseId('L', $input);
	}

	/**
	 * @param int $id
	 *
	 * @return string
	 */
	public static function getFragmentId($id)
	{
		return sprintf('F%s', $id);
	}

	/**
	 * @param int $index
	 *
	 * @return string
	 */
	public static function getFragmentIndex($index)
	{
		return sprintf('X%s', $index);
	}

	/**
	 * @param int $id
	 *
	 * @return string
	 */
	public static function getInstallationId($id)
	{
		return sprintf('I%s', $id);
	}

	/**
	 * @param int $id
	 *
	 * @return string
	 */
	public static function getLogId($id)
	{
		return sprintf('L%s', $id);
	}

	/**
	 * Parse fragment range. Returns all fragment ids within the range, FALSE in case of invalid range.
	 *
	 * @param string $target
	 *
	 * @return int[]|null
	 */
	public static function parseFragmentRange($target)
	{
		$target = str_replace(' ', '', $target);
		$chunks = explode('-', $target);
		if (count($chunks) != 2) {
			return null;
		}
		$start = self::parseFragmentId($chunks[0]);
		$end = self::parseFragmentId($chunks[1]);
		if ($start == null || $end == null) {
			return null;
		}
		$ids = [];
		for ($i = $start; $i <= $end; $i++) {
			$ids[] = $i;
		}

		return $ids;
	}

	/**
	 * Parse id from input. In case invalid input is given returns null.
	 *
	 * @param string $shortcutLetter
	 * @param string $input
	 *
	 * @return null|int
	 */
	private static function parseId($shortcutLetter, $input)
	{
		$input = trim($input);

		if (!is_string($input)) {
			return null;
		}
		if ($input[0] != $shortcutLetter) {
			return null;
		}
		$id = substr($input, 1);
		if (!is_numeric($id)) {
			return null;
		}
		return intval($id);
	}
}