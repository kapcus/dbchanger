<?php

namespace Kapcus\DbChanger\Entity;

use Doctrine\ORM\Mapping AS ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity
 * @ORM\Table(name="DBCH_PLACEHOLDER")
 */
class Placeholder
{
	/** @ORM\Id
	 * @ORM\Column(type="integer")
	 * @ORM\GeneratedValue(strategy="SEQUENCE")
	 * @ORM\SequenceGenerator(sequenceName="DBCH_PLACE_SEQ")
	 *
	 * @var int
	 */
	protected $id;

	/**
	 * @ORM\Column(type="string")
	 * @var string
	 */
	protected $code;

	/**
	 * @ORM\Column(type="string", name="translated_value")
	 *
	 * @var string
	 */
	protected $translatedValue;

	/**
	 * @ORM\ManyToOne(targetEntity="Environment", inversedBy="placeholders")
	 *
	 * @var \Kapcus\DbChanger\Entity\Environment
	 **/
	protected $environment;

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
	public function getTranslatedValue()
	{
		return $this->translatedValue;
	}

	/**
	 * @param string $translatedValue
	 */
	public function setTranslatedValue($translatedValue)
	{
		$this->translatedValue = $translatedValue;
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