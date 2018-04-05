<?php

namespace Kapcus\DbChanger\Entity;

use Doctrine\ORM\Mapping AS ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="DBCH_REQUIREMENT")
 */
class Requirement
{
	/** @ORM\Id
	 * @ORM\Column(type="integer")
	 * @ORM\GeneratedValue(strategy="SEQUENCE")
	 * @ORM\SequenceGenerator(sequenceName="DBCH_REQ_SEQ")
	 *
	 * @var int
	 */
	protected $id;

	/**
	 * @ORM\ManyToOne(targetEntity="DbChange", inversedBy="requiredDbChanges", cascade={"persist", "remove"})
	 * @ORM\JoinColumn(name="dbchange_id", referencedColumnName="id")
	 *
	 * @var \Kapcus\DbChanger\Entity\DbChange
	 **/
	protected $masterChange;

	/**
	 * @ORM\ManyToOne(targetEntity="DbChange", inversedBy="dependentDbChanges", cascade={"persist", "remove"})
	 * @ORM\JoinColumn(name="required_dbchange_id", referencedColumnName="id")
	 *
	 * @var \Kapcus\DbChanger\Entity\DbChange
	 **/
	protected $requiredDbChange;

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
	 * @return \Kapcus\DbChanger\Entity\DbChange
	 */
	public function getMasterChange()
	{
		return $this->masterChange;
	}

	/**
	 * @param \Kapcus\DbChanger\Entity\DbChange $masterChange
	 */
	public function setMasterChange($masterChange)
	{
		$this->masterChange = $masterChange;
	}

	/**
	 * @return \Kapcus\DbChanger\Entity\DbChange
	 */
	public function getRequiredDbChange()
	{
		return $this->requiredDbChange;
	}

	/**
	 * @param \Kapcus\DbChanger\Entity\DbChange $requiredDbChange
	 */
	public function setRequiredDbChange($requiredDbChange)
	{
		$this->requiredDbChange = $requiredDbChange;
	}



}