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

namespace App\Modules\People\Entity;

use App\Manager\EntityInterface;
use App\Manager\Traits\BooleanList;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class Staff
 * @package App\Modules\People\Entity
 * @ORM\Entity(repositoryClass="App\Modules\People\Repository\StaffRepository")
 * @ORM\Table(options={"auto_increment": 1}, name="staff", uniqueConstraints={@ORM\UniqueConstraint(name="person", columns={"person"}), @ORM\UniqueConstraint(name="initials", columns={"initials"})})
 */
class Staff implements EntityInterface
{
    use BooleanList;

    /**
     * @var integer|null
     * @ORM\Id
     * @ORM\Column(type="bigint", name="id", columnDefinition="INT(10) UNSIGNED AUTO_INCREMENT")
     * @ORM\GeneratedValue
     */
    private $id;

    /**
     * @var Person|null
     * @ORM\OneToOne(targetEntity="Person", inversedBy="staff")
     * @ORM\JoinColumn(name="person", referencedColumnName="id", nullable=false)
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
     * @ORM\Column(length=4,nullable=true,unique=true)
     */
    private $initials;

    /**
     * @var string|null
     * @ORM\Column(length=100,name="jobTitle")
     */
    private $jobTitle;

    /**
     * @var string|null
     * @ORM\Column(length=1, name="smartWorkflowHelp", options={"default": "Y"})
     * @Assert\Choice(callback="getBooleanList")
     */
    private $smartWorkflowHelp = 'Y';

    /**
     * @var string|null
     * @ORM\Column(length=1, name="firstAidQualified", options={"default": "N"})
     * @Assert\Choice(callback="getBooleanList")
     */
    private $firstAidQualified = 'N';

    /**
     * @var \DateTime|null
     * @ORM\Column(type="date", name="firstAidExpiry", nullable=true)
     */
    private $firstAidExpiry;

    /**
     * @var string|null
     * @ORM\Column(length=80, name="countryOfOrigin",nullable=true)
     * @Assert\Country()
     */
    private $countryOfOrigin;

    /**
     * @var string|null
     * @ORM\Column(name="qualifications",nullable=true)
     */
    private $qualifications;

    /**
     * @var string|null
     * @ORM\Column(type="text",nullable=true)
     */
    private $biography;

    /**
     * @var string|null
     * @ORM\Column(length=100,name="biographicalGrouping",options={"comment": "Used for group staff when creating a staff directory."},nullable=true)
     */
    private $biographicalGrouping;

    /**
     * @var integer|null
     * @ORM\Column(name="biographicalGroupingPriority", type="smallint", columnDefinition="INT(3)")
     */
    private $biographicalGroupingPriority;

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param int|null $id
     * @return Staff
     */
    public function setId(?int $id): Staff
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
     * @return \DateTime|null
     */
    public function getFirstAidExpiry(): ?\DateTime
    {
        return $this->firstAidExpiry;
    }

    /**
     * @param \DateTime|null $firstAidExpiry
     * @return Staff
     */
    public function setFirstAidExpiry(?\DateTime $firstAidExpiry): Staff
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
        return $this->getPerson()->formatName();
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
    public function create(): string
    {
        return "CREATE TABLE `__prefix__Staff` (
                    `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                    `type` varchar(20) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
                    `initials` varchar(4) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
                    `jobTitle` varchar(100) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
                    `smartWorkflowHelp` varchar(1) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Y',
                    `firstAidQualified` varchar(1) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT 'N',
                    `firstAidExpiry` date DEFAULT NULL,
                    `countryOfOrigin` varchar(80) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
                    `qualifications` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
                    `biography` longtext CHARACTER SET utf8 COLLATE utf8_unicode_ci,
                    `biographicalGrouping` varchar(100) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Used for group staff when creating a staff directory.',
                    `biographicalGroupingPriority` int(3) DEFAULT NULL,
                    `person` int(10) UNSIGNED DEFAULT NULL,
                    PRIMARY KEY (`id`),
                    UNIQUE KEY `staff` (`person`) USING BTREE,
                    UNIQUE KEY `initials` (`initials`)
                ) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
    }

    /**
     * foreignConstraints
     * @return string
     */
    public function foreignConstraints(): string
    {
        return "ALTER TABLE `gibbonstaff`
                    ADD CONSTRAINT FOREIGN KEY (`person`) REFERENCES `__prefix__Person` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT;";
    }

    /**
     * coreData
     * @return string
     */
    public function coreData(): string
    {
        return "";
    }

}