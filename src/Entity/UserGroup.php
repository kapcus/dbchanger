<?php

namespace Kapcus\DbChanger\Entity;

use Doctrine\ORM\Mapping AS ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="DBCH_USERGROUP")
 */
class UserGroup
{
	/**
	 * @ORM\Id
	 * @ORM\Column(type="integer")
	 * @ORM\GeneratedValue(strategy="SEQUENCE")
	 * @ORM\SequenceGenerator(sequenceName="DBCH_UG_SEQ")
	 *
	 * @var int
	 */
	protected $id;

	/**
	 * @ORM\ManyToOne(targetEntity="User", inversedBy="assignedEnvironmentGroups", cascade={"persist", "remove"})
	 *
	 * @var \Kapcus\DbChanger\Entity\User
	 **/
	protected $user;

	/**
	 * @ORM\ManyToOne(targetEntity="Group", inversedBy="assignedEnvironmentUsers", cascade={"persist", "remove"})
	 *
	 * @var \Kapcus\DbChanger\Entity\Group
	 **/
	protected $group;

	/**
	 * @ORM\ManyToOne(targetEntity="Environment", inversedBy="userGroups")
	 *
	 * @var \Kapcus\DbChanger\Entity\Environment
	 **/
	protected $environment;

	/**
	 * @ORM\OneToMany(targetEntity="InstalledFragment", mappedBy="userGroup")
	 *
	 * @var \Kapcus\DbChanger\Entity\InstalledFragment[]
	 */
	protected $installedFragments;

	/**
	 * @return int
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * @return \Kapcus\DbChanger\Entity\User
	 */
	public function getUser()
	{
		return $this->user;
	}

	/**
	 * @param \Kapcus\DbChanger\Entity\User $user
	 */
	public function setUser($user)
	{
		$this->user = $user;
	}

	/**
	 * @return \Kapcus\DbChanger\Entity\Group
	 */
	public function getGroup()
	{
		return $this->group;
	}

	/**
	 * @param \Kapcus\DbChanger\Entity\Group $group
	 */
	public function setGroup($group)
	{
		$this->group = $group;
	}

	/**
	 * @return \Kapcus\DbChanger\Entity\Environment
	 */
	public function getEnvironment()
	{
		return $this->environment;
	}

	/**
	 * @param \Kapcus\DbChanger\Entity\Environment $environment
	 */
	public function setEnvironment($environment)
	{
		$this->environment = $environment;
	}


}