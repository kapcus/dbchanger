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
	 * @param string $userName
	 *
	 * @return mixed
	 */
	public function createEnvironmentUser(Environment $environment, $userName);

	/**
	 * @param \Kapcus\DbChanger\Model\Environment $environment
	 * @param string $userName
	 *
	 * @return \Kapcus\DbChanger\Model\User
	 */
	public function getEnvironmentUserByName(Environment $environment, $userName);

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
	 * @param \Kapcus\DbChanger\Model\Environment $environment
	 *
	 * @return mixed
	 */
	public function loadEnvironment(Environment $environment);

	public function begin();
	public function commit();
	public function rollback();
}