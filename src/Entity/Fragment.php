<?php

namespace Kapcus\DbChanger\Entity;

use Doctrine\ORM\Mapping AS ORM;
use Doctrine\Common\Collections\ArrayCollection;

use Kapcus\DbChanger\Model\Placeholder;

/**
 * @ORM\Entity
 * @ORM\Table(name="DBCH_FRAGMENT")
 */
class Fragment
{
	/**
	 * @ORM\Id
	 * @ORM\Column(type="integer")
	 * @ORM\GeneratedValue(strategy="SEQUENCE")
	 * @ORM\SequenceGenerator(sequenceName="DBCH_FRAG_SEQ")
	 *
	 * @var int
	 */
	protected $id;

	/**
	 *
	 * @ORM\Column(type="clob")
	 *
	 * @var string
	 */
	protected $templateContent;

	/**
	 * @ORM\ManyToOne(targetEntity="DbChange", inversedBy="fragments")
	 *
	 * @var \Kapcus\DbChanger\Entity\DbChange
	 **/
	protected $dbChange;

	/**
	 * @ORM\ManyToOne(targetEntity="Group", inversedBy="fragments")
	 *
	 * @var \Kapcus\DbChanger\Entity\Group
	 **/
	protected $group;

	/**
	 * @var string
	 */
	protected $filename;

	/**
	 * @var string
	 */
	protected $pathname;


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
	public function getTemplateContent()
	{
		return $this->templateContent;
	}

	/**
	 * @param string $templateContent
	 */
	public function setTemplateContent($templateContent)
	{
		$this->templateContent = $templateContent;
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
	public function setDbChange($dbChange)
	{
		$this->dbChange = $dbChange;
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
	 * @return string
	 */
	public function getFilename()
	{
		return $this->filename;
	}

	/**
	 * @param string $filename
	 */
	public function setFilename($filename)
	{
		$this->filename = $filename;
	}

	/**
	 * @return string
	 */
	public function getPathname()
	{
		return $this->pathname;
	}

	/**
	 * @param string $pathname
	 */
	public function setPathname($pathname)
	{
		$this->pathname = $pathname;
	}

	/**
	 * @return string
	 */
	public function getTemplateContentFromFile()
	{
		return file_get_contents($this->getPathname());
	}


}