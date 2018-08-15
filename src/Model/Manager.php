<?php

namespace Kapcus\DbChanger\Model;

use Doctrine\Common\Util\Debug;
use Doctrine\ORM\EntityManager;
use Kapcus\DbChanger\Entity\DbChange;
use Kapcus\DbChanger\Entity\Environment;
use Kapcus\DbChanger\Entity\Fragment;
use Kapcus\DbChanger\Entity\Group;
use Kapcus\DbChanger\Entity\Installation;
use Kapcus\DbChanger\Entity\InstallationLog;
use Kapcus\DbChanger\Entity\InstalledFragment;
use Kapcus\DbChanger\Entity\Placeholder;
use Kapcus\DbChanger\Entity\Requirement;
use Kapcus\DbChanger\Entity\User;
use Kapcus\DbChanger\Entity\UserGroup;
use Kapcus\DbChanger\Model\Exception\ConnectionException;
use Kapcus\DbChanger\Model\Exception\DbChangeException;
use Kapcus\DbChanger\Model\Exception\EnvironmentException;
use Kapcus\DbChanger\Model\Exception\ExecutionException;
use Kapcus\DbChanger\Model\Exception\InstallationException;
use Kapcus\DbChanger\Model\Exception\OutOfSyncException;
use Kapcus\DbChanger\Model\Reporting\Column;
use Kapcus\DbChanger\Model\Reporting\Table;

class Manager
{
	/**
	 * @var \Kapcus\DbChanger\Model\IExecutor
	 */
	private $executor;

	/**
	 * @var \Kapcus\DbChanger\Model\ILoader
	 */
	private $loader;

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

	/**
	 * @var \Kapcus\DbChanger\Model\IGenerator
	 */
	private $generator;

	const DEFAULT_USER = 'user';

	public function __construct(
		$outputDirectory,
		ILoader $loader,
		EntityManager $entityManager,
		IGenerator $generator,
		IExecutor $executor
	) {
		$this->entityManager = $entityManager;
		$this->loader = $loader;
		$this->outputDirectory = $outputDirectory;
		$this->generator = $generator;
		$this->executor = $executor;
	}

	/**
	 * @param \Kapcus\DbChanger\Entity\DbChange $dbChange
	 *
	 * @param bool $ignoreRequirements TRUE will completely ignore all dependant dbCchanges
	 * @param bool $overwriteExisting TRUE will overwrite existing dbChange with the same code if any
	 *
	 * @throws \Doctrine\ORM\OptimisticLockException
	 * @throws \Kapcus\DbChanger\Model\Exception\DbChangeException
	 */
	public function registerDbChange(DbChange $dbChange, $ignoreRequirements = false, $overwriteExisting = false)
	{
		$existingDbChange = $this->getActiveDbChangeByCodeIfExists($dbChange->getCode());
		if ($existingDbChange != null) {
			if (!$overwriteExisting) {
				if (!$this->isEqualDbChanges($dbChange, $existingDbChange)) {
					throw new DbChangeException(sprintf('User %s is out of sync.', $dbChange->getCode()));
				} else {
					throw new DbChangeException(sprintf('DbChange %s is already registered.', $dbChange->getCode()));
				}
			} elseif ($this->hasDbChangePendingInstallation($existingDbChange)) {
				throw new DbChangeException(sprintf('DbChange %s has pending installations, deal with them first.', $dbChange->getCode()));
			}
			$dbChange->setVersionNumber($existingDbChange->getVersionNumber() + 1);
			$existingDbChange->setIsActive(false);
			$this->entityManager->persist($existingDbChange);
		}
		$dbChange->loadFragmentTemplateContent();
		$this->entityManager->persist($dbChange);
		if (!$ignoreRequirements) {
			foreach ($dbChange->getReqDbChanges() as $requiredDbChange) {
				$existingRequiredDbChange = $this->getActiveDbChangeByCode($requiredDbChange->getCode());
				$requirement = new Requirement();
				$requirement->setMasterChange($dbChange);
				$requirement->setRequiredDbChange($existingRequiredDbChange);
				$this->entityManager->persist($requirement);
				$dbChange->addRequiredDbChange($requirement);
			}
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
				if (!$this->isEqualUsers($user, $existingUser)) {
					throw new OutOfSyncException(sprintf('User %s is out of sync.', $user->getName()));
				}
				$user = $existingUser;
			}
		}
		$this->entityManager->flush();
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

			$existingEnvironment = $this->getEnvironmentByCodeIfExists($environment->getCode());
			if ($existingEnvironment == null) {
				//Debug::dump($environment);
				$this->entityManager->persist($environment);
			} else {
				if (!$this->isEqualEnvironments($environment, $existingEnvironment)) {
					throw new OutOfSyncException(sprintf('Environment %s is out of sync.', $environment->getCode()));
				}
				$environment = $existingEnvironment;
			}
			$this->addEnvironment($environment);
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
				if (!$this->isEqualGroups($group, $existingGroup)) {
					throw new OutOfSyncException(sprintf('Group %s is out of sync.', $group->getName()));
				}
				$group = $existingGroup;
			}
			$this->addGroup($group);
		}
		$this->entityManager->flush();
	}

	/**
	 * @param \Kapcus\DbChanger\Entity\Environment $environment
	 * @param \Kapcus\DbChanger\Entity\DbChange $dbChange
	 *
	 * @param bool $activeOnly
	 *
	 * @return \Kapcus\DbChanger\Entity\Installation|null
	 */
	public function getInstallation(Environment $environment, DbChange $dbChange, $activeOnly = false)
	{
		$whereCondition = $this->getInstallationWhereCondition($dbChange, $environment->getId(), $activeOnly);

		return $this->entityManager->getRepository(Installation::class)->findOneBy($whereCondition);
	}

	/**
	 * @param \Kapcus\DbChanger\Entity\DbChange $dbChange
	 *
	 * @return bool
	 */
	public function hasDbChangePendingInstallation(DbChange $dbChange)
	{
		$whereCondition = $this->getInstallationWhereCondition($dbChange, null, true);

		return $this->entityManager->getRepository(Installation::class)->findOneBy($whereCondition) !== null;
	}

	public function getInstalledInstallation(Environment $environment, DbChange $dbChange)
	{

		$whereCondition = [
			'environment' => $environment->getId(),
			'dbChange' => $dbChange->getId(),
			'status' => Installation::STATUS_INSTALLED,
		];

		return $this->entityManager->getRepository(Installation::class)->findOneBy($whereCondition);
	}

	/**
	 * @param \Kapcus\DbChanger\Entity\Environment $environment
	 * @param \Kapcus\DbChanger\Entity\DbChange $dbChange
	 *
	 * @param bool $activeOnly
	 *
	 * @return \Kapcus\DbChanger\Entity\Installation[]
	 */
	public function getInstallations(Environment $environment, DbChange $dbChange, $activeOnly = false)
	{
		$whereCondition = $this->getInstallationWhereCondition($dbChange, $environment->getId(), $activeOnly);

		return $this->entityManager->getRepository(Installation::class)->findBy($whereCondition);
	}

	/**
	 * @param \Kapcus\DbChanger\Entity\Environment $environment
	 * @param string $dbChangeCode
	 *
	 * @return \Kapcus\DbChanger\Entity\Installation[]
	 */
	public function getInstallationsByDbChangeCode(Environment $environment, $dbChangeCode)
	{
		$query = $this->entityManager->createQuery(
			'select 
														  	i 
														  from 
															Kapcus\DbChanger\Entity\Installation i, 
															Kapcus\DbChanger\Entity\DbChange d,
															Kapcus\DbChanger\Entity\Environment e 
														  WHERE
															i.environment = e AND
															i.dbChange = d AND
															e.id = ?1 AND 
															d.id IN (SELECT dd.id FROM Kapcus\DbChanger\Entity\DbChange dd WHERE dd.code = ?2) ORDER BY d.id ASC, i.id ASC'
		);
		$query->setParameter(1, $environment->getId());
		$query->setParameter(2, $dbChangeCode);

		return $query->getResult();
	}

	/**
	 * @param \Kapcus\DbChanger\Entity\DbChange $dbChange
	 * @param string|null $environmentCode
	 * @param boolean $activeOnly
	 *
	 * @return array
	 */
	private function getInstallationWhereCondition(DbChange $dbChange, $environmentCode, $activeOnly)
	{
		$whereCondition = [
			'dbChange' => $dbChange->getId(),
		];

		if ($environmentCode !== null) {
			$whereCondition['environment'] = $environmentCode;
		}
		if ($activeOnly) {
			$whereCondition['status'] = InstalledFragment::getActiveStatuses();
		}

		return $whereCondition;
	}

	/**
	 * @param \Kapcus\DbChanger\Entity\Environment $environment
	 * @param \Kapcus\DbChanger\Entity\DbChange $dbChange
	 *
	 * @return \Kapcus\DbChanger\Entity\Installation
	 * @throws \Doctrine\ORM\OptimisticLockException
	 */
	public function prepareInstallation(Environment $environment, DbChange $dbChange)
	{
		$installation = $this->createNewInstallation($environment, $dbChange, self::DEFAULT_USER);
		foreach ($dbChange->getFragments() as $fragment) {
			foreach ($environment->getUserGroupsByGroup($fragment->getGroup()) as $userGroup) {
				$content = $this->generator->getFragmentContent($environment, $fragment, $userGroup);
				$installedFragment = new InstalledFragment();
				$installedFragment->setInstallation($installation);
				$installedFragment->setFragment($fragment);
				$installedFragment->setUserGroup($userGroup);
				$installedFragment->setContent($content);
				$installedFragment->setStatus(InstalledFragment::STATUS_NEW);
				$this->entityManager->persist($installedFragment);
			}
		}
		$this->entityManager->flush();

		return $installation;
	}

	public function installDbChange(Environment $environment, array $connectionConfigurations, DbChange $dbChange)
	{
		$installation = $this->getInstallation($environment, $dbChange, true);
		if ($installation == null) {
			$installation = $this->prepareInstallation($environment, $dbChange);
		}

		try {
			foreach ($dbChange->getRequiredDbChanges() as $requiredDbChange) {
				$reqDbChangeInstallation = $this->getInstalledInstallation($environment, $requiredDbChange->getRequiredDbChange());
				if ($reqDbChangeInstallation == null) {
					throw new InstallationException(
						sprintf(
							'Required DbChange %s is not installed. Install it first.',
							$requiredDbChange->getRequiredDbChange()->getCode()
						)
					);
				}
			}
			$fragmentsForInstallation = $this->getInstallationFragmentsByStatus(
				$installation,
				[InstalledFragment::STATUS_NEW, InstalledFragment::STATUS_PENDING]
			);
			foreach ($fragmentsForInstallation as $installationFragment) {
				if ($installationFragment->getFragment()->getGroup()->getIsManual()) {
					throw new InstallationException(
						sprintf(
							'!!!Installation interrupted!!! ' .
							'Manual fragment %s needs to be deployed manually. ' .
							'Use \'generate\' command to get sql to be executed. ' .
							'Then use \'mark\' command to mark fragment as installed and rerun \'install\' command. ',
							Util::getFragmentId($installationFragment->getId())
						)
					);
				}
				if ($installationFragment->getStatus() == InstalledFragment::STATUS_PENDING) {
					throw new InstallationException(
						sprintf(
							'!!!Installation interrupted!!! ' .
							'Pending fragment %s requires manual fix. ' .
							'Once fixed, use \'mark\' command and rerun \'install\' command. ',
							Util::getFragmentId($installationFragment->getId())
						)
					);
				}
				$userConnectionConfiguration = Util::getConnectionConfigurationByUserName(
					$connectionConfigurations,
					$installationFragment->getUserGroup()->getUser()->getName()
				);
				if ($userConnectionConfiguration == null) {
					throw new InstallationException(sprintf('Undefined user %s.', $installationFragment->getUserGroup()->getUser()->getName()));
				}
				$this->installFragment($userConnectionConfiguration, $installationFragment);
				$installation->setStatus(InstalledFragment::STATUS_PENDING);
			}
			$installation->setStatus(Installation::STATUS_INSTALLED);
		} catch (ConnectionException $e) {
			throw $e;
		} catch (ExecutionException $e) {
			$installation->setStatus(Installation::STATUS_PENDING);
			throw $e;
		} finally {
			$this->entityManager->flush();
		}
	}

	/**
	 * @param int[] $fragmentIds
	 * @param string $statusShortcut
	 *
	 * @throws \Doctrine\ORM\OptimisticLockException
	 * @throws \Kapcus\DbChanger\Model\Exception\DbChangeException
	 */
	private function markFragmentsByIds($fragmentIds, $statusShortcut)
	{
		$installationId = null;
		foreach($fragmentIds as $fragmentId) {
			$installationFragment = $this->getInstallationFragmentById($fragmentId);
			if ($installationFragment == null) {
				throw new DbChangeException(sprintf('Installation fragment with given id %s not found.', $fragmentId));
			}
			if ($installationId == null) {
				$installationId = $installationFragment->getInstallation()->getId();
			} elseif ($installationId !== $installationFragment->getInstallation()->getId()) {
				throw new DbChangeException(sprintf('Fragments belongs to different installations which is not allowed.', $fragmentId));
			}
			$this->markFragment($installationFragment, $statusShortcut, false);
		}
		$this->entityManager->flush();
	}

	/**
	 * @param int $fragmentId
	 * @param string $statusShortcut
	 *
	 * @throws \Doctrine\ORM\OptimisticLockException
	 * @throws \Kapcus\DbChanger\Model\Exception\DbChangeException
	 */
	public function markFragmentById($fragmentId, $statusShortcut)
	{
		$installationFragment = $this->getInstallationFragmentById($fragmentId);
		if ($installationFragment == null) {
			throw new DbChangeException(sprintf('Installation fragment with given id %s not found.', $fragmentId));
		}
		$this->markFragment($installationFragment, $statusShortcut);
	}

	/**
	 * @param \Kapcus\DbChanger\Entity\InstalledFragment $installationFragment
	 * @param string $statusShortcut
	 *
	 * @param bool $commit TRUE = commit will be called
	 *
	 * @throws \Doctrine\ORM\OptimisticLockException
	 * @throws \Kapcus\DbChanger\Model\Exception\DbChangeException
	 */
	public function markFragment(InstalledFragment $installationFragment, $statusShortcut, $commit = true)
	{
		$fragmentStatus = InstalledFragment::getStatusByShortcut($statusShortcut);
		if ($fragmentStatus == null) {
			throw new DbChangeException(
				sprintf('Invalid status shortcut \'%s\' given. Use one of: %s', $statusShortcut, InstalledFragment::getStatusNameString())
			);
		}

		$installationFragment->setStatus($fragmentStatus);
		if ($commit) {
			$this->entityManager->flush();
		}
	}

	/**
	 * @param \Kapcus\DbChanger\Model\ConnectionConfiguration $connectionConfiguration
	 * @param \Kapcus\DbChanger\Entity\InstalledFragment $installationFragment
	 *
	 * @throws \Exception
	 * @throws \Kapcus\DbChanger\Model\Exception\ConnectionException
	 * @throws \Kapcus\DbChanger\Model\Exception\ExecutionException
	 */
	private function installFragment(
		ConnectionConfiguration $connectionConfiguration,
		InstalledFragment $installationFragment
	) {
		$resultMessage = 'OK';
		try {
			$this->executor->setupConnection($connectionConfiguration);
			$this->executor->executeContent($installationFragment->getContent());
			$installationFragment->setStatus(InstalledFragment::STATUS_INSTALLED);
		} catch (ConnectionException $e) {
			$resultMessage = sprintf('%s : %s', $e->getMessage(), $e->getPrevious()->getMessage());
			throw $e;
		} catch (ExecutionException $e) {
			$installationFragment->setStatus(InstalledFragment::STATUS_PENDING);
			$resultMessage = sprintf('%s : %s', $e->getMessage(), $e->getPrevious()->getMessage());
			throw $e;
			// should NEVER happen but for sure
		} catch (\Exception $e) {
			$resultMessage = sprintf('%s : %s', $e->getMessage());
			throw $e;
		} finally {
			$this->createInstallationLog($installationFragment, $resultMessage);
		}
	}

	/**
	 * @return \Kapcus\DbChanger\Entity\Group[]
	 */
	public function getGroups()
	{
		return $this->entityManager->getRepository(Group::class)->findAll();
	}

	/**
	 * @param string $environmentCode
	 *
	 * @return \Kapcus\DbChanger\Entity\Environment
	 * @throws \Kapcus\DbChanger\Model\Exception\EnvironmentException
	 */
	public function getEnvironmentByCode($environmentCode)
	{
		$result = $this->getEnvironmentByCodeIfExists($environmentCode);
		if ($result == null) {
			throw new EnvironmentException(
				sprintf(
					'Unknown environment code %1$s, ensure this environment is defined in your configuration and properly initialized.',
					$environmentCode
				)
			);
		}

		return $result;
	}

	/**
	 * @param \Kapcus\DbChanger\Entity\Installation $installation
	 * @param int[] $statuses
	 *
	 * @return \Kapcus\DbChanger\Entity\InstalledFragment[]
	 */
	public function getInstallationFragmentsByStatus(Installation $installation, array $statuses)
	{
		return $this->entityManager->getRepository(InstalledFragment::class)->findBy(
			[
				'installation' => $installation->getId(),
				'status' => $statuses,
			],
			[
				'id' => 'ASC',
			]
		);
	}

	/**
	 * @param \Kapcus\DbChanger\Entity\Installation $installation
	 *
	 * @return \Kapcus\DbChanger\Entity\InstalledFragment[]
	 */
	public function getInstallationFragments(Installation $installation)
	{
		return $this->entityManager->getRepository(InstalledFragment::class)->findBy(
			[
				'installation' => $installation->getId(),
			],
			[
				'id' => 'ASC',
			]
		);
	}

	/**
	 * @param int $id
	 *
	 * @return null|\Kapcus\DbChanger\Entity\InstalledFragment
	 */
	public function getInstallationFragmentById($id)
	{
		return $this->entityManager->getRepository(InstalledFragment::class)->find($id);
	}

	/**
	 * @param int $id
	 *
	 * @return null|\Kapcus\DbChanger\Entity\InstallationLog
	 */
	public function getInstallationLogById($id)
	{
		return $this->entityManager->getRepository(InstallationLog::class)->find($id);
	}

	/**
	 * @param int $id
	 *
	 * @return null|\Kapcus\DbChanger\Entity\InstalledFragment
	 */
	public function getInstallationById($id)
	{
		return $this->entityManager->getRepository(Installation::class)->find($id);
	}

	/**
	 * @param string $environmentCode
	 *
	 * @return null|\Kapcus\DbChanger\Entity\Environment
	 */
	public function getEnvironmentByCodeIfExists($environmentCode)
	{
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
	private function isEqualUsers(User $user, User $secondUser)
	{
		if ($user->getName() != $secondUser->getName()) {
			return false;
		}

		if ($user->getEnvironment() != $secondUser->getEnvironment()) {
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
	private function isEqualGroups(Group $group, Group $secondGroup)
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
	private function isEqualEnvironments(Environment $environment, Environment $existingEnvironment)
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

	/**
	 * @param \Kapcus\DbChanger\Entity\DbChange $dbChange
	 * @param \Kapcus\DbChanger\Entity\DbChange $secondDbChange
	 *
	 * @return bool
	 */
	private function isEqualDbChanges(DbChange $dbChange, DbChange $secondDbChange)
	{
		if ($dbChange->getCode() != $secondDbChange->getCode()) {
			return false;
		}

		if ($dbChange->getDescription() != $secondDbChange->getDescription()) {
			return false;
		}

		return true;
	}

	/**
	 * @param string $dbChangeCode
	 *
	 * @return \Kapcus\DbChanger\Entity\DbChange
	 * @throws \Kapcus\DbChanger\Model\Exception\DbChangeException
	 */
	public function getActiveDbChangeByCode($dbChangeCode)
	{
		$result = $this->getActiveDbChangeByCodeIfExists($dbChangeCode);
		if ($result == null) {
			throw new DbChangeException(sprintf('DbChange with code %1$s has not been registered yet..', $dbChangeCode));
		}

		return $result;
	}

	/**
	 * @param string $dbChangeCode
	 *
	 * @return null|\Kapcus\DbChanger\Entity\DbChange
	 */
	public function getActiveDbChangeByCodeIfExists($dbChangeCode)
	{
		return $this->entityManager->getRepository(DbChange::class)->findOneBy(['code' => $dbChangeCode, 'isActive' => 1]);
	}

	/**
	 * @param \Kapcus\DbChanger\Entity\Environment $environment
	 * @param \Kapcus\DbChanger\Entity\DbChange $dbChange
	 * @param string $createdBy
	 *
	 * @return \Kapcus\DbChanger\Entity\Installation
	 * @throws \Doctrine\ORM\OptimisticLockException
	 */
	public function createNewInstallation(Environment $environment, DbChange $dbChange, $createdBy)
	{
		$installation = new Installation();
		$installation->setCreatedBy($createdBy);
		$installation->setEnvironment($environment);
		$installation->setDbChange($dbChange);
		$installation->setStatus(Installation::STATUS_NEW);
		$this->entityManager->persist($installation);
		$this->entityManager->flush();

		return $installation;
	}

	public function createInstallationLog(InstalledFragment $installedFragment, $resultMessage)
	{
		$log = new InstallationLog();
		$log->createFromFragment($installedFragment);
		$log->setResultMessage($resultMessage);
		$log->setCreatedBy(self::DEFAULT_USER);
		$this->entityManager->persist($log);
	}

	public function checkTables()
	{
		$schemaManager = $this->entityManager->getConnection()->getSchemaManager();
		//TODO check also sequences
		if (!$schemaManager->tablesExist(
				[
					$this->entityManager->getClassMetadata(DbChange::class)->getTableName(),
					$this->entityManager->getClassMetadata(Requirement::class)->getTableName(),
					$this->entityManager->getClassMetadata(Environment::class)->getTableName(),
					$this->entityManager->getClassMetadata(Fragment::class)->getTableName(),
					$this->entityManager->getClassMetadata(Group::class)->getTableName(),
					$this->entityManager->getClassMetadata(Installation::class)->getTableName(),
					$this->entityManager->getClassMetadata(InstallationLog::class)->getTableName(),
					$this->entityManager->getClassMetadata(InstalledFragment::class)->getTableName(),
					$this->entityManager->getClassMetadata(Placeholder::class)->getTableName(),
					$this->entityManager->getClassMetadata(User::class)->getTableName(),
					$this->entityManager->getClassMetadata(UserGroup::class)->getTableName(),

				]
			) == true) {
			throw new DbChangeException(
				'Unable to check all database objects. Ensure all tables are properly created in DbChanger schema and database configuration is valid.'
			);
		}
	}

	/**
	 * @param string $target
	 * @param string $statusShortcut
	 *
	 * @throws \Doctrine\ORM\OptimisticLockException
	 * @throws \Kapcus\DbChanger\Model\Exception\DbChangeException
	 */
	public function markTarget($target, $statusShortcut)
	{
		if (($fragmentId = Util::parseFragmentId($target)) !== null) {
			$this->markFragmentById($fragmentId, $statusShortcut);
		} elseif (($fragmentIds = Util::parseFragmentRange($target)) !== null) {
			$this->markFragmentsByIds($fragmentIds, $statusShortcut);
		} elseif (($installationId = Util::parseInstallationId($target)) !== null) {
			$this->markInstallationById($installationId, $statusShortcut);
		} else {
			throw new DbChangeException(sprintf('Unsupported target for mark, only fragment id, fragment range or installation id is supported.'));
		}
	}

	/**
	 * @param string $fragmentInputId
	 *
	 * @return \Kapcus\DbChanger\Entity\InstalledFragment|null
	 * @throws \Kapcus\DbChanger\Model\Exception\DbChangeException
	 */
	public function getInstallationFragment($fragmentInputId) {
		$fragmentId = Util::parseFragmentId($fragmentInputId);

		if ($fragmentId == null) {
			throw new DbChangeException(sprintf('Installation fragment id is not valid.', $fragmentInputId));
		}

		$installationFragment = $this->getInstallationFragmentById($fragmentId);
		if ($installationFragment == null) {
			throw new DbChangeException(sprintf('Installation fragment with given id %s not found.', $fragmentId));
		}
		return $installationFragment;
	}

	/**
	 * @param string $fragmentInputId
	 *
	 * @return \Kapcus\DbChanger\Model\Reporting\Table
	 * @throws \Kapcus\DbChanger\Model\Exception\DbChangeException
	 */
	public function getInstallationFragmentLogReport($fragmentInputId)
	{
		$installationFragment = $this->getInstallationFragment($fragmentInputId);

		$logs = $installationFragment->getLogs();

		$table = new Table();
		$table->addColumn(new Column('ID', Column::TYPE_STRING, 8));
		$table->addColumn(new Column('INST. STATUS', Column::TYPE_STRING, 12));
		$table->addColumn(new Column('FRAG. STATUS', Column::TYPE_STRING, 12));
		$table->addColumn(new Column('CONTENT', Column::TYPE_STRING, 50));
		$table->addColumn(new Column('RESULT', Column::TYPE_STRING, 50));

		foreach ($logs as $log) {
			$table->addRow();
			$table->addField(Util::getLogId($log->getId()));
			$table->addField(Installation::getStatusName($log->getInstallationStatus()));
			$table->addField(InstalledFragment::getStatusName($log->getInstalledFragmentStatus()));
			$table->addField(substr($log->getContent(), 0, 50));
			$table->addField(substr($log->getResultMessage(), 0, 50));
		}

		return $table;
	}

	/**
	 * @param \Kapcus\DbChanger\Entity\Environment $environment
	 * @param string $dbChangeCode
	 *
	 * @return array
	 */
	public function getDbChangeReport(Environment $environment, $dbChangeCode)
	{
		$output = [];
		$output['messages'] = [];
		$installationDetails = [];
		$installations = $this->getInstallationsByDbChangeCode($environment, $dbChangeCode);

		$table = new Table();
		$table->addColumn(new Column('ID', Column::TYPE_STRING, 8));
		$table->addColumn(new Column('VERSION', Column::TYPE_STRING, 6));
		$table->addColumn(new Column('STATUS', Column::TYPE_STRING, 15));
		$table->addColumn(new Column('CREATED AT', Column::TYPE_STRING, 10));

		foreach ($installations as $installation) {
			$table->addRow();
			$table->addField(Util::getInstallationId($installation->getId()));
			$table->addField($installation->getDbChange()->getVersionNumber());
			$table->addField(Installation::getStatusName($installation->getStatus()));
			$table->addField($installation->getCreatedAt());
			$installationDetails[] = $installation;
		}
		$output['installations'] = $table;
		$output['details'] = [];

		foreach ($installationDetails as $installation) {
			$installationFragments = $this->getInstallationFragments($installation);

			$table = new Table();
			$table->addColumn(new Column('ID', Column::TYPE_STRING, 8));
			$table->addColumn(new Column('INDEX', Column::TYPE_STRING, 6));
			$table->addColumn(new Column('STATUS', Column::TYPE_STRING, 15));
			$table->addColumn(new Column('MANUAL', Column::TYPE_STRING, 6));
			$table->addColumn(new Column('GROUP', Column::TYPE_STRING, 20));
			$table->addColumn(new Column('USER', Column::TYPE_STRING, 20));

			$key = '';
			foreach ($installationFragments as $installationFragment) {
				$key = $installationFragment->getInstallation()->getId();
				$table->addRow();
				$table->addField(Util::getFragmentId($installationFragment->getId()));
				$table->addField(Util::getFragmentIndex($installationFragment->getFragment()->getIndex()));
				$table->addField(InstalledFragment::getStatusName($installationFragment->getStatus()));
				$table->addField($installationFragment->getFragment()->getGroup()->getIsManual() ? 'YES' : 'no');
				$table->addField($installationFragment->getUserGroup()->getGroup()->getName());
				$table->addField($installationFragment->getUserGroup()->getUser()->getName());
			}
			$output['details'][$key] = $table;
		}

		return $output;
	}

	/**
	 * @param int $installationId
	 * @param string $statusShortcut
	 *
	 * @throws \Doctrine\ORM\OptimisticLockException
	 * @throws \Kapcus\DbChanger\Model\Exception\DbChangeException
	 */
	private function markInstallationById($installationId, $statusShortcut)
	{
		$installation = $this->getInstallationById($installationId);
		if ($installation == null) {
			throw new DbChangeException(sprintf('Installation with given id %s not found.', $installationId));
		}
		$installationStatus = Installation::getStatusByShortcut($statusShortcut);
		if ($installationStatus == null) {
			throw new DbChangeException(
				sprintf('Invalid status shortcut \'%s\' given. Use one of: %s', $statusShortcut, Installation::getStatusNameString())
			);
		}

		$installation->setStatus($installationStatus);
		$this->entityManager->flush();


	}

	/**
	 * @param string $logInputId
	 *
	 * @return \Kapcus\DbChanger\Entity\InstallationLog|null
	 * @throws \Kapcus\DbChanger\Model\Exception\DbChangeException
	 */
	public function getInstallationLog($logInputId) {
		$logId = Util::parseLogId($logInputId);

		if ($logId == null) {
			throw new DbChangeException(sprintf('Installation log id is not valid.', $logInputId));
		}

		$installationLog = $this->getInstallationLogById($logId);
		if ($installationLog == null) {
			throw new DbChangeException(sprintf('Installation log with given id %s not found.', $logId));
		}
		return $installationLog;
	}

	/**
	 * @param string $target
	 *
	 * @return string[]
	 * @throws \Kapcus\DbChanger\Model\Exception\DbChangeException
	 */
	public function displayDetail($target)
	{
		if (Util::parseFragmentId($target) !== null) {
			$installationFragment = $this->getInstallationFragment($target);
			return ['CONTENT' => $installationFragment->getContent()];

		} elseif (Util::parseLogId($target) !== null) {
			$log = $this->getInstallationLog($target);
			return ['CONTENT' => $log->getContent(), 'RESULT' => $log->getResultMessage()];
		} else {
			throw new DbChangeException(sprintf('Unsupported target for display, only fragment id or log id is supported.'));
		}
	}
}