<?php

namespace Kapcus\DbChanger\Entity;

use Doctrine\ORM\Mapping AS ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity
 * @ORM\Table(name="DBCH_GROUP")
 */
class Group
{
	/** @ORM\Id
	 * @ORM\Column(type="integer")
	 * @ORM\GeneratedValue(strategy="SEQUENCE")
	 * @ORM\SequenceGenerator(sequenceName="DBCH_G_SEQ")
	 *
	 * @var int
	 */
	protected $id;

	/**
	 * @ORM\Column(type="string")
	 * @var string
	 */
	protected $name;

	/**
	 * @ORM\Column(type="integer", name="is_manual")
	 *
	 * @var int
	 */
	protected $isManual;

	/**
	 * @ORM\OneToMany(targetEntity="UserGroup", mappedBy="group")
	 *
	 * @var \Kapcus\DbChanger\Entity\UserGroup[]
	 */
	protected $assignedEnvironmentUsers;

	/**
	 * Group constructor.
	 */
	public function __construct()
	{
		$this->assignedEnvironmentUsers = new ArrayCollection();
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
	 * @return int
	 */
	public function getIsManual()
	{
		return $this->isManual;
	}

	/**
	 * @param int $isManual
	 */
	public function setIsManual($isManual)
	{
		$this->isManual = $isManual;
	}

	/**
	 * @param \Kapcus\DbChanger\Entity\UserGroup $assignedEnvironmentUser
	 */
	public function assignedToEnvironmentUser(UserGroup $assignedEnvironmentUser)
	{
		$this->assignedEnvironmentUsers[] = $assignedEnvironmentUser;
	}

}