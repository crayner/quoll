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
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\PersistentCollection;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * Class TimetableColumn
 * @package App\Modules\Timetable\Entity
 * @ORM\Entity(repositoryClass="App\Modules\Timetable\Repository\TimetableColumnRepository")
 * @ORM\Table(name="TimetableColumn",
 *     uniqueConstraints={@ORM\UniqueConstraint(name="name",columns={"name"}),
 *     @ORM\UniqueConstraint(name="abbreviation",columns={"abbreviation"})})
 * @UniqueEntity("name")
 * @UniqueEntity("abbreviation")
 */
class TimetableColumn extends AbstractEntity
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
     * @var string|null
     * @ORM\Column(length=30)
     */
    private $name;

    /**
     * @var string|null
     * @ORM\Column(length=12, name="abbreviation")
     */
    private $abbreviation;

    /**
     * @var Collection
     * @ORM\OneToMany(targetEntity="TimetableColumnPeriod",mappedBy="timetableColumn",cascade={"all"})
     */
    private $timetableColumnPeriods;

    /**
     * TimetableColumn constructor.
     */
    public function __construct()
    {
        $this->setTimetableColumnPeriods(new ArrayCollection());
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
     * @return TimetableColumn
     */
    public function setId(?string $id): TimetableColumn
    {
        $this->id = $id;
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
     * @return TimetableColumn
     */
    public function setName(?string $name): TimetableColumn
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
     * @return TimetableColumn
     */
    public function setAbbreviation(?string $abbreviation): TimetableColumn
    {
        $this->abbreviation = $abbreviation;
        return $this;
    }

    /**
     * getTimetableColumnPeriods
     * @return Collection
     */
    public function getTimetableColumnPeriods(): Collection
    {
        if (empty($this->timetableColumnPeriods))
            $this->timetableColumnPeriods = new ArrayCollection();

        if ($this->timetableColumnPeriods instanceof PersistentCollection)
            $this->timetableColumnPeriods->initialize();

        $iterator = $this->timetableColumnPeriods->getIterator();
        $iterator->uasort(
            function ($a, $b) {
                return ($a->getTimeStart()->format('His') < $b->getTimeStart()->format('His')) ? -1 : 1;
            }
        );

        $this->timetableColumnPeriods = new ArrayCollection(iterator_to_array($iterator, false));

        return $this->timetableColumnPeriods;
    }

    /**
     * @param Collection $timetableColumnPeriods
     * @return TimetableColumn
     */
    public function setTimetableColumnPeriods(Collection $timetableColumnPeriods): TimetableColumn
    {
        $this->timetableColumnPeriods = $timetableColumnPeriods;
        return $this;
    }

    /**
     * addTimetableColumnPeriod
     * @param TimetableColumnPeriod $period
     * @return $this
     * 5/08/2020 10:19
     */
    public function addTimetableColumnPeriod(TimetableColumnPeriod $period, bool $reflect = true): TimetableColumn
    {
        if (!$this->getTimetableColumnPeriods()->contains($period)) {
            if ($reflect) {
                $period->setTimetableColumn($this, false);
            }
            $this->timetableColumnPeriods->add($period);
        }
        return $this;
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
            'periodCount' => $this->getTimetableColumnPeriods()->count(),
            'hasPeriods' => intval($this->getTimetableColumnPeriods()->count()) > 0,
            'canDelete' => true,
        ];
    }
}
