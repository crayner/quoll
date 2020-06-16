<?php
/**
 * Created by PhpStorm.
 *
* Quoll
 *
 * (c) 2018 Craig Rayner <craig@craigrayner.com>
 *
 * User: craig
 * Date: 23/11/2018
 * Time: 15:27
 */

namespace App\Modules\Staff\Entity;

use App\Manager\AbstractEntity;
use App\Manager\Traits\BooleanList;
use App\Modules\People\Entity\Person;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class Staff
 * @package App\Modules\People\Entity
 * @ORM\Entity(repositoryClass="App\Modules\Staff\Repository\StaffRepository")
 * @ORM\Table(name="staff",
 *     uniqueConstraints={@ORM\UniqueConstraint(name="person", columns={"person"}),
 *     @ORM\UniqueConstraint(name="initials", columns={"initials"})})
 * @UniqueEntity({"person"})
 * @UniqueEntity({"initials"})
 */
class Staff extends AbstractEntity
{
    CONST VERSION = '1.0.00';

    use BooleanList;

    /**
     * @var string|null
     * @ORM\Id()
     * @ORM\Column(type="guid")
     * @ORM\GeneratedValue(strategy="UUID")
     */
    private $id;

    /**
     * @var Person|null
     * @ORM\OneToOne(targetEntity="App\Modules\People\Entity\Person")
     * @ORM\JoinColumn(name="person",referencedColumnName="id",nullable=false)
     */
    private $person;

    /**
     * @var string|null
     * @ORM\Column(length=20)
     * @Assert\Choice(callback="getTypeList")
     */
    private $type;

    /**
     * @var array
     */
    private static $typeList = [
        'Teaching',
        'Support',
        'Other',
    ];
    /**
     * @var string|null
     * @ORM\Column(length=4,nullable=true)
     */
    private $initials;

    /**
     * @var string|null
     * @ORM\Column(length=100,nullable=true)
     */
    private $jobTitle;

    /**
     * @var string|null
     * @ORM\Column(length=1,options={"default": "Y"})
     * @Assert\Choice(callback="getBooleanList")
     */
    private $smartWorkflowHelp = 'Y';

    /**
     * @var string|null
     * @ORM\Column(length=1,options={"default": "N"})
     * @Assert\Choice(callback="getBooleanList")
     */
    private $firstAidQualified = 'N';

    /**
     * @var \DateTimeImmutable|null
     * @ORM\Column(type="date_immutable",nullable=true)
     */
    private $firstAidExpiry;

    /**
     * @var string|null
     * @ORM\Column(length=3,nullable=true)
     * @Assert\Country()
     */
    private $countryOfOrigin;

    /**
     * @var string|null
     * @ORM\Column(name="qualifications",nullable=true,length=191)
     */
    private $qualifications;

    /**
     * @var string|null
     * @ORM\Column(type="text",nullable=true)
     */
    private $biography;

    /**
     * @var string|null
     * @ORM\Column(length=100,options={"comment": "Used for group staff when creating a staff directory."},nullable=true)
     */
    private $biographicalGrouping;

    /**
     * @var integer|null
     * @ORM\Column(type="smallint",nullable=true)
     */
    private $biographicalGroupingPriority;

    /**
     * @return string|null
     */
    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * Id.
     *
     * @param string|null $id
     * @return Staff
     */
    public function setId(?string $id): Staff
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return Person|null
     */
    public function getPerson(): ?Person
    {
        return $this->person;
    }

    /**
     * setPerson
     * @param Person|null $person
     * @param bool $add
     * @return Staff
     */
    public function setPerson(?Person $person, bool $add = true): Staff
    {
        if ($person instanceof Person)
            $person->setStaff($this, false);
        $this->person = $person;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getType(): ?string
    {
        return $this->type;
    }

    /**
     * @param string|null $type
     * @return Staff
     */
    public function setType(?string $type): Staff
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getInitials(): ?string
    {
        return $this->initials;
    }

    /**
     * @param string|null $initials
     * @return Staff
     */
    public function setInitials(?string $initials): Staff
    {
        $this->initials = $initials;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getJobTitle(): ?string
    {
        return $this->jobTitle;
    }

    /**
     * @param string|null $jobTitle
     * @return Staff
     */
    public function setJobTitle(?string $jobTitle): Staff
    {
        $this->jobTitle = $jobTitle;
        return $this;
    }

    /**
     * @return bool
     */
    public function isSmartWorkflowHelp(): bool
    {
        return $this->getSmartWorkflowHelp() === 'Y';
    }

    /**
     * @return string|null
     */
    public function getSmartWorkflowHelp(): ?string
    {
        return $this->smartWorkflowHelp = self::checkBoolean($this->smartWorkflowHelp, 'Y');
    }

    /**
     * @param string|null $smartWorkflowHelp
     * @return Staff
     */
    public function setSmartWorkflowHelp(?string $smartWorkflowHelp): Staff
    {
        $this->smartWorkflowHelp = self::checkBoolean($smartWorkflowHelp);
        return $this;
    }

    /**
     * @return string|null
     */
    public function getFirstAidQualified(): ?string
    {
        return $this->firstAidQualified = self::checkBoolean($this->firstAidQualified, 'N');
    }

    /**
     * @param string|null $firstAidQualified
     * @return Staff
     */
    public function setFirstAidQualified(?string $firstAidQualified): Staff
    {
        $this->firstAidQualified = self::checkBoolean($firstAidQualified, 'N');
        return $this;
    }

    /**
     * @return \DateTimeImmutable|null
     */
    public function getFirstAidExpiry(): ?\DateTimeImmutable
    {
        return $this->firstAidExpiry;
    }

    /**
     * FirstAidExpiry.
     *
     * @param \DateTimeImmutable|null $firstAidExpiry
     * @return Staff
     */
    public function setFirstAidExpiry(?\DateTimeImmutable $firstAidExpiry): Staff
    {
        $this->firstAidExpiry = $firstAidExpiry;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getCountryOfOrigin(): ?string
    {
        return $this->countryOfOrigin;
    }

    /**
     * @param string|null $countryOfOrigin
     * @return Staff
     */
    public function setCountryOfOrigin(?string $countryOfOrigin): Staff
    {
        $this->countryOfOrigin = $countryOfOrigin;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getQualifications(): ?string
    {
        return $this->qualifications;
    }

    /**
     * @param string|null $qualifications
     * @return Staff
     */
    public function setQualifications(?string $qualifications): Staff
    {
        $this->qualifications = $qualifications;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getBiography(): ?string
    {
        return $this->biography;
    }

    /**
     * @param string|null $biography
     * @return Staff
     */
    public function setBiography(?string $biography): Staff
    {
        $this->biography = $biography;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getBiographicalGrouping(): ?string
    {
        return $this->biographicalGrouping;
    }

    /**
     * @param string|null $biographicalGrouping
     * @return Staff
     */
    public function setBiographicalGrouping(?string $biographicalGrouping): Staff
    {
        $this->biographicalGrouping = $biographicalGrouping;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getBiographicalGroupingPriority(): ?int
    {
        return $this->biographicalGroupingPriority;
    }

    /**
     * @param int|null $biographicalGroupingPriority
     * @return Staff
     */
    public function setBiographicalGroupingPriority(?int $biographicalGroupingPriority): Staff
    {
        $this->biographicalGroupingPriority = $biographicalGroupingPriority;
        return $this;
    }

    /**
     * @return array
     */
    public static function getTypeList(): array
    {
        return self::$typeList;
    }

    /**
     * __toString
     * @return string
     */
    public function __toString(): string
    {
        if ($this->getPerson())
            return $this->getPerson()->formatName();
        return $this->getId() ?: 'New Record.';
    }

    /**
     * toArray
     * @param string|null $name
     * @return array
     */
    public function toArray(?string $name = null): array
    {
        return [];
    }

    /**
     * create
     * @return string
     */
    public function create(): array
    {
        return ["CREATE TABLE `__prefix__Staff` (
                    `id` CHAR(36) NOT NULL COMMENT '(DC2Type:guid)',
                    `type` CHAR(20) NOT NULL,
                    `initials` CHAR(4) DEFAULT NULL,
                    `job_title` CHAR(100) DEFAULT NULL,
                    `smart_workflow_help` CHAR(1) NOT NULL DEFAULT 'Y',
                    `first_aid_qualified` CHAR(1) NOT NULL DEFAULT 'N',
                    `first_aid_expiry` date DEFAULT NULL COMMENT '(DC2Type:date_immutable)',
                    `country_of_origin` CHAR(3) DEFAULT NULL,
                    `qualifications` CHAR(191) DEFAULT NULL,
                    `biography` longtext,
                    `biographical_grouping` CHAR(100) DEFAULT NULL COMMENT 'Used for group staff when creating a staff directory.',
                    `biographical_grouping_priority` smallint DEFAULT NULL,
                    `person` CHAR(36) DEFAULT NULL,
                    PRIMARY KEY (`id`),
                    UNIQUE KEY `staff` (`person`),
                    UNIQUE KEY `initials` (`initials`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;"];
    }

    /**
     * foreignConstraints
     * @return string
     */
    public function foreignConstraints(): string
    {
        return "ALTER TABLE `__prefix__Staff`
                    ADD CONSTRAINT FOREIGN KEY (`person`) REFERENCES `__prefix__Person` (`id`);";
    }

    /**
     * coreData
     * @return string
     */public static function getVersion(): string
    {
        return self::VERSION;
    }
}
