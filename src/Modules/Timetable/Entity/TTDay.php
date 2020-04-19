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

use App\Manager\EntityInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\PersistentCollection;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * Class TTDay
 * @package App\Modules\Timetable\Entity
 * @ORM\Entity(repositoryClass="App\Modules\Timetable\Repository\TTDayRepository")
 * @ORM\Table(options={"auto_increment": 1}, name="TTDay",
 *     indexes={@ORM\Index(name="timetable_column", columns={"timetable_column"}),
 *     @ORM\Index(name="timetable", columns={"timetable"})},
 *     uniqueConstraints={@ORM\UniqueConstraint(name="name_timetable",columns={"name","timetable"}),
 *     @ORM\UniqueConstraint(name="name_short_timetable",columns={"nameShort","timetable"})})
 * @UniqueEntity({"name","TT"})
 * @UniqueEntity({"nameShort","TT"})
 */
class TTDay implements EntityInterface
{
    /**
     * @var integer|null
     * @ORM\Id
     * @ORM\Column(type="integer",columnDefinition="INT(10) UNSIGNED AUTO_INCREMENT")
     * @ORM\GeneratedValue
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
     * @ORM\Column(length=4, name="nameShort")
     */
    private $nameShort;

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
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param int|null $id
     * @return TTDay
     */
    public function setId(?int $id): TTDay
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
    public function getNameShort(): ?string
    {
        return $this->nameShort;
    }

    /**
     * @param string|null $nameShort
     * @return TTDay
     */
    public function setNameShort(?string $nameShort): TTDay
    {
        $this->nameShort = $nameShort;
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
        return $this->getName() . ' ('.$this->getNameShort().') of '.$this->getTT()->__toString();
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

    public function create(): string
    {
        return 'CREATE TABLE `__prefix__TTDay` (
                    `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                    `name` varchar(12) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
                    `nameShort` varchar(4) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
                    `colour` varchar(6) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
                    `font_colour` varchar(6) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
                    `timetable` int(8) UNSIGNED DEFAULT NULL,
                    `timetable_column` int(6) UNSIGNED DEFAULT NULL,
                    PRIMARY KEY (`id`),
                    UNIQUE KEY `name_short_timetable` (`timetable`,`nameShort`) USING BTREE,
                    UNIQUE KEY `name_timetable` (`timetable`,`name`) USING BTREE,
                    KEY `timetable` (`timetable`) USING BTREE,
                    KEY `timetable_column` (`timetable_column`) USING BTREE
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;';
    }

    public function foreignConstraints(): string
    {
        return 'ALTER TABLE `__prefix__TTDay`
                    ADD CONSTRAINT FOREIGN KEY (`timetable`) REFERENCES `__prefix__TT` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
                    ADD CONSTRAINT FOREIGN KEY (`timetable_column`) REFERENCES `__prefix__TTColumn` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT;';
    }

    public function coreData(): string
    {
        return '';
    }
}