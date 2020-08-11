<?php
/**
 * Created by PhpStorm.
 *
 * Gibbon, Flexible & Open School System
 * Copyright (C) 2010, Ross Parker
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program in the LICENCE file.
 * If not, see <hTTColumnp://www.gnu.org/licenses/>.
 *
 * Project: Kookaburra
 * Build: Quoll
 *
 * (c) 2018 Craig Rayner <craig@craigrayner.com>
 *
 * User: craig
 * Date: 5/12/2018
 * Time: 16:42
 */
namespace App\Modules\Timetable\Entity;

use App\Manager\AbstractEntity;
use App\Modules\School\Entity\DaysOfWeek;
use App\Provider\ProviderFactory;
use App\Util\TranslationHelper;
use App\Validator\Colour;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\PersistentCollection;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class TimetableColumn
 * @package App\Modules\Timetable\Entity
 * @ORM\Entity(repositoryClass="App\Modules\Timetable\Repository\TimetableDayRepository")
 * @ORM\Table(name="TimetableDay",
 *     indexes={@ORM\Index(name="timetable",columns={"timetable"})},
 *     uniqueConstraints={@ORM\UniqueConstraint(name="name",columns={"name"}),
 *     @ORM\UniqueConstraint(name="abbreviation",columns={"abbreviation"}),
 *     @ORM\UniqueConstraint(name="timetable_order",columns={"rotate_order","timetable"})})
 * @UniqueEntity("name")
 * @UniqueEntity("abbreviation")
 * @UniqueEntity({"rotateOrder","timetable"})
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
     * @ORM\ManyToOne(targetEntity="App\Modules\Timetable\Entity\Timetable",inversedBy="timetableDays")
     * @ORM\JoinColumn(name="timetable",referencedColumnName="id")
     */
    private $timetable;
    
    /**
     * @var string|null
     * @ORM\Column(length=30)
     * @Assert\NotBlank()
     * @Assert\Length(max=30)
     */
    private $name;

    /**
     * @var string|null
     * @ORM\Column(length=12,name="abbreviation")
     * @Assert\NotBlank()
     * @Assert\Length(max=12)
     */
    private $abbreviation;

    /**
     * @var Collection|DaysOfWeek[]|null
     * @ORM\ManyToMany(targetEntity="App\Modules\School\Entity\DaysOfWeek")
     * @ORM\JoinTable(name="TimetableDayDayOfWeek",
     *      joinColumns={@ORM\JoinColumn(name="timetable_day",referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="day_of_week",referencedColumnName="id")})
     */
    private $daysOfWeek;

    /**
     * @var Collection
     * @ORM\OneToMany(targetEntity="TimetablePeriod",mappedBy="timetableDay",cascade={"all"})
     * @ORM\OrderBy({"timeStart" = "ASC"})
     */
    private $periods;

    /**
     * @var string|null
     * @ORM\Column(length=7,name="colour",options={"default": "#0000CC"})
     * @Colour(enforceType="hex")
     */
    private $colour;

    /**
     * @var string|null
     * @ORM\Column(length=7,name="font_colour",options={"default": "#FFFFFF"})
     * @Colour(enforceType="hex")
     */
    private $fontColour;

    /**
     * @var integer
     * @ORM\Column(type="smallint")
     * @Assert\Range(min=1,max=99)
     */
    private $rotateOrder;

    /**
     * @var Collection
     * @ORM\OneToMany(targetEntity="TimetableDate",mappedBy="timetableDay",cascade={"all"},orphanRemoval=true)
     */
    private $timetableDates;

    /**
     * TimetableColumn constructor.
     * @param Timetable|null $timetable
     */
    public function __construct(?Timetable $timetable = null)
    {
        $this->setPeriods(new ArrayCollection())
            ->setTimetable($timetable)
            ->nextRotateOrder();
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
     * @param Timetable|null $timetable
     * @return TimetableDay
     */
    public function setTimetable(?Timetable $timetable): TimetableDay
    {
        $this->timetable = $timetable;
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
     * @return DaysOfWeek[]|Collection|null
     */
    public function getDaysOfWeek(): Collection
    {
        if ($this->daysOfWeek === null) $this->daysOfWeek = new ArrayCollection();

        if ($this->daysOfWeek instanceof PersistentCollection) $this->daysOfWeek->initialize();

        return $this->daysOfWeek;
    }

    /**
     * setDaysOfWeek
     * @param Collection|null $daysOfWeek
     * @return TimetableDay
     * 7/08/2020 13:35
     */
    public function setDaysOfWeek(?Collection $daysOfWeek): TimetableDay
    {
        $this->daysOfWeek = $daysOfWeek;
        return $this;
    }

    /**
     * getDaysOfWeekNames
     * @return string
     * 7/08/2020 12:55
     */
    public function getDaysOfWeekNames(): string
    {
        $result = [];
        foreach ($this->getDaysOfWeek() as $day) {
            $result[] = $day->getAbbreviation();
        }
        return implode(', ',$result);
    }

    /**
     * getPeriods
     * @return Collection
     */
    public function getPeriods(): Collection
    {
        if (empty($this->periods))
            $this->periods = new ArrayCollection();

        if ($this->periods instanceof PersistentCollection)
            $this->periods->initialize();

        $iterator = $this->periods->getIterator();
        $iterator->uasort(
            function ($a, $b) {
                return ($a->getTimeStart()->format('His') < $b->getTimeStart()->format('His')) ? -1 : 1;
            }
        );

        $this->periods = new ArrayCollection(iterator_to_array($iterator, false));

        return $this->periods;
    }

    /**
     * @param Collection $periods
     * @return TimetableDay
     */
    public function setPeriods(Collection $periods): TimetableDay
    {
        $this->periods = $periods;
        return $this;
    }

    /**
     * addPeriod
     * @param TimetablePeriod $period
     * @param bool $reflect
     * @return $this
     * 5/08/2020 10:19
     */
    public function addPeriod(TimetablePeriod $period, bool $reflect = true): TimetableDay
    {
        if (!$this->getPeriods()->contains($period)) {
            if ($reflect) {
                $period->setTimetableDay($this, false);
            }
            $this->periods->add($period);
        }
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
     * @return int
     */
    public function getRotateOrder(): int
    {
        return $this->rotateOrder;
    }

    /**
     * @param int $rotateOrder
     * @return TimetableDay
     */
    public function setRotateOrder(int $rotateOrder): TimetableDay
    {
        $this->rotateOrder = $rotateOrder;
        return $this;
    }

    /**
     * getFixed
     * @return bool
     * 10/08/2020 10:58
     */
    public function isFixed(): bool
    {
        return ProviderFactory::create(TimetableDay::class)->isFixed($this);
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
            'periodCount' => $this->getPeriods()->count(),
            'hasPeriods' => intval($this->getPeriods()->count()) > 0,
            'canDelete' => true,
            'weekDays' => $this->getDaysOfWeekNames(),
            'timetable' => $this->getTimetable() ? $this->getTimetable()->getId() : null,
            'fixed' => TranslationHelper::translate($this->isFixed() ? 'Yes' : 'No', [], 'messages'),
            'isFixed' => $this->isFixed(),
        ];
    }

    /**
     * nextRotateOrder
     * @return TimetableDay
     * 6/08/2020 13:05
     */
    public function nextRotateOrder(): TimetableDay
    {
        if ($this->getTimetable() instanceof Timetable)
            return $this->setRotateOrder(ProviderFactory::getRepository(TimetableDay::class)->nextRotateOrder($this->getTimetable()));
        return $this;
    }
}
