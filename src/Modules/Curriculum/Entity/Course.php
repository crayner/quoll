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
namespace App\Modules\Curriculum\Entity;

use App\Manager\AbstractEntity;
use App\Modules\Enrolment\Entity\CourseClass;
use App\Modules\School\Entity\AcademicYear;
use App\Modules\Department\Entity\Department;
use App\Modules\School\Entity\YearGroup;
use App\Modules\School\Util\AcademicYearHelper;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\PersistentCollection;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class Course
 * @package App\Modules\Curriculum\Entity
 * @ORM\Entity(repositoryClass="App\Modules\Curriculum\Repository\CourseRepository")
 * @ORM\Table(name="Course",
 *  indexes={
 *     @ORM\Index(name="academic_year",columns={"academic_year"}),
 *     @ORM\Index(name="department",columns={"department"})},
 *  uniqueConstraints={
 *     @ORM\UniqueConstraint(name="name_year",columns={"name","academic_year"}),
 *     @ORM\UniqueConstraint(name="abbreviation_year",columns={"abbreviation","academic_year"})})
 * @UniqueEntity({"name","academicYear"})
 * @UniqueEntity({"abbreviation", "academicYear"})
 */
class Course extends AbstractEntity
{
    CONST VERSION = '1.0.00';

    /**
     * @var string|null
     * @ORM\Id()
     * @ORM\Column(type="guid")
     * @ORM\GeneratedValue(strategy="UUID")
     */
    private string $id;

    /**
     * @var AcademicYear|null
     * @ORM\ManyToOne(targetEntity="App\Modules\School\Entity\AcademicYear")
     * @ORM\JoinColumn(name="academic_year",referencedColumnName="id",nullable=false)
     */
    private ?AcademicYear $academicYear;

    /**
     * @var Department|null
     * @ORM\ManyToOne(targetEntity="App\Modules\Department\Entity\Department")
     * @ORM\JoinColumn(name="department",referencedColumnName="id",nullable=true)
     */
    private ?Department $department;

    /**
     * @var string|null
     * @ORM\Column(length=60)
     * @Assert\NotBlank
     */
    private string $name;

    /**
     * @var string|null
     * @ORM\Column(length=12)
     * @Assert\NotBlank
     */
    private string $abbreviation;

    /**
     * @var string|null
     * @ORM\Column(type="text", nullable=true)
     */
    private ?string $description;

    /**
     * @var bool
     * @ORM\Column(type="boolean", options={"comment": "Should this course be included in curriculum maps and other summaries?", "default": 1})
     */
    private bool $map = true;

    /**
     * @var Collection|YearGroup[]|null
     * @ORM\ManyToMany(targetEntity="App\Modules\School\Entity\YearGroup")
     * @ORM\JoinTable(name="CourseYearGroup",
     *      joinColumns={@ORM\JoinColumn(name="course",referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="year_group",referencedColumnName="id")}
     *  )
     * @Assert\Count(minMessage = "You must specify at least one year group.", min = 1)
     */
    private Collection $yearGroups;

    /**
     * @var integer|null
     * @ORM\Column(type="smallint",name="order_by",nullable=true)
     */
    private ?int $orderBy;

    /**
     * @var Collection|CourseClass[]|null
     * @ORM\OneToMany(targetEntity="App\Modules\Enrolment\Entity\CourseClass", mappedBy="course")
     * @ORM\OrderBy({"name" = "ASC"})
     */
    private ?Collection $courseClasses;

    /**
     * Course constructor.
     */
    public function __construct()
    {
        $this->setCourseClasses(new ArrayCollection())
            ->setAcademicYear(AcademicYearHelper::getCurrentAcademicYear(true))
        ;
    }

    /**
     * getId
     *
     * 31/08/2020 10:27
     * @return string|null
     */
    public function getId(): ?string
    {
        return isset($this->id) ? $this->id : null;
    }

    /**
     * setId
     *
     * 31/08/2020 10:29
     * @param string $id
     * @return $this
     */
    public function setId(string $id): Course
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
     * @return Course
     */
    public function setAcademicYear(?AcademicYear $academicYear): Course
    {
        $this->academicYear = $academicYear;
        return $this;
    }

    /**
     * @return Department|null
     */
    public function getDepartment(): ?Department
    {
        return $this->department;
    }

    /**
     * @param Department|null $department
     * @return Course
     */
    public function setDepartment(?Department $department): Course
    {
        $this->department = $department;
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
     * setName
     *
     * 31/08/2020 10:29
     * @param string $name
     * @return $this
     */
    public function setName(string $name): Course
    {
        $this->name = $name;
        return $this;
    }

    /**
     * getAbbreviation
     *
     * 31/08/2020 11:27
     * @return string|null
     */
    public function getAbbreviation(): ?string
    {
        return isset($this->abbreviation) ? $this->abbreviation : null;
    }

    /**
     * setAbbreviation
     *
     * 31/08/2020 10:30
     * @param string $abbreviation
     * @return $this
     */
    public function setAbbreviation(string $abbreviation): Course
    {
        $this->abbreviation = $abbreviation;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @param string|null $description
     * @return Course
     */
    public function setDescription(?string $description): Course
    {
        $this->description = $description;
        return $this;
    }

    /**
     * getMap
     *
     * 31/08/2020 09:53
     * @return bool
     */
    public function getMap(): bool
    {
        return (bool) $this->map;
    }

    /**
     * setMap
     *
     * 31/08/2020 09:53
     * @param bool|null $map
     * @return Course
     */
    public function setMap(?bool $map): Course
    {
        $this->map = (bool) $map;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getOrderBy(): ?int
    {
        return $this->orderBy;
    }

    /**
     * @param int|null $orderBy
     * @return Course
     */
    public function setOrderBy(?int $orderBy): Course
    {
        $this->orderBy = $orderBy;
        return $this;
    }

    /**
     * getYearGroups
     *
     * 31/08/2020 10:22
     * @return Collection
     */
    public function getYearGroups(): Collection
    {
        if (!isset($this->yearGroups) || is_null($this->yearGroups)) $this->yearGroups = new ArrayCollection();

        if ($this->yearGroups instanceof PersistentCollection) $this->yearGroups->initialize();

        return $this->yearGroups;
    }

    /**
     * setYearGroups
     *
     * 31/08/2020 10:30
     * @param Collection $yearGroups
     * @return $this
     */
    public function setYearGroups(Collection $yearGroups): Course
    {
        $this->yearGroups = $yearGroups;
        return $this;
    }

    /**
     * addYearGroup
     *
     * 31/08/2020 10:26
     * @param YearGroup $yearGroup
     * @return Course
     */
    public function addYearGroup(YearGroup $yearGroup): Course
    {
        if ($yearGroup === null || $this->getYearGroups()->contains($yearGroup)) return $this;

        $this->yearGroups->add($yearGroup);

        return $this;
    }

    /**
     * getCourseClasses
     * @return Collection
     */
    public function getCourseClasses(): Collection
    {
        if (empty($this->courseClasses))
            $this->courseClasses = new ArrayCollection();

        if ($this->courseClasses instanceof PersistentCollection)
            $this->courseClasses->initialize();

        return $this->courseClasses;
    }

    /**
     * @param Collection|null $courseClasses
     * @return Course
     */
    public function setCourseClasses(?Collection $courseClasses): Course
    {
        $this->courseClasses = $courseClasses ?: new ArrayCollection();
        return $this;
    }

    /**
     * __toArray
     * @param array $ignore
     * @return array
     */
    public function __toArray(array $ignore = []): array
    {
        return [];
    }

    /**
     * __toString
     * @return string
     */
    public function __toString(): string
    {
        return $this->getName() . ' ('. $this->getAbbreviation(). ')';
    }

    /**
     * toArray
     * @param string|null $name
     * @return array
     */
    public function toArray(?string $name = null): array
    {
        if ($name === 'CoursePagination') return [
            'name' => $this->getName(),
            'id' => $this->getId(),
            'abbreviation' => $this->getAbbreviation(),
            'area' => $this->getDepartment()->getName(),
            'yearGroups' => $this->getYearGroupAbbreviations(),
            'classCount' => $this->getCourseClasses()->count() ?: '0',
            'canDelete' => $this->canDelete(),
        ];
        return [];
    }

    /**
     * getYearGroupAbbreviations
     *
     * 31/08/2020 12:07
     * @return string
     */
    public function getYearGroupAbbreviations(): string
    {
        $result = [];
        foreach ($this->getYearGroups() as $yg)  $result[] = $yg->getAbbreviation();

        return implode(',', $result);
    }

    /**
     * canDelete
     *
     * 31/08/2020 13:45
     * @return bool
     */
    public function canDelete(): bool
    {
        return $this->getCourseClasses()->count() === 0;
    }
}
