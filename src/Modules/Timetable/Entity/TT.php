<?php
/**
 * Created by PhpStorm.
 *
* Quoll
 *
 * (c) 2018 Craig Rayner <craig@craigrayner.com>
 *
 * User: craig
 * Date: 5/12/2018
 * Time: 16:35
 */
namespace App\Modules\Timetable\Entity;

use App\Manager\AbstractEntity;
use App\Modules\School\Entity\AcademicYear;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\PersistentCollection;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class TT
 * @package App\Modules\Timetable\Entity
 * @ORM\Entity(repositoryClass="App\Modules\Timetable\Repository\TTRepository")
 * @ORM\Table(name="TT")
 */
class TT extends AbstractEntity
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
     * @var AcademicYear|null
     * @ORM\ManyToOne(targetEntity="App\Modules\School\Entity\AcademicYear")
     * @ORM\JoinColumn(name="academic_year", referencedColumnName="id", nullable=false)
     */
    private $AcademicYear;

    /**
     * @var string|null
     * @ORM\Column(length=30,unique=true)
     */
    private $name;

    /**
     * @var string|null
     * @ORM\Column(length=12, name="abbreviation",unique=true)
     */
    private $abbreviation;

    /**
     * @var string|null
     * @ORM\Column(length=24,options={"default": "Day Of The Week"})
     * @Assert\Choice(callback="getDisplayMode")
     */
    private $displayMode = 'Day Of The Week';

    /**
     * @var array
     */
    private static $displayModeList = ['Day Of The Week','Dashboard Day Short Name',''];

    /**
     * @var array|null
     * @ORM\Column(type="simple_array")
     */
    private $yearGroupList;

    /**
     * @var string|null
     * @ORM\Column(length=1)
     */
    private $active;

    /**
     * @var Collection
     * @ORM\OneToMany(targetEntity="TTDay", mappedBy="TT")
     */
    private $TTDays;

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
     * @return TT
     */
    public function setId(?string $id): TT
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return AcademicYear|null
     */
    public function getAcademicYear(): ?AcademicYear
    {
        return $this->AcademicYear;
    }

    /**
     * @param AcademicYear|null $AcademicYear
     * @return TT
     */
    public function setAcademicYear(?AcademicYear $AcademicYear): TT
    {
        $this->AcademicYear = $AcademicYear;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string|null $name
     * @return TT
     */
    public function setName(?string $name): TT
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getAbbreviation(): ?string
    {
        return $this->abbreviation;
    }

    /**
     * @param string|null $abbreviation
     * @return TT
     */
    public function setAbbreviation(?string $abbreviation): TT
    {
        $this->abbreviation = $abbreviation;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getDisplayMode(): ?string
    {
        return $this->displayMode;
    }

    /**
     * @param string|null $displayMode
     * @return TT
     */
    public function setDisplayMode(?string $displayMode): TT
    {
        $this->displayMode = in_array($displayMode, self::getDisplayModeList()) ? $displayMode : '';
        return $this;
    }

    /**
     * @return array|null
     */
    public function getYearGroupList(): ?array
    {
        return $this->yearGroupList;
    }

    /**
     * @param array|null $yearGroupList
     */
    public function setYearGroupList(?array $yearGroupList): void
    {
        $this->yearGroupList = $yearGroupList;
    }

    /**
     * @return string|null
     */
    public function getActive(): ?string
    {
        return $this->active;
    }

    /**
     * @param string|null $active
     * @return TT
     */
    public function setActive(?string $active): TT
    {
        $this->active = $active;
        return $this;
    }

    /**
     * @return array
     */
    public static function getDisplayModeList(): array
    {
        return self::$displayModeList;
    }

    /**
     * getTTDays
     * @return Collection
     */
    public function getTTDays(): Collection
    {
        if (empty($this->tTDays))
            $this->tTDays = new ArrayCollection();

        if ($this->tTDays instanceof PersistentCollection)
            $this->tTDays->initialize();

        return $this->tTDays;
    }

    /**
     * @param Collection $tTDays
     * @return TT
     */
    public function setTTDays(Collection $tTDays): TT
    {
        $this->tTDays = $tTDays;
        return $this;
    }

    /**
     * __toString
     * @return string
     */
    public function __toString(): string
    {
        return $this->getName() . ' ('.$this->getAbbreviation().')';
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

    public function create(): array
    {
        return ["CREATE TABLE `__prefix__TT` (
                    `id` CHAR(36) NOT NULL COMMENT '(DC2Type:guid)',
                    `name` CHAR(30) NOT NULL,
                    `abbreviation` CHAR(12) NOT NULL,
                    `display_mode` CHAR(24) NOT NULL DEFAULT 'Day Of The Week',
                    `year_group_list` text NOT NULL COMMENT '(DC2Type:simple_array)',
                    `active` CHAR(1) NOT NULL,
                    `academic_year` CHAR(36) DEFAULT NULL,
                    PRIMARY KEY (`id`),
                    KEY `academic_year` (`academic_year`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;"];
    }

    public function foreignConstraints(): string
    {
        return "ALTER TABLE `__prefix__TT`
  ADD CONSTRAINT FOREIGN KEY (`academic_year`) REFERENCES `__prefix__AcademicYear` (`id`);";
    }

    public static function getVersion(): string
    {
        return self::VERSION;
    }
}
