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
 * Time: 16:35
 */
namespace App\Modules\Timetable\Entity;

use App\Manager\AbstractEntity;
use App\Modules\School\Entity\AcademicYear;
use App\Modules\School\Entity\YearGroup;
use App\Util\StringHelper;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\PersistentCollection;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class Timetable
 * @package App\Modules\Timetable\Entity
 * @ORM\Entity(repositoryClass="App\Modules\Timetable\Repository\TimetableRepository")
 * @ORM\Table(name="Timetable",
 *     uniqueConstraints={@ORM\UniqueConstraint(name="name_academic_year",columns={"name","academic_year"}),
 *     @ORM\UniqueConstraint(name="abbreviation_academic_year",columns={"abbreviation","academic_year"})},
 *     indexes={@ORM\Index(name="academic_year",columns={"academic_year"})})
 * @UniqueEntity({"name","academicYear"})
 * @UniqueEntity({"abbreviation","academicYear"})
 */
class Timetable extends AbstractEntity
{
    CONST VERSION = '1.0.00';

    /**
     * @var string|null
     * @ORM\Id()
     * @ORM\Column(type="guid")
     * @ORM\GeneratedValue(strategy="UUID")
     */
    private ?string $id = null;

    /**
     * @var AcademicYear|null
     * @ORM\ManyToOne(targetEntity="App\Modules\School\Entity\AcademicYear")
     * @ORM\JoinColumn(name="academic_year",referencedColumnName="id",nullable=false)
     * @Assert\NotBlank()
     */
    private ?AcademicYear $academicYear;

    /**
     * @var string|null
     * @ORM\Column(length=30)
     * @Assert\NotBlank()
     * @Assert\Length(max=30)
     */
    private ?string $name;

    /**
     * @var string|null
     * @ORM\Column(length=12, name="abbreviation")
     * @Assert\NotBlank()
     * @Assert\Length(max=12)
     */
    private ?string $abbreviation;

    /**
     * @var string
     * @ORM\Column(length=32,options={"default": "Day Of The Week"})
     * @Assert\Choice(callback="getDisplayModeList")
     */
    private string $displayMode = 'Day Of The Week';

    /**
     * @var array
     */
    private static array $displayModeList = [
        'Day Of The Week',
        'Timetable Day Abbreviation'
    ];

    /**
     * @var Collection|YearGroup[]|null
     * @ORM\ManyToMany(targetEntity="App\Modules\School\Entity\YearGroup")
     * @ORM\JoinTable(name="TimetableYearGroup",
     *      joinColumns={@ORM\JoinColumn(name="timetable",referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="year_group",referencedColumnName="id")}
     *      )
     * @ORM\OrderBy({"sortOrder" = "ASC"})
     */
    private ?Collection $yearGroups;

    /**
     * @var bool|null
     * @ORM\Column(type="boolean",options={"default": 1})
     */
    private bool $active = true;

    /**
     * @var Collection|TimetableDay[]
     * @ORM\OneToMany(targetEntity="TimetableDay",mappedBy="timetable",orphanRemoval=true)
     */
    private $timetableDays;

    /**
     * Timetable constructor.
     * @param AcademicYear|null $academicYear
     */
    public function __construct(?AcademicYear $academicYear = null)
    {
         $this->setYearGroups(new ArrayCollection())
             ->setAcademicYear($academicYear)
             ->setTimetableDays(new ArrayCollection());
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
     * @return Timetable
     */
    public function setId(?string $id): Timetable
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return AcademicYear|null
     */
    public function getAcademicYear(): ?AcademicYear
    {
        return $this->academicYear;
    }

    /**
     * @param AcademicYear|null $academicYear
     * @return Timetable
     */
    public function setAcademicYear(?AcademicYear $academicYear): Timetable
    {
        $this->academicYear = $academicYear;
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
     * @return Timetable
     */
    public function setName(?string $name): Timetable
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
     * @return Timetable
     */
    public function setAbbreviation(?string $abbreviation): Timetable
    {
        $this->abbreviation = $abbreviation;
        return $this;
    }

    /**
     * getDisplayMode
     *
     * 25/08/2020 08:53
     * @return string
     */
    public function getDisplayMode(): string
    {
        return $this->displayMode = in_array($this->displayMode, self::getDisplayModeList()) ? $this->displayMode : 'Day Of The Week';
    }

    /**
     * setDisplayMode
     *
     * 25/08/2020 08:52
     * @param string $displayMode
     * @return $this
     */
    public function setDisplayMode(string $displayMode): Timetable
    {
        $this->displayMode = in_array($displayMode, self::getDisplayModeList()) ? $displayMode : 'Day Of The Week';
        return $this;
    }

    /**
     * @return YearGroup[]|Collection
     */
    public function getYearGroups()
    {
        if (null === $this->yearGroups) $this->yearGroups = new ArrayCollection();

        if ($this->yearGroups instanceof PersistentCollection) $this->yearGroups->initialize();

        return $this->yearGroups;
    }

    /**
     * @param Collection|null $yearGroups
     * @return Timetable
     */
    public function setYearGroups(?Collection $yearGroups): Timetable
    {
        $this->yearGroups = $yearGroups;
        return $this;
    }

    /**
     * isActive
     *
     * 25/08/2020 08:52
     * @return bool
     */
    public function isActive(): bool
    {
        return (bool)$this->active;
    }

    /**
     * setActive
     *
     * 25/08/2020 08:52
     * @param bool|null $active
     * @return $this
     */
    public function setActive(?bool $active): Timetable
    {
        $this->active = (bool)$active;
        return $this;
    }

    /**
     * getDisplayModeList
     *
     * 25/08/2020 08:52
     * @return array|string[]
     */
    public static function getDisplayModeList(): array
    {
        return self::$displayModeList;
    }

    /**
     * getTimetableDays
     * @return Collection
     */
    public function getTimetableDays(): Collection
    {
        if (empty($this->timetableDays))
            $this->timetableDays = new ArrayCollection();

        if ($this->timetableDays instanceof PersistentCollection)
            $this->timetableDays->initialize();

        return $this->timetableDays;
    }

    /**
     * @param Collection|TimetableDay[] $timetableDays
     * @return Timetable
     */
    public function setTimetableDays(Collection $timetableDays): Timetable
    {
        $this->timetableDays = $timetableDays;
        return $this;
    }

    /**
     * __toString
     * @return string
     */
    public function __toString(): string
    {
        return $this->getName() !== null ? $this->getAcademicYear()->__toString() . ': ' . $this->getName() . ' ('.$this->getAbbreviation().')' : $this->getId();
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
            'year_groups' => $this->getYearGroupsNames(),
            'active' => StringHelper::getYesNo($this->isActive()),
            'canDelete' => $this->canDelete(),
        ];
    }

    /**
     * getYearGroupsNames
     * @return string
     * 3/08/2020 14:36
     */
    public function getYearGroupsNames(): string
    {
        $result = [];
        foreach($this->getYearGroups() as $yg) {
            $result[] = $yg->getAbbreviation();
        }
        return implode(',', $result);
    }

    /**
     * canDelete
     * @return bool
     * 3/08/2020 16:19
     */
    public function canDelete(): bool
    {
        return $this->getTimetableDays()->count() === 0;
    }
}
