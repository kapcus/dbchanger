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
	 * @ORM\OneToMany(targetEntity="UserGroup", mappedBy="user")
	 *
	 * @var \Kapcus\DbChanger\Entity\UserGroup[]
	 */
	protected $assignedEnvironmentGroups;

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



}