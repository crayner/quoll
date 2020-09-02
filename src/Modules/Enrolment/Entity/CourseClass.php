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
namespace App\Modules\Enrolment\Entity;

use App\Manager\AbstractEntity;
use App\Modules\Curriculum\Entity\Course;
use App\Modules\Assess\Entity\Scale;
use App\Modules\Timetable\Entity\TimetablePeriodClass;
use App\Util\StringHelper;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\PersistentCollection;
use Exception;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class CourseClass
 * @package App\Modules\Enrolment\Entity
 * @ORM\Entity(repositoryClass="App\Modules\Enrolment\Repository\CourseClassRepository")
 * @ORM\Table(name="CourseClass",
 *     indexes={@ORM\Index(name="course", columns={"course"}),
 *     @ORM\Index(name="scale",columns={"scale"})},
 *     uniqueConstraints={@ORM\UniqueConstraint(name="name_course",columns={ "name", "course"}),
 *     @ORM\UniqueConstraint(name="abbreviation_course",columns={ "abbreviation", "course"})})
 * @UniqueEntity({"name","course"})
 * @UniqueEntity({"abbreviation","course"})
 */
class CourseClass extends AbstractEntity
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
     * @var Course|null
     * @ORM\ManyToOne(targetEntity="App\Modules\Curriculum\Entity\Course",inversedBy="courseClasses")
     * @ORM\JoinColumn(name="course",referencedColumnName="id",nullable=false)
     */
    private ?Course $course;

    /**
     * @var string
     * @ORM\Column(length=30)
     * @Assert\NotBlank()
     */
    private string $name;

    /**
     * @var string
     * @ORM\Column(length=8)
     * @Assert\NotBlank()
     */
    private string $abbreviation;

    /**
     * @var bool
     * @ORM\Column(type="boolean", options={"default": 1})
     */
    private bool $reportable = true;

    /**
     * @var bool
     * @ORM\Column(type="boolean", options={"default": 1})
     */
    private bool $attendance = true;

    /**
     * @var Scale
     * @ORM\ManyToOne(targetEntity="App\Modules\Assess\Entity\Scale")
     * @ORM\JoinColumn(name="scale", referencedColumnName="id")
     */
    private Scale $scale;

    /**
     * @var Collection|CourseClassPerson[]|null
     * @ORM\OneToMany(targetEntity="CourseClassPerson", mappedBy="courseClass")
     */
    private Collection $courseClassPeople;

    /**
     * @var Collection|TimetablePeriodClass[]|null
     * @ORM\OneToMany(targetEntity="App\Modules\Timetable\Entity\TimetablePeriodClass",mappedBy="courseClass")
     */
    private Collection $periodClasses;

    /**
     * CourseClass constructor.
     * @param Course|null $course
     */
    public function __construct(?Course $course = null)
    {
        $this->setPeriodClasses(new ArrayCollection())
            ->setCourse($course)
            ->setCourseClassPeople(new ArrayCollection());
    }

    /**
     * @return string|null
     */
    public function getId(): ?string
    {
        return isset($this->id) ? $this->id : null;
    }

    /**
     * Id.
     *
     * @param string $id
     * @return CourseClass
     */
    public function setId(string $id): CourseClass
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return Course|null
     */
    public function getCourse(): ?Course
    {
        return $this->course;
    }

    /**
     * @param Course|null $course
     * @return CourseClass
     */
    public function setCourse(?Course $course): CourseClass
    {
        $this->course = $course;
        return $this;
    }

    /**
     * getName
     * @param bool $withCourse
     * @return string|null
     */
    public function getName(bool $withCourse = false): ?string
    {
        if ($withCourse)
            return $this->getCourse()->getName() . '.' . $this->name;
        return $this->name;
    }

    /**
     * @param string $name
     * @return CourseClass
     */
    public function setName(string $name): CourseClass
    {
        $this->name = $name;
        return $this;
    }

    /**
     * getAbbreviation
     * @param bool $withCourse
     * @return string|null
     */
    public function getAbbreviation(bool $withCourse = false): ?string
    {
        if ($withCourse)
            return $this->getCourse()->getAbbreviation() . '.' . $this->abbreviation;
        return $this->abbreviation;
    }

    /**
     * @param string $abbreviation
     * @return CourseClass
     */
    public function setAbbreviation(string $abbreviation): CourseClass
    {
        $this->abbreviation = $abbreviation;
        return $this;
    }

    /**
     * @return bool
     */
    public function isReportable(): bool
    {
        return (bool)$this->reportable;
    }

    /**
     * @param bool $reportable
     * @return CourseClass
     */
    public function setReportable(bool $reportable): CourseClass
    {
        $this->reportable = $reportable;
        return $this;
    }

    /**
     * @return bool
     */
    public function isAttendance(): bool
    {
        return (bool)$this->attendance;
    }

    /**
     * @param bool $attendance
     * @return CourseClass
     */
    public function setAttendance(bool $attendance): CourseClass
    {
        $this->attendance = $attendance;
        return $this;
    }

    /**
     * @return Scale|null
     */
    public function getScale(): ?Scale
    {
        return $this->scale;
    }

    /**
     * @param Scale $scale
     * @return CourseClass
     */
    public function setScale(Scale $scale): CourseClass
    {
        $this->scale = $scale;
        return $this;
    }

    /**
     * getCourseClassPeople
     * @return Collection|null
     */
    public function getCourseClassPeople(): ?Collection
    {
        if (empty($this->courseClassPeople))
            $this->courseClassPeople = new ArrayCollection();

        if ($this->courseClassPeople instanceof PersistentCollection)
            $this->courseClassPeople->initialize();

        return $this->courseClassPeople;
    }

    /**
     * @param Collection $courseClassPeople
     * @return CourseClass
     */
    public function setCourseClassPeople(Collection $courseClassPeople): CourseClass
    {
        $this->courseClassPeople = $courseClassPeople;
        return $this;
    }

    /**
     * getPeriodClasses
     * @return Collection|null
     */
    public function getPeriodClasses(): ?Collection
    {
        if (empty($this->periodClasses))
            $this->periodClasses = new ArrayCollection();

        if ($this->periodClasses instanceof PersistentCollection)
            $this->periodClasses-> initialize();

        return $this->periodClasses;
    }

    /**
     * @param Collection $periodClasses
     * @return CourseClass
     */
    public function setPeriodClasses(Collection $periodClasses): CourseClass
    {
        $this->periodClasses = $periodClasses;
        return $this;
    }

    /**
     * @var Collection
     */
    private Collection $students;

    /**
     * getStudents
     *
     * 31/08/2020 14:01
     * @return Collection
     */
    public function getStudents(): Collection
    {
        if (!isset($this->students) || !$this->students instanceof Collection || $this->students->count() === 0)
        {
            $this->students = $this->getCourseClassPeople()->filter(function($entry) {
                return $entry->getRole() === 'Student';
            });
        }

        try {
            $iterator = $this->students->getIterator();
            $iterator->uasort(
                function ($a, $b) {
                    return $a->getPerson()->getFullName() < $b->getPerson()->getFullName() ? -1 : 1 ;
                }
            );
            $this->students  = new ArrayCollection(iterator_to_array($iterator, false));
        } catch (Exception $e) {
        }



        return $this->students;
    }

    /**
     * @var Collection
     */
    private Collection $staff;

    /**
     * getStaff
     *
     * 31/08/2020 14:01
     * @return Collection
     * @throws Exception
     */
    public function getStaff(): Collection
    {
        if (!isset($this->staff) || !$this->staff instanceof Collection || $this->staff->count() === 0)
        {
            $this->staff = $this->getCourseClassPeople()->filter(function($entry) {
                return in_array($entry->getRole(), ['Teacher','Assistant','Technician']);
            });
        }

        $iterator = $this->staff->getIterator();
        $iterator->uasort(
            function ($a, $b) {
                return $a->getPerson()->getFullName() < $b->getPerson()->getFullName() ? -1 : 1 ;
            }
        );

        $this->staff  = new ArrayCollection(iterator_to_array($iterator, false));

        return $this->staff;
    }

    /**
     * courseClassName
     * @param bool $short
     * @return string
     */
    public function courseClassName(bool $short = false): string
    {
        return $short ? $this->getCourse()->getAbbreviation() . '.' . $this->getAbbreviation() : $this->getCourse()->getName() . '.' . $this->getName();
    }

    /**
     * __toString
     * @return string
     */
    public function __toString(): string
    {
        return $this->courseClassName(true);
    }

    /**
     * toArray
     * @param string|null $name
     * @return array
     */
    public function toArray(?string $name = null): array
    {
        if ($name === 'CourseClassPagination') return [
            'id' => $this->getId(),
            'name' => $this->getName(),
            'abbreviation' => $this->getAbbreviation(),
            'reportable' => StringHelper::getYesNo($this->isReportable()),
            'participantCount' => $this->getStudents()->count() ?: '0',
            'course_id' => $this->getCourse()->getId(),
        ];
        return [];
    }
}
