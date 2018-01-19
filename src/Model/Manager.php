<?php

namespace Kapcus\DbChanger\Model;

use Kapcus\DbChanger\Model\Exception\DbChangeException;
use Kapcus\DbChanger\Model\Exception\EnvironmentException;

class Manager
{
	/**
	 * @var \Kapcus\DbChanger\Model\IStorage
	 */
	private $storage;

	/**
	 * @var \Kapcus\DbChanger\Model\IExecutor
	 */
	private $executor;

	/**
	 * @var \Kapcus\DbChanger\Model\ILoader
	 */
	private $loader;

	/**
	 * @var \Kapcus\DbChanger\Model\DbChangeFactory
	 */
	private $dbChangeFactory;

	public function __construct(IStorage $storage, IExecutor $executor, ILoader $loader, DbChangeFactory $dbChangeFactory)
	{
		$this->storage = $storage;
		$this->executor = $executor;
		$this->loader = $loader;
		$this->dbChangeFactory = $dbChangeFactory;
	}

	/**
	 * @param string $dbChangeCode
	 *
	 * @return \Kapcus\DbChanger\Model\DbChange (persistent)
	 * @throws \Kapcus\DbChanger\Model\Exception\DbChangeException
	 * @throws \Kapcus\DbChanger\Model\Exception\StorageException
	 */
	public function registerDbChangeByCode($dbChangeCode)
	{
		$dbChange = $this->dbChangeFactory->createDbChange($dbChangeCode);
		$dbChangesCodes = $this->storage->getDbChangeCodes();
		if (array_key_exists($dbChange->getCode(), $dbChangesCodes)) {
			throw new DbChangeException(sprintf('DbChange %s is already registered.', $dbChange->getCode()));
		}

		return $this->storage->storeDbChange($dbChange);
	}

	public function initializeEnvironment(Environment $environment)
	{
		$registeredEnvironment = $this->storage->getEnvironmentByCode($environment->getCode());
		if ($registeredEnvironment !== null) {
			throw new DbChangeException();
		}
		$this->storage->begin();
		$this->storage->storeEnvironment($environment);
		foreach($environment->getUsers() as $user) {
			$this->storage->createEnvironmentUser($environment, $user->getName());
		}
		$this->storage->commit();
	}

	/**
	 * @param \Kapcus\DbChanger\Model\Environment $environment
	 * @param string $dbChangeCode
	 *
	 * @throws \Kapcus\DbChanger\Model\Exception\DbChangeException - DbChange is not registered
	 * @throws \Kapcus\DbChanger\Model\Exception\EnvironmentException - Environment is not initialized
	 * @throws \Kapcus\DbChanger\Model\Exception\StorageException
	 */
	public function installDbChange(Environment $environment, $dbChangeCode, $skipManual)
	{
		$dbChange = $this->storage->getDbChangeByCode($dbChangeCode);
		if ($dbChange === null) {
			throw new DbChangeException(sprintf('DbChange %s has not been registered yet.', $dbChangeCode));
		}
		$initializedEnvironment = $this->storage->getEnvironmentByCode($environment->getCode());
		if ($initializedEnvironment === null) {
			throw new EnvironmentException(sprintf('Environment %s has not been initialized yet.', $environment->getCode()));
		}
		$this->storage->loadEnvironment($environment);
		$this->checkInstalledDbChange($environment, $dbChange);
		$this->doInstall($environment, $dbChange, $skipManual);
	}

	public function checkInstalledDbChange(Environment $environment, DbChange $dbChange)
	{
		if (!$environment->isFullyPersistent() || !$dbChange->isFullyPersistent()) {
			throw new DbChangeException(sprintf('Both environment and dbchange objects must be persistent.'));
		}
		$isInstalled = $this->storage->isDbChangeInstalled($environment->getId(), $dbChange->getId());
		if ($isInstalled) {
			throw new EnvironmentException(
				sprintf('DbChange %s is already installed in environment %s.', $dbChange->getCode(), $environment->getCode())
			);
		}
	}

	/**
	 * @param \Kapcus\DbChanger\Model\Environment $environment
	 * @param \Kapcus\DbChanger\Model\DbChange $dbChange
	 * @param boolean $skipManual
	 *
	 * @throws \Kapcus\DbChanger\Model\Exception\StorageException
	 */
	private function doInstall(Environment $environment, DbChange $dbChange, $skipManual)
	{
		$dbChange->generate($environment);
		if ($skipManual) {
			$fragments = $dbChange->getAutomaticFragments();
		} else {
			$fragments = $dbChange->getFragments();
		}
		foreach ($fragments as $fragment) {
			foreach($fragment->getUsers() as $user) {
				$this->executor->begin();
				$this->executor->loadContent($fragment->getUserContent($user));
				$this->storage->confirmFragmentIsInstalled($fragment, $user);
				$this->executor->commit();
			}
		}
	}

	/*public function commitInstalledDbChange(Environment $environment, DbChange $dbChange) {
		if (!$environment->isPersistent() || !$dbChange->isPersistent()) {
			throw new DbChangeException(sprintf('Both environment and dbchange objects must be persistent.'));
		}
		return $this->connection->query($this->database->createInstalledDbChange(), $environment->getId(), $dbChange->getId());
	}*/

	/**
	 * @param string $environmentCode
	 *
	 * @return \Kapcus\DbChanger\Model\Environment
	 * @throws \Dibi\Exception
	 * @throws \Kapcus\DbChanger\Model\Exception\DbChangeException
	 */
	public function getEnvironmentByCode($environmentCode)
	{
		$storedEnvironment = $this->connection->query($this->database->getEnvironmentByCode(), $environmentCode)->fetch();
		if ($storedEnvironment === false) {
			throw new DbChangeException(sprintf('Environment %s has not been initialized yet.', $environmentCode));
		}

		return new Environment($storedEnvironment['ID'], $storedEnvironment['CODE'], $storedEnvironment['NAME']);
	}
}