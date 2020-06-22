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

/**
 * Class TTColumn
 * @package App\Modules\Timetable\Entity
 * @ORM\Entity(repositoryClass="App\Modules\Timetable\Repository\TTColumnRepository")
 * @ORM\Table(name="TTColumn")
 */
class TTColumn extends AbstractEntity
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
     * @ORM\OneToMany(targetEntity="TTColumnRow", mappedBy="TTColumn")
     */
    private $timetableColumnRows;

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
     * @return TTColumn
     */
    public function setId(?string $id): TTColumn
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
     * @return TTColumn
     */
    public function setName(?string $name): TTColumn
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
     * @return TTColumn
     */
    public function setAbbreviation(?string $abbreviation): TTColumn
    {
        $this->abbreviation = $abbreviation;
        return $this;
    }

    /**
     * getTimetableColumnRows
     * @return Collection
     */
    public function getTimetableColumnRows(): Collection
    {
        if (empty($this->timetableColumnRows))
            $this->timetableColumnRows = new ArrayCollection();

        if ($this->timetableColumnRows instanceof PersistentCollection)
            $this->timetableColumnRows->initialize();

        $iterator = $this->timetableColumnRows->getIterator();
        $iterator->uasort(
            function ($a, $b) {
                return ($a->getTimeStart()->format('His') < $b->getTimeStart()->format('His')) ? -1 : 1;
            }
        );

        $this->timetableColumnRows = new ArrayCollection(iterator_to_array($iterator, false));


        return $this->timetableColumnRows;
    }

    /**
     * @param Collection $timetableColumnRows
     * @return TTColumn
     */
    public function setTimetableColumnRows(Collection $timetableColumnRows): TTColumn
    {
        $this->timetableColumnRows = $timetableColumnRows;
        return $this;
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
        return ["CREATE TABLE  `__prefix__TTColumn` (
                    `id` CHAR(36) NOT NULL COMMENT '(DC2Type:guid)',
                    `name` CHAR(30) NOT NULL,
                    `abbreviation` CHAR(12) NOT NULL,
                    PRIMARY KEY (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;"];
    }

    public function foreignConstraints(): string
    {
        return '';
    }

    public static function getVersion(): string
    {
        return self::VERSION;
    }
}
