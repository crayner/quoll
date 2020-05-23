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
 * Time: 16:52
 */
namespace App\Modules\Timetable\Entity;

use App\Manager\AbstractEntity;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\PersistentCollection;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * Class TTDay
 * @package App\Modules\Timetable\Entity
 * @ORM\Entity(repositoryClass="App\Modules\Timetable\Repository\TTDayRepository")
 * @ORM\Table(name="TTDay",
 *     indexes={@ORM\Index(name="timetable_column", columns={"timetable_column"}),
 *     @ORM\Index(name="timetable", columns={"timetable"})},
 *     uniqueConstraints={@ORM\UniqueConstraint(name="name_timetable",columns={"name","timetable"}),
 *     @ORM\UniqueConstraint(name="name_short_timetable",columns={"abbreviation","timetable"})})
 * @UniqueEntity({"name","TT"})
 * @UniqueEntity({"abbreviation","TT"})
 */
class TTDay extends AbstractEntity
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
     * @var TT|null
     * @ORM\ManyToOne(targetEntity="TT", inversedBy="TTDays")
     * @ORM\JoinColumn(name="timetable", referencedColumnName="id", nullable=false)
     */
    private $TT;

    /**
     * @var TTColumn|null
     * @ORM\ManyToOne(targetEntity="TTColumn")
     * @ORM\JoinColumn(name="timetable_column", referencedColumnName="id", nullable=false)
     */
    private $TTColumn;

    /**
     * @var string|null
     * @ORM\Column(length=12)
     */
    private $name;

    /**
     * @var string|null
     * @ORM\Column(length=4, name="abbreviation")
     */
    private $abbreviation;

    /**
     * @var string|null
     * @ORM\Column(length=6, name="colour")
     */
    private $colour;

    /**
     * @var string|null
     * @ORM\Column(length=6, name="font_colour")
     */
    private $fontColour;

    /**
     * @var Collection
     * @ORM\OneToMany(targetEntity="TTDayRowClass", mappedBy="TTDay")
     */
    private $TTDayRowClasses;

    /**
     * @var Collection
     * @ORM\OneToMany(targetEntity="TTDayDate", mappedBy="TTDay")
     */
    private $timetableDayDates;

    /**
     * TTDay constructor.
     */
    public function __construct()
    {
        $this->setTimetableDayDates(new ArrayCollection());
    }

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
     * @return TTDay
     */
    public function setId(?string $id): TTDay
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return TT|null
     */
    public function getTT(): ?TT
    {
        return $this->TT;
    }

    /**
     * @param TT|null $TT
     * @return TTDay
     */
    public function setTT(?TT $TT): TTDay
    {
        $this->TT = $TT;
        return $this;
    }

    /**
     * @return TTColumn|null
     */
    public function getTTColumn(): ?TTColumn
    {
        return $this->TTColumn;
    }

    /**
     * @param TTColumn|null $TTColumn
     * @return TTDay
     */
    public function setTTColumn(?TTColumn $TTColumn): TTDay
    {
        $this->TTColumn = $TTColumn;
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
     * @return TTDay
     */
    public function setName(?string $name): TTDay
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
     * @return TTDay
     */
    public function setAbbreviation(?string $abbreviation): TTDay
    {
        $this->abbreviation = $abbreviation;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getColour(): ?string
    {
        return $this->colour;
    }

    /**
     * @param string|null $colour
     * @return TTDay
     */
    public function setColour(?string $colour): TTDay
    {
        $this->colour = $colour;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getFontColour(): ?string
    {
        return $this->fontColour;
    }

    /**
     * @param string|null $fontColour
     * @return TTDay
     */
    public function setFontColour(?string $fontColour): TTDay
    {
        $this->fontColour = $fontColour;
        return $this;
    }

    /**
     * getTTDayRowClasses
     * @return Collection
     */
    public function getTTDayRowClasses(): Collection
    {
        if (empty($this->TTDayRowClasses))
            $this->TTDayRowClasses = new ArrayCollection();

        if ($this->TTDayRowClasses instanceof PersistentCollection)
            $this->TTDayRowClasses->initialize();

        return $this->TTDayRowClasses;
    }

    /**
     * @param Collection $TTDayRowClasses
     * @return TTDay
     */
    public function setTTDayRowClasses(Collection $TTDayRowClasses): TTDay
    {
        $this->TTDayRowClasses = $TTDayRowClasses;
        return $this;
    }

    /**
     * getTimetableDayDates
     * @return Collection
     */
    public function getTimetableDayDates(): Collection
    {
        if (empty($this->timetableDayDates))
            $this->timetableDayDates = new ArrayCollection();
        
        if ($this->timetableDayDates instanceof PersistentCollection)
            $this->timetableDayDates->initialize();
        
        return $this->timetableDayDates;
    }

    /**
     * @param Collection $timetableDayDates
     * @return TTDay
     */
    public function setTimetableDayDates(Collection $timetableDayDates): TTDay
    {
        $this->timetableDayDates = $timetableDayDates;
        return $this;
    }

    /**
     * __toString
     * @return string
     */
    public function __toString(): string
    {
        return $this->getName() . ' ('.$this->getAbbreviation().') of '.$this->getTT()->__toString();
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
        return ["CREATE TABLE `__prefix__TTDay` (
                    `id` CHAR(36) NOT NULL COMMENT '(DC2Type:guid)',
                    `name` CHAR(12) NOT NULL,
                    `abbreviation` CHAR(4) NOT NULL,
                    `colour` CHAR(6) NOT NULL,
                    `font_colour` CHAR(6) NOT NULL,
                    `timetable` CHAR(36) DEFAULT NULL,
                    `timetable_column` CHAR(36) DEFAULT NULL,
                    PRIMARY KEY (`id`),
                    UNIQUE KEY `name_short_timetable` (`timetable`,`abbreviation`),
                    UNIQUE KEY `name_timetable` (`timetable`,`name`),
                    KEY `timetable` (`timetable`),
                    KEY `timetable_column` (`timetable_column`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;"];
    }

    public function foreignConstraints(): string
    {
        return "ALTER TABLE `__prefix__TTDay`
                    ADD CONSTRAINT FOREIGN KEY (`timetable`) REFERENCES `__prefix__TT` (`id`),
                    ADD CONSTRAINT FOREIGN KEY (`timetable_column`) REFERENCES `__prefix__TTColumn` (`id`);";
    }

    public static function getVersion(): string
    {
        return self::VERSION;
    }
}
