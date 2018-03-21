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
	 * @var \Kapcus\DbChanger\Model\Placeholder[]
	 */
	private $placeholders = [];

	/**
	 * @ORM\OneToMany(targetEntity="UserGroup", mappedBy="environment", cascade={"persist", "remove"})
	 *
	 * @var \Kapcus\DbChanger\Entity\UserGroup[]
	 */
	protected $userGroups;

	/**
	 * Group constructor.
	 */
	public function __construct()
	{
		$this->userGroups = new ArrayCollection();
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
	 * @return \Kapcus\DbChanger\Model\Placeholder[]
	 */
	public function getPlaceholders()
	{
		return $this->placeholders;
	}

	/**
	 * @param \Kapcus\DbChanger\Model\Placeholder $placeholder
	 */
	public function addPlaceholder(Placeholder $placeholder)
	{
		$this->placeholders[] = $placeholder;
	}

	public function getPlaceholderCodes()
	{
		return array_map(
			function (Placeholder $o) {
				return $o->getCode();
			},
			$this->getPlaceholders()
		);
	}

	public function getPlaceholderValues()
	{
		return array_map(
			function (Placeholder $o) {
				return $o->getValue();
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


}