<?php

namespace Kapcus\DbChanger\Entity;

use Doctrine\ORM\Mapping AS ORM;
use Doctrine\Common\Collections\ArrayCollection;

use Kapcus\DbChanger\Model\Placeholder;

/**
 * @ORM\Entity
 * @ORM\Table(name="DBCH_ENVIRONMENT")
 */
class Environment
{
	/**
	 * @ORM\Id
	 * @ORM\Column(type="integer")
	 * @ORM\GeneratedValue(strategy="SEQUENCE")
	 * @ORM\SequenceGenerator(sequenceName="DBCH_ENV_SEQ")
	 *
	 * @var int
	 */
	protected $id;

	/**
	 *
	 * @ORM\Column(type="string", nullable=true)
	 *
	 * @var string
	 */
	protected $name;

	/**
	 *
	 * @ORM\Column(type="string")
	 *
	 * @var string
	 */
	protected $code;

	/**
	 *
	 * @ORM\Column(type="string", nullable=true)
	 *
	 * @var string
	 */
	protected $description;

	/**
	 * @ORM\OneToMany(targetEntity="Placeholder", mappedBy="environment", cascade={"persist", "remove"})
	 *
	 * @var \Kapcus\DbChanger\Entity\Placeholder[]
	 */
	private $placeholders = [];

	/**
	 * @ORM\OneToMany(targetEntity="User", mappedBy="environment", cascade={"persist", "remove"})
	 *
	 * @var \Kapcus\DbChanger\Entity\User[]
	 */
	private $users = [];

	/**
	 * @ORM\OneToMany(targetEntity="Installation", mappedBy="environment", cascade={"persist", "remove"})
	 *
	 * @var \Kapcus\DbChanger\Entity\Installation[]
	 */
	private $installations = [];

	/**
	 * @ORM\OneToMany(targetEntity="UserGroup", mappedBy="environment", cascade={"persist", "remove"})
	 *
	 * @var \Kapcus\DbChanger\Entity\UserGroup[]
	 */
	protected $userGroups = [];

	/**
	 * @var string
	 */
	private $host;

	/**
	 * @var int
	 */
	private $port;

	/**
	 * @var string
	 */
	private $databaseName;

	/**
	 * Environment constructor.
	 */
	public function __construct()
	{
		$this->userGroups = new ArrayCollection();
		$this->userGroups = new ArrayCollection();
		$this->installations = new ArrayCollection();
		$this->placeholders = new ArrayCollection();
		$this->users = new ArrayCollection();
	}

	/**
	 * @return int
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * @param string $name
	 */
	public function setName($name)
	{
		$this->name = $name;
	}

	/**
	 * @return string
	 */
	public function getCode()
	{
		return $this->code;
	}

	/**
	 * @param string $code
	 */
	public function setCode($code)
	{
		$this->code = $code;
	}

	/**
	 * @return \Kapcus\DbChanger\Entity\Placeholder[]
	 */
	public function getPlaceholders()
	{
		return $this->placeholders;
	}

	/**
	 * @param \Kapcus\DbChanger\Entity\Placeholder $placeholder
	 */
	public function addPlaceholder(\Kapcus\DbChanger\Entity\Placeholder $placeholder)
	{
		$this->placeholders[] = $placeholder;
	}

	public function getPlaceholderCodes()
	{
		return array_map(
			function (\Kapcus\DbChanger\Entity\Placeholder $o) {
				return $o->getCode();
			},
			$this->getPlaceholders()
		);
	}

	public function getPlaceholderValues()
	{
		return array_map(
			function (\Kapcus\DbChanger\Entity\Placeholder $o) {
				return $o->getTranslatedValue();
			},
			$this->getPlaceholders()
		);
	}

	/**
	 * @return string
	 */
	public function getDescription()
	{
		return $this->description;
	}

	/**
	 * @param string $description
	 */
	public function setDescription($description)
	{
		$this->description = $description;
	}

	/**
	 * @param \Kapcus\DbChanger\Entity\UserGroup $userGroup
	 */
	public function addUserGroup(UserGroup $userGroup)
	{
		$this->userGroups[] = $userGroup;
	}

	/**
	 * @return \Kapcus\DbChanger\Entity\UserGroup[]
	 */
	public function getUserGroups()
	{
		return $this->userGroups;
	}

	/**
	 * @param string $groupName
	 * @param string $userName
	 *
	 * @return \Kapcus\DbChanger\Entity\UserGroup|null
	 */
	public function getUserGroup($groupName, $userName) {
		foreach($this->getUserGroups() as $userGroup) {
			if ($userGroup->getGroup()->getName() == $groupName && $userGroup->getUser()->getName() == $userName) {
				return $userGroup;
			}
		}
		return null;
	}

	/**
	 * @return string[]
	 */
	public function getGroupNames() {
		$groupNames = [];
		foreach($this->getUserGroups() as $userGroup) {
			$groupNames[] = $userGroup->getGroup()->getName();
		}
		return array_unique($groupNames);
	}

	/**
	 * @param \Kapcus\DbChanger\Entity\Group $group
	 *
	 * @return \Kapcus\DbChanger\Entity\UserGroup[]
	 */
	public function getUserGroupsByGroup(Group $group) {
		$groups = [];
		foreach($this->getUserGroups() as $userGroup) {
			if ($userGroup->getGroup()->getName() == $group->getName()) {
				$groups[] = $userGroup;
			}
		}
		return $groups;
	}

	/**
	 * @return \Kapcus\DbChanger\Entity\Installation[]
	 */
	public function getInstallations()
	{
		return $this->installations;
	}

	/**
	 * @param \Kapcus\DbChanger\Entity\Installation $installation
	 */
	public function addInstallation(Installation $installation)
	{
		$this->installations[] = $installation;
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
	 * @param string $userName
	 *
	 * @return \Kapcus\DbChanger\Entity\User
	 */
	public function getUserByName($userName) {
		foreach($this->getUsers() as $user) {
			if ($user->getName() == $userName) {
				return $user;
			}
		}
		return null;
	}

	/**
	 * @return string
	 */
	public function getHost()
	{
		return $this->host;
	}

	/**
	 * @param string $host
	 */
	public function setHost($host)
	{
		$this->host = $host;
	}

	/**
	 * @return int
	 */
	public function getPort()
	{
		return $this->port;
	}

	/**
	 * @param int $port
	 */
	public function setPort($port)
	{
		$this->port = $port;
	}

	/**
	 * @return string
	 */
	public function getDatabaseName()
	{
		return $this->databaseName;
	}

	/**
	 * @param string $databaseName
	 */
	public function setDatabaseName($databaseName)
	{
		$this->databaseName = $databaseName;
	}


}