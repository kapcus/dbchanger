<?php

namespace Kapcus\DbChanger\Entity;

use Doctrine\ORM\Mapping AS ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity
 * @ORM\Table(name="DBCH_USER")
 */
class User
{
	/**
	 * @ORM\Id
	 * @ORM\Column(type="integer")
	 * @ORM\GeneratedValue(strategy="SEQUENCE")
	 * @ORM\SequenceGenerator(sequenceName="DBCH_USER_SEQ")
	 *
	 * @var int
	 */
	protected $id;

	/**
	 * @ORM\Column(type="string")
	 */
	protected $name;

	/**
	 * @ORM\ManyToOne(targetEntity="Environment", inversedBy="users")
	 *
	 * @var \Kapcus\DbChanger\Entity\Environment
	 **/
	protected $environment;

	/**
	 * @ORM\OneToMany(targetEntity="UserGroup", mappedBy="user")
	 *
	 * @var \Kapcus\DbChanger\Entity\UserGroup[]
	 */
	protected $assignedEnvironmentGroups;

	/**
	 * @var string
	 */
	protected $password;

	/**
	 * User constructor.
	 */
	public function __construct()
	{
		$this->assignedEnvironmentGroups = new ArrayCollection();
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
	 * @param \Kapcus\DbChanger\Entity\UserGroup $assignedEnvironmentGroup
	 */
	public function assignedToEnvironmentGroup(UserGroup $assignedEnvironmentGroup)
	{
		$this->assignedEnvironmentGroups[] = $assignedEnvironmentGroup;
	}

	/**
	 * @return string
	 */
	public function getPassword()
	{
		return $this->password;
	}

	/**
	 * @param string $password
	 */
	public function setPassword($password)
	{
		$this->password = $password;
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