<?php

namespace Kapcus\DbChanger\Entity;

use Doctrine\ORM\Mapping AS ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity
 * @ORM\Table(name="DBCH_INSTALLATION")
 */
class Installation
{
	/** @ORM\Id
	 * @ORM\Column(type="integer")
	 * @ORM\GeneratedValue(strategy="SEQUENCE")
	 * @ORM\SequenceGenerator(sequenceName="DBCH_INST_SEQ")
	 *
	 * @var int
	 */
	protected $id;

	/**
	 * @ORM\Column(type="string", name="created_by")
	 * @var string
	 */
	protected $createdBy;

	/**
	 * @ORM\OneToMany(targetEntity="InstalledFragment", mappedBy="installation")
	 *
	 * @var \Kapcus\DbChanger\Entity\InstalledFragment[]
	 */
	protected $installedFragments;

	/**
	 * @ORM\ManyToOne(targetEntity="Environment", inversedBy="installations")
	 *
	 * @var \Kapcus\DbChanger\Entity\Environment
	 **/
	protected $environment;

	/**
	 * @ORM\ManyToOne(targetEntity="DbChange", inversedBy="installations")
	 *
	 * @var \Kapcus\DbChanger\Entity\DbChange
	 **/
	protected $dbChange;

	/**
	 *
	 * @ORM\Column(type="integer")
	 *
	 * @var int
	 */
	protected $status;

	/**
	 * Installation constructor.
	 */
	public function __construct()
	{
		$this->installedFragments = new ArrayCollection();
	}

	/**
	 * @return int
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * @param int $id
	 */
	public function setId($id)
	{
		$this->id = $id;
	}

	/**
	 * @return string
	 */
	public function getCreatedBy()
	{
		return $this->createdBy;
	}

	/**
	 * @param string $createdBy
	 */
	public function setCreatedBy($createdBy)
	{
		$this->createdBy = $createdBy;
	}

	/**
	 * @return \Kapcus\DbChanger\Entity\InstalledFragment[]
	 */
	public function getInstalledFragments()
	{
		return $this->installedFragments;
	}

	/**
	 * @param \Kapcus\DbChanger\Entity\InstalledFragment $installedFragment
	 */
	public function addInstalledFragments($installedFragment)
	{
		$this->installedFragments[] = $installedFragment;
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
	public function setEnvironment(Environment $environment)
	{
		$this->environment = $environment;
	}

	/**
	 * @return \Kapcus\DbChanger\Entity\DbChange
	 */
	public function getDbChange()
	{
		return $this->dbChange;
	}

	/**
	 * @param \Kapcus\DbChanger\Entity\DbChange $dbChange
	 */
	public function setDbChange(DbChange $dbChange)
	{
		$this->dbChange = $dbChange;
	}

	/**
	 * @return int
	 */
	public function getStatus()
	{
		return $this->status;
	}

	/**
	 * @param int $status
	 */
	public function setStatus($status)
	{
		$this->status = $status;
	}



}