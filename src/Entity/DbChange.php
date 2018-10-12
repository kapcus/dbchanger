<?php

namespace Kapcus\DbChanger\Entity;

use Doctrine\ORM\Mapping AS ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity
 * @ORM\Table(name="DBCH_DBCHANGE")
 */
class DbChange
{
	/**
	 * @ORM\Id
	 * @ORM\Column(type="integer")
	 * @ORM\GeneratedValue(strategy="SEQUENCE")
	 * @ORM\SequenceGenerator(sequenceName="DBCH_DBCH_SEQ")
	 *
	 * @var int
	 */
	protected $id;

	/**
	 *
	 * @ORM\Column(type="string")
	 *
	 * @var string
	 */
	protected $code;

	/**
	 *
	 * @var \DateTime
	 */
	protected $registeredAt;

	/**
	 * @ORM\Column(type="integer", name="is_active")
	 *
	 * @var boolean
	 */
	protected $isActive = true;

	/**
	 * @ORM\Column(type="integer", name="version_number")
	 *
	 * @var int
	 */
	protected $versionNumber = 1;

	/**
	 *
	 * @ORM\Column(type="string", nullable=true)
	 *
	 * @var string
	 */
	protected $description;

	/**
	 * @ORM\OneToMany(targetEntity="Fragment", mappedBy="dbChange", cascade={"persist", "remove"})
	 * @ORM\OrderBy({"id" = "ASC"})
	 *
	 * @var \Kapcus\DbChanger\Entity\Fragment[]
	 */
	protected $fragments;

	/**
	 * @ORM\OneToMany(targetEntity="Installation", mappedBy="dbChange", cascade={"persist", "remove"})
	 *
	 * @var \Kapcus\DbChanger\Entity\Installation[]
	 */
	private $installations = [];

	/**
	 * @ORM\OneToMany(targetEntity="Requirement", mappedBy="masterChange", cascade={"persist", "remove"})
	 *
	 * @var \Kapcus\DbChanger\Entity\Requirement[]
	 */
	private $requiredDbChanges = [];

	/**
	 * @ORM\OneToMany(targetEntity="Requirement", mappedBy="requiredDbChange", cascade={"persist", "remove"})
	 *
	 * @var \Kapcus\DbChanger\Entity\Requirement[]
	 */
	private $dependentDbChanges = [];

	/**
	 * @var \Kapcus\DbChanger\Entity\DbChange[]
	 */
	private $reqDbChanges = [];

	/**
	 * DbChange constructor.
	 */
	public function __construct()
	{
		$this->fragments = new ArrayCollection();
		$this->installations = new ArrayCollection();
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
	 * @return \Kapcus\DbChanger\Entity\Fragment[]
	 */
	public function getFragments()
	{
		return $this->fragments;
	}

	/**
	 * @param int $index
	 *
	 * @return \Kapcus\DbChanger\Entity\Fragment|null
	 */
	public function getFragmentByIndex($index)
	{
		foreach ($this->getFragments() as $fragment) {
			if ($fragment->getIndex() == $index) {
				return $fragment;
			}
		}

		return null;
	}

	/**
	 * @param \Kapcus\DbChanger\Entity\Fragment $fragment
	 */
	public function addFragment(Fragment $fragment)
	{
		$this->fragments[] = $fragment;
	}

	public function hasFragment() {
		return count($this->getFragments()) > 0;
	}

	public function loadFragmentTemplateContent()
	{
		foreach ($this->getFragments() as $fragment) {
			$fragment->loadTemplateContentFromFile();
		}
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
	 * @return \Kapcus\DbChanger\Entity\Requirement[]
	 */
	public function getRequiredDbChanges()
	{
		return $this->requiredDbChanges;
	}

	/**
	 * @param \Kapcus\DbChanger\Entity\Requirement $requiredDbChange
	 */
	public function addRequiredDbChange(Requirement $requiredDbChange)
	{
		$this->requiredDbChanges[] = $requiredDbChange;
	}

	/**
	 * @return \Kapcus\DbChanger\Entity\Requirement[]
	 */
	public function getDependentDbChanges()
	{
		return $this->dependentDbChanges;
	}

	/**
	 * @param \Kapcus\DbChanger\Entity\Requirement $dependentDbChange
	 */
	public function addDependentDbChanges(Requirement $dependentDbChange)
	{
		$this->dependentDbChanges[] = $dependentDbChange;
	}

	/**
	 * @return \Kapcus\DbChanger\Entity\DbChange[]
	 */
	public function getReqDbChanges()
	{
		return $this->reqDbChanges;
	}

	/**
	 * @param \Kapcus\DbChanger\Entity\DbChange $reqDbChange
	 */
	public function addReqDbChanges(DbChange $reqDbChange)
	{
		$this->reqDbChanges[] = $reqDbChange;
	}

	/**
	 * @return \DateTime
	 */
	public function getRegisteredAt()
	{
		return $this->registeredAt;
	}

	/**
	 * @param \DateTime $registeredAt
	 */
	public function setRegisteredAt($registeredAt)
	{
		$this->registeredAt = $registeredAt;
	}

	/**
	 * @return boolean
	 */
	public function isActive()
	{
		return $this->isActive == 1 ? true : false;
	}

	/**
	 * @param boolean $isActive
	 */
	public function setIsActive($isActive)
	{
		$this->isActive = $isActive ? 1 : 0;
	}

	/**
	 * @return int
	 */
	public function getVersionNumber()
	{
		return $this->versionNumber;
	}

	/**
	 * @param int $versionNumber
	 */
	public function setVersionNumber($versionNumber)
	{
		$this->versionNumber = $versionNumber;
	}



}