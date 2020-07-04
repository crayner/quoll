<?php
/**
 * Created by PhpStorm.
 *
  * Project: Kookaburra
 * Build: Quoll
 *
 * (c) 2018 Craig Rayner <craig@craigrayner.com>
 *
 * User: craig
 * Date: 23/11/2018
 * Time: 15:27
 */
namespace App\Modules\School\Entity;

use App\Manager\AbstractEntity;
use App\Modules\People\Entity\Person;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class FacilityPerson
 * @package App\Modules\School\Entity
 * @ORM\Entity(repositoryClass="App\Modules\School\Repository\FacilityPersonRepository")
 * @ORM\Table(name="FacilityPerson",
 *     indexes={@ORM\Index(name="facility",columns={"facility"}),
 *     @ORM\Index(name="person",columns={"person"})})
 */
class FacilityPerson extends AbstractEntity
{
    CONST VERSION = '1.0.00';

    /**
     * @var string|null
     * @ORM\Id()
     * @ORM\Column(type="guid")
     * @ORM\GeneratedValue(strategy="UUID")
     */
    private $id;

    /**
     * @var Facility|null
     * @ORM\ManyToOne(targetEntity="Facility")
     * @ORM\JoinColumn(name="facility", referencedColumnName="id", nullable=false)
     */
    private $facility;

    /**
     * @var Person|null
     * @ORM\ManyToOne(targetEntity="App\Modules\People\Entity\Person")
     * @ORM\JoinColumn(name="person", referencedColumnName="id", nullable=false)
     */
    private $person;

    /**
     * @var string|null
     * @ORM\Column(length=8,nullable=true)
     */
    private $usageType;

    /**
     * @var array
     */
    private static $usageTypeList = ['', 'Teaching', 'Office', 'Other'];

    /**
     * @return string|null
     */
    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * @param string|null $id
     * @return FacilityPerson
     */
    public function setId(?string $id): FacilityPerson
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return Facility|null
     */
    public function getFacility(): ?Facility
    {
        return $this->facility;
    }

    /**
     * Facility.
     *
     * @param Facility|null $facility
     * @return FacilityPerson
     */
    public function setFacility(?Facility $facility): FacilityPerson
    {
        $this->facility = $facility;
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
     * @param Person|null $person
     * @return FacilityPerson
     */
    public function setPerson(?Person $person): FacilityPerson
    {
        $this->person = $person;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getUsageType(): ?string
    {
        return $this->usageType;
    }

    /**
     * @param string|null $usageType
     * @return FacilityPerson
     */
    public function setUsageType(?string $usageType): FacilityPerson
    {
        $this->usageType = in_array($usageType, self::getUsageTypeList()) ? $usageType : null;
        return $this;
    }

    /**
     * @return array
     */
    public static function getUsageTypeList(): array
    {
        return self::$usageTypeList;
    }

    public function toArray(?string $name = null): array
    {
        return [];
    }

    /**
     * create
     * @return array|string[]
     * 4/06/2020 12:28
     */
    public function create(): array
    {
        return ["CREATE TABLE `__prefix__FacilityPerson` (
                    `id` char(36) NOT NULL COMMENT '(DC2Type:guid)',
                    `facility` char(36) NOT NULL COMMENT '(DC2Type:guid)',
                    `person` char(36) NOT NULL COMMENT '(DC2Type:guid)',
                    `usage_type` varchar(8) DEFAULT NULL,
                    PRIMARY KEY (`id`),
                    KEY `facility` (`facility`),
                    KEY `person` (`person`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;"];
    }

    /**
     * foreignConstraints
     * @return string
     * 4/06/2020 12:28\
     */
    public function foreignConstraints(): string
    {
        return "ALTER TABLE `__prefix__FacilityPerson`
                    ADD CONSTRAINT FOREIGN KEY (`facility`) REFERENCES `__prefix__Facility` (`id`),
                    ADD CONSTRAINT FOREIGN KEY (`person`) REFERENCES `__prefix__Person` (`id`);";
    }

    /**
     * getVersion
     * @return string
     * 4/06/2020 12:22
     */
    public static function getVersion(): string
    {
        return static::VERSION;
    }
}