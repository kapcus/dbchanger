<?php

namespace Kapcus\DbChanger\Model;

interface IStorage
{
	/**
	 * @param string $dbChangeCode
	 *
	 * @return \Kapcus\DbChanger\Model\DbChange|null
	 * @throws \Kapcus\DbChanger\Model\Exception\StorageException
	 */
	public function getDbChangeByCode($dbChangeCode);

	/**
	 * @return \Kapcus\DbChanger\Model\DbChange[]
	 * @throws \Kapcus\DbChanger\Model\Exception\StorageException
	 */
	public function getDbChangeCodes();

	/**
	 * @param \Kapcus\DbChanger\Model\DbChange $dbChange
	 *
	 * @return \Kapcus\DbChanger\Model\DbChange (persistent)
	 * @throws \Kapcus\DbChanger\Model\Exception\StorageException
	 */
	public function storeDbChange(DbChange $dbChange);

	/**
	 * @param \Kapcus\DbChanger\Model\User $user
	 *
	 * @return \Kapcus\DbChanger\Model\User (persistent)
	 * @throws \Kapcus\DbChanger\Model\Exception\StorageException
	 */
	public function storeUser(User $user);

	/**
	 * @param int $id
	 * @param \Kapcus\DbChanger\Model\User $user
	 *
	 * @return mixed
	 */
	public function loadUserById($id, User $user);

	/**
	 * @param \Kapcus\DbChanger\Model\Group $group
	 *
	 * @return mixed
	 */
	public function loadGroup(Group $group);

	/**
	 * @param \Kapcus\DbChanger\Model\Group $group
	 *
	 * @return \Kapcus\DbChanger\Model\Group (persistent)
	 * @throws \Kapcus\DbChanger\Model\Exception\StorageException
	 */
	public function storeGroup(Group $group);

	/**
	 * @param string $environmentCode
	 *
	 * @return \Kapcus\DbChanger\Model\Environment|null (persistent)
	 */
	public function getEnvironmentByCode($environmentCode);

	/**
	 * @param \Kapcus\DbChanger\Model\Environment $environment
	 *
	 * @return \Kapcus\DbChanger\Model\Environment (persistent)
	 * @throws \Kapcus\DbChanger\Model\Exception\StorageException
	 */
	public function storeEnvironment(Environment $environment);

	/**
	 * @param \Kapcus\DbChanger\Model\Environment $environment
	 * @param \Kapcus\DbChanger\Model\UserGroup $userGroup
	 *
	 * @return mixed
	 * @throws \Kapcus\DbChanger\Model\Exception\StorageException
	 */
	public function createEnvironmentUserGroup(Environment $environment, UserGroup $userGroup);

	/**
	 * @param string $userName
	 *
	 * @return \Kapcus\DbChanger\Model\User
	 */
	public function getUserByName($userName);

	/**
	 * @param string $groupName
	 *
	 * @return \Kapcus\DbChanger\Model\Group
	 */
	public function getGroupByName($groupName);

	/**
	 * @param int $environmentId
	 * @param int $dbChangeId
	 *
	 * @return boolean
	 * @throws \Kapcus\DbChanger\Model\Exception\StorageException
	 */
	public function isDbChangeInstalled($environmentId, $dbChangeId);

	/**
	 * @param string $sqlQuery
	 * @return void
	 * @throws \Kapcus\DbChanger\Model\Exception\StorageException
	 */
	public function query($sqlQuery);

	/**
	 * @param \Kapcus\DbChanger\Model\Fragment $fragment
	 * @param \Kapcus\DbChanger\Model\User $user
	 *
	 * @return void
	 * @throws \Kapcus\DbChanger\Model\Exception\StorageException
	 */
	public function confirmFragmentIsInstalled(Fragment $fragment, User $user);

	/**
	 * Loads IDs for environment and assigned users in group.
	 *
	 * @param \Kapcus\DbChanger\Model\Environment $environment
	 *
	 * @return mixed
	 */
	public function loadEnvironment(Environment $environment);

	/**
	 * @param \Kapcus\DbChanger\Model\DbChange $dbChange
	 * @param \Kapcus\DbChanger\Model\Environment $environment
	 *
	 * @return array
	 */
	public function getInstallationResults(DbChange $dbChange, Environment $environment);

	/**
	 * @return \Kapcus\DbChanger\Model\Group[]
	 */
	public function getGroups();

	public function begin();
	public function commit();
	public function rollback();
}