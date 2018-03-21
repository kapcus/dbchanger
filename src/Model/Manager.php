<?php

namespace Kapcus\DbChanger\Model;

use Doctrine\Common\Util\Debug;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\Setup;
use Kapcus\DbChanger\Entity\Environment;
use Kapcus\DbChanger\Entity\Group;
use Kapcus\DbChanger\Entity\User;
use Kapcus\DbChanger\Model\Exception\DbChangeException;
use Kapcus\DbChanger\Model\Exception\EnvironmentException;
use Kapcus\DbChanger\Model\Exception\OutOfSyncException;
use Kapcus\DbChanger\Repository\UserRepository;

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

	/**
	 * @var string
	 */
	private $outputDirectory;

	/**
	 * @var EntityManager
	 */
	private $entityManager;

	/**
	 * @var \Kapcus\DbChanger\Entity\User[]
	 */
	private $users;

	/**
	 * @var \Kapcus\DbChanger\Entity\Group[]
	 */
	private $groups;

	/**
	 * @var \Kapcus\DbChanger\Entity\Environment[]
	 */
	private $environments;

	public function __construct($outputDirectory, ILoader $loader, DbChangeFactory $dbChangeFactory, EntityManager $entityManager)
	{
		$this->entityManager = $entityManager;
		$this->loader = $loader;
		$this->dbChangeFactory = $dbChangeFactory;
		$this->outputDirectory = $outputDirectory;
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
		//$groups = $this->entityManager->getGroups();
		$dbChange = $this->dbChangeFactory->createDbChange($dbChangeCode, $groups);
		$dbChangesCodes = $this->storage->getDbChangeCodes();
		if (array_key_exists($dbChange->getCode(), $dbChangesCodes)) {
			throw new DbChangeException(sprintf('DbChange %s is already registered.', $dbChange->getCode()));
		}

		return $this->storage->storeDbChange($dbChange);
	}

	/**
	 * @param \Kapcus\DbChanger\Entity\Environment[] $environments
	 *
	 * @throws \Doctrine\ORM\OptimisticLockException
	 * @throws \Kapcus\DbChanger\Model\Exception\OutOfSyncException
	 */
	public function initializeEnvironments(array $environments)
	{
		foreach ($environments as $environment) {

			$existingEnvironment = $this->getEnvironmentByCode($environment->getCode());
			if ($existingEnvironment == null) {
				//Debug::dump($environment);
				$this->entityManager->persist($environment);
			} else {
				if (!$this->compareEnvironments($environment, $existingEnvironment)) {
					throw new OutOfSyncException(sprintf('Environment %s is out of sync.', $environment->getCode()));
				}
				$environment = $existingEnvironment;
			}
			$this->addEnvironment($environment);
		}
		$this->entityManager->flush();
	}

	/**
	 * @param \Kapcus\DbChanger\Entity\User[] $users
	 *
	 * @throws \Doctrine\ORM\OptimisticLockException
	 * @throws \Kapcus\DbChanger\Model\Exception\OutOfSyncException
	 */
	public function initializeUsers(array $users)
	{
		foreach ($users as $user) {
			$existingUser = $this->entityManager->getRepository(User::class)->findOneBy(['name' => $user->getName()]);
			if ($existingUser == null) {
				$this->entityManager->persist($user);
			} else {
				if (!$this->compareUsers($user, $existingUser)) {
					throw new OutOfSyncException(sprintf('User %s is out of sync.', $user->getName()));
				}
				$user = $existingUser;
			}
			$this->addUser($user);
		}
		$this->entityManager->flush();
	}

	/**
	 * @param \Kapcus\DbChanger\Entity\Group[] $groups
	 *
	 * @throws \Doctrine\ORM\OptimisticLockException
	 * @throws \Kapcus\DbChanger\Model\Exception\OutOfSyncException
	 */
	public function initializeGroups(array $groups)
	{
		foreach ($groups as $group) {
			$existingGroup = $this->entityManager->getRepository(Group::class)->findOneBy(['name' => $group->getName()]);
			if ($existingGroup == null) {
				$this->entityManager->persist($group);
			} else {
				if (!$this->compareGroups($group, $existingGroup)) {
					throw new OutOfSyncException(sprintf('Group %s is out of sync.', $group->getName()));
				}
				$group = $existingGroup;
			}
			$this->addGroup($group);
		}
		$this->entityManager->flush();
	}

	/**
	 * @param \Kapcus\DbChanger\Model\Environment $environment
	 * @param string $dbChangeCode
	 * @param boolean $skipManual
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

		$dbChange->generate($environment);

		$this->doInstall($environment, $dbChange, $skipManual);
		if ($skipManual) {
			$this->generateManualFragments($environment, $dbChange);
		}
	}

	public function checkInstalledDbChange(Environment $environment, DbChange $dbChange)
	{
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
		if ($skipManual) {
			$fragments = $dbChange->getAutomaticFragments();
		} else {
			$fragments = $dbChange->getFragments();
		}
		foreach ($fragments as $fragment) {
			foreach ($fragment->getUsers() as $user) {
				$this->executor->begin();
				$this->executor->loadContent($fragment->getUserContent($user));
				$this->storage->confirmFragmentIsInstalled($fragment, $user);
				$this->executor->commit();
			}
		}
	}

	private function generateManualFragments(Environment $environment, DbChange $dbChange)
	{
		$chunks = [];
		foreach ($dbChange->getManualFragments() as $fragment) {
			foreach ($fragment->getUsers() as $user) {
				$chunks[] = $fragment->getUserContent($user);
				$chunks[] = '';
			}
		}
		file_put_contents(sprintf('%s_manual.sql', $this->outputDirectory . DIRECTORY_SEPARATOR . $dbChange->getCode()), implode("\n", $chunks));
	}

	public function checkDbChange(Environment $environment, DbChange $dbChangeCode)
	{
		$this->storage->isDbChangeInstalled($environment->getId(), $dbChange->getId());
	}

	/**
	 * @return \Kapcus\DbChanger\Entity\User[]
	 */
	public function getUsers()
	{
		return $this->users;
	}

	/**
	 * @param \Kapcus\DbChanger\Entity\User $user
	 */
	public function addUser(User $user)
	{
		$this->users[] = $user;
	}

	/**
	 * @return \Kapcus\DbChanger\Entity\Group[]
	 */
	public function getGroups()
	{
		//return $this->groups;
		return $this->entityManager->getRepository(Group::class)->findAll();
	}

	/**
	 * @param string $environmentCode
	 *
	 * @return null|\Kapcus\DbChanger\Entity\Environment
	 */
	public function getEnvironmentByCode($environmentCode) {
		return $this->entityManager->getRepository(Environment::class)->findOneBy(['code' => $environmentCode]);
	}

	/**
	 * @param \Kapcus\DbChanger\Entity\Group $group
	 */
	public function addGroup($group)
	{
		$this->groups[] = $group;
	}

	/**
	 * @param \Kapcus\DbChanger\Entity\User $user
	 * @param \Kapcus\DbChanger\Entity\User $secondUser
	 *
	 * @return bool
	 */
	private function compareUsers(User $user, User $secondUser)
	{
		if ($user->getName() != $secondUser->getName()) {
			return false;
		}

		return true;
	}

	/**
	 * @param \Kapcus\DbChanger\Entity\Group $group
	 * @param \Kapcus\DbChanger\Entity\Group $secondGroup
	 *
	 * @return bool
	 */
	private function compareGroups(Group $group, Group $secondGroup)
	{
		if ($group->getName() != $secondGroup->getName()) {
			return false;
		}
		if ($group->getIsManual() != $secondGroup->getIsManual()) {
			return false;
		}

		return true;
	}

	/**
	 * @return \Kapcus\DbChanger\Entity\Environment[]
	 */
	public function getEnvironments()
	{
		return $this->environments;
	}

	/**
	 * @param \Kapcus\DbChanger\Entity\Environment $environment
	 */
	public function addEnvironment(Environment $environment)
	{
		$this->environments[] = $environment;
	}

	/**
	 * @param \Kapcus\DbChanger\Entity\Environment $environment
	 * @param \Kapcus\DbChanger\Entity\Environment $existingEnvironment
	 *
	 * @return bool
	 */
	private function compareEnvironments(Environment $environment, Environment $existingEnvironment)
	{
		if ($environment->getCode() != $existingEnvironment->getCode()) {
			return false;
		}

		if ($environment->getName() != $existingEnvironment->getName()) {
			return false;
		}

		if ($environment->getDescription() != $existingEnvironment->getDescription()) {
			return false;
		}

		return true;
	}
}