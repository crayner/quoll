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
 * Date: 5/12/2018
 * Time: 16:52
 */
namespace App\Modules\Timetable\Entity;

use App\Manager\AbstractEntity;
use App\Validator\Colour;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\PersistentCollection;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class TIMETABLEDay
 * @package App\Modules\Timetable\Entity
 * @ORM\Entity(repositoryClass="App\Modules\Timetable\Repository\TimetableDayRepository")
 * @ORM\Table(name="TimetableDay",
 *     indexes={@ORM\Index(name="timetable_column", columns={"timetable_column"}),
 *     @ORM\Index(name="timetable", columns={"timetable"})},
 *     uniqueConstraints={@ORM\UniqueConstraint(name="name_timetable",columns={"name","timetable"}),
 *     @ORM\UniqueConstraint(name="abbreviation_timetable",columns={"abbreviation","timetable"})})
 * @UniqueEntity({"name","timetable"})
 * @UniqueEntity({"abbreviation","Timetable"})
 */
class TimetableDay extends AbstractEntity
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
     * @var Timetable|null
     * @ORM\ManyToOne(targetEntity="Timetable", inversedBy="timetableDays")
     * @ORM\JoinColumn(name="timetable",referencedColumnName="id",nullable=false)
     */
    private $timetable;

    /**
     * @var TimetableColumn|null
     * @ORM\ManyToOne(targetEntity="TimetableColumn")
     * @ORM\JoinColumn(name="timetable_column",referencedColumnName="id",nullable=false)
     */
    private $timetableColumn;

    /**
     * @var string|null
     * @ORM\Column(length=12)
     * @Assert\NotBlank()
     * @Assert\Length(max=12)
     */
    private $name;

    /**
     * @var string|null
     * @ORM\Column(length=4, name="abbreviation")
     * @Assert\NotBlank()
     * @Assert\Length(max=4)
     */
    private $abbreviation;

    /**
     * @var string|null
     * @ORM\Column(length=7, name="colour")
     * @Colour(enforceType="hex")
     */
    private $colour;

    /**
     * @var string|null
     * @ORM\Column(length=7, name="font_colour")
     * @Colour(enforceType="hex")
     */
    private $fontColour;

    /**
     * @var Collection
     * @ORM\OneToMany(targetEntity="TimetableDayRowClass",mappedBy="timetableDay")
     */
    private $timetableDayRowClasses;

    /**
     * @var Collection
     * @ORM\OneToMany(targetEntity="TimetableDayDate",mappedBy="timetableDay")
     */
    private $timetableDayDates;

    /**
     * TimetableDay constructor.
     * @param Timetable|null $timetable
     */
    public function __construct(?Timetable $timetable = null)
    {
        $this->setTimetableDayDates(new ArrayCollection())
            ->setTimetableDayRowClasses(new ArrayCollection())
            ->setTimetable($timetable)
        ;
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
     * @return TimetableDay
     */
    public function setId(?string $id): TimetableDay
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return Timetable|null
     */
    public function getTimetable(): ?Timetable
    {
        return $this->timetable;
    }

    /**
     * @param Timetable|null $TIMETABLE
     * @return TimetableDay
     */
    public function setTimetable(?Timetable $timetable): TimetableDay
    {
        $this->timetable = $timetable;
        return $this;
    }

    /**
     * getTimetableColumn
     * @return TimetableColumn|null
     * 4/08/2020 08:41
     */
    public function getTimetableColumn(): ?TimetableColumn
    {
        return $this->timetableColumn;
    }

    /**
     * setTimetableColumn
     * @param TimetableColumn|null $timetableColumn
     * @return $this
     * 4/08/2020 08:41
     */
    public function setTimetableColumn(?TimetableColumn $timetableColumn): TimetableDay
    {
        $this->timetableColumn = $timetableColumn;
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
     * @return TimetableDay
     */
    public function setName(?string $name): TimetableDay
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
     * @return TimetableDay
     */
    public function setAbbreviation(?string $abbreviation): TimetableDay
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
     * @return TimetableDay
     */
    public function setColour(?string $colour): TimetableDay
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
     * @return TimetableDay
     */
    public function setFontColour(?string $fontColour): TimetableDay
    {
        $this->fontColour = $fontColour;
        return $this;
    }

    /**
     * getTimetableDayRowClasses
     * @return Collection
     * 4/08/2020 08:40
     */
    public function getTimetableDayRowClasses(): Collection
    {
        if (empty($this->timetableDayRowClasses))
            $this->timetableDayRowClasses = new ArrayCollection();

        if ($this->timetableDayRowClasses instanceof PersistentCollection)
            $this->timetableDayRowClasses->initialize();

        return $this->timetableDayRowClasses;
    }

    /**
     * @param Collection $TIMETABLEDayRowClasses
     * @return TimetableDay
     */
    public function setTimetableDayRowClasses(Collection $TIMETABLEDayRowClasses): TimetableDay
    {
        $this->timetableDayRowClasses = $TIMETABLEDayRowClasses;
        return $this;
    }

    /**
     * getTimetableDayDates
     * @return Collection
     * 4/08/2020 08:40
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
     * @return TimetableDay
     */
    public function setTimetableDayDates(Collection $timetableDayDates): TimetableDay
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
        return $this->getName() . ' ('.$this->getAbbreviation().') of '.$this->getTimetable()->__toString();
    }

    /**
     * toArray
     * @param string|null $name
     * @return array
     */
    public function toArray(?string $name = null): array
    {
        return [
            'id' => $this->getId(),
            'name' => $this->getName(),
            'abbreviation' => $this->getAbbreviation(),
            'columns' => $this->getTimetableColumn()->getName(),
            'timetable' => $this->getTimetable()->getId(),
        ];
    }
}
