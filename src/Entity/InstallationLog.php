<?php

namespace Kapcus\DbChanger\Entity;

use Doctrine\ORM\Mapping AS ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity
 * @ORM\Table(name="DBCH_INSTALLATION_LOG")
 */
class InstallationLog
{
	/** @ORM\Id
	 * @ORM\Column(type="integer")
	 * @ORM\GeneratedValue(strategy="SEQUENCE")
	 * @ORM\SequenceGenerator(sequenceName="DBCH_IL_SEQ")
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
	 * @ORM\ManyToOne(targetEntity="InstalledFragment", inversedBy="logs", cascade={"persist", "remove"})
	 * @ORM\JoinColumn(name="installed_fragment_id", referencedColumnName="id")
	 *
	 * @var \Kapcus\DbChanger\Entity\InstalledFragment
	 **/
	protected $installedFragment;

	/**
	 * @ORM\ManyToOne(targetEntity="Installation", inversedBy="logs")
	 *
	 * @var \Kapcus\DbChanger\Entity\Installation
	 **/
	protected $installation;

	/**
	 *
	 * @ORM\Column(type="text")
	 *
	 * @var string
	 */
	protected $content;

	/**
	 *
	 * @ORM\Column(type="integer", name="installation_status")
	 *
	 * @var int
	 */
	protected $installationStatus;

	/**
	 *
	 * @ORM\Column(type="integer", name="installed_fragment_status")
	 *
	 * @var int
	 */
	protected $installedFragmentStatus;

	/**
	 *
	 * @ORM\Column(type="text", name="result_message")
	 *
	 * @var string
	 */
	protected $resultMessage;

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
	 * @return \Kapcus\DbChanger\Entity\InstalledFragment
	 */
	public function getInstalledFragment()
	{
		return $this->installedFragment;
	}

	/**
	 * @param \Kapcus\DbChanger\Entity\InstalledFragment $installedFragment
	 */
	public function setInstalledFragment(InstalledFragment $installedFragment)
	{
		$this->installedFragment = $installedFragment;
	}

	/**
	 * @return \Kapcus\DbChanger\Entity\Installation
	 */
	public function getInstallation()
	{
		return $this->installation;
	}

	/**
	 * @param \Kapcus\DbChanger\Entity\Installation $installation
	 */
	public function setInstallation(Installation $installation)
	{
		$this->installation = $installation;
	}

	/**
	 * @return string
	 */
	public function getContent()
	{
		return $this->content;
	}

	/**
	 * @param string $content
	 */
	public function setContent($content)
	{
		$this->content = $content;
	}

	/**
	 * @return int
	 */
	public function getInstallationStatus()
	{
		return $this->installationStatus;
	}

	/**
	 * @param int $installationStatus
	 */
	public function setInstallationStatus($installationStatus)
	{
		$this->installationStatus = $installationStatus;
	}

	/**
	 * @return int
	 */
	public function getInstalledFragmentStatus()
	{
		return $this->installedFragmentStatus;
	}

	/**
	 * @param int $installedFragmentStatus
	 */
	public function setInstalledFragmentStatus($installedFragmentStatus)
	{
		$this->installedFragmentStatus = $installedFragmentStatus;
	}

	/**
	 * @return string
	 */
	public function getResultMessage()
	{
		return $this->resultMessage;
	}

	/**
	 * @param string $resultMessage
	 */
	public function setResultMessage($resultMessage)
	{
		$this->resultMessage = $resultMessage;
	}

	/**
	 * @param \Kapcus\DbChanger\Entity\InstalledFragment $installedFragment
	 */
	public function createFromFragment(InstalledFragment $installedFragment) {
		$this->setInstallation($installedFragment->getInstallation());
		$this->setInstalledFragment($installedFragment);
		$this->setInstallationStatus($installedFragment->getInstallation()->getStatus());
		$this->setInstalledFragmentStatus($installedFragment->getStatus());
		$this->setContent($installedFragment->getContent());
	}


}