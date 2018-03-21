<?php

namespace Kapcus\DbChanger\Entity;

use Doctrine\ORM\Mapping AS ORM;
use Doctrine\Common\Collections\ArrayCollection;

use Kapcus\DbChanger\Model\Placeholder;

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
	 * @ORM\Column(type="string", nullable=true)
	 *
	 * @var string
	 */
	protected $description;

	/**
	 * @ORM\OneToMany(targetEntity="Fragment", mappedBy="dbchange", cascade={"persist", "remove"})
	 *
	 * @var \Kapcus\DbChanger\Entity\Fragment[]
	 */
	protected $fragments;

	/**
	 * DbChange constructor.
	 */
	public function __construct()
	{
		$this->fragments = new ArrayCollection();
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
	 * @param \Kapcus\DbChanger\Entity\Fragment $fragment
	 */
	public function addFragment(Fragment $fragment)
	{
		$this->fragments[] = $fragment;
	}

	public function hasFragment() {
		return count($this->getFragments()) > 0;
	}


}