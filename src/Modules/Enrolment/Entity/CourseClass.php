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
use App\Modules\Staff\Entity\Staff;
use App\Modules\Timetable\Entity\TimetablePeriodClass;
use App\Util\CacheHelper;
use App\Util\StringHelper;
use App\Util\TranslationHelper;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\PersistentCollection;
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
 * @ORM\HasLifecycleCallbacks()
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
    private ?string $id;

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
     * @var Collection|CourseClassStudent[]|null
     * @ORM\OneToMany(targetEntity="CourseClassStudent", mappedBy="courseClass")
     */
    private Collection $students;

    /**
     * @var Collection|TimetablePeriodClass[]|null
     * @ORM\OneToMany(targetEntity="App\Modules\Timetable\Entity\TimetablePeriodClass",mappedBy="courseClass")
     */
    private Collection $periodClasses;

    /**
     * @var Collection|CourseClassTutor[]|null
     * @ORM\OneToMany(targetEntity="App\Modules\Enrolment\Entity\CourseClassTutor",cascade={"persist","remove"},mappedBy="courseClass")
     * @ORM\OrderBy({"sortOrder" = "DESC"})
     */
    private ?Collection $tutors;

    /**
     * CourseClass constructor.
     * @param Course|null $course
     */
    public function __construct(?Course $course = null)
    {
        $this->setPeriodClasses(new ArrayCollection())
            ->setCourse($course)
            ->setTutors(new ArrayCollection())
            ->setStudents(new ArrayCollection());
    }

    /**
     * @return string|null
     */
    public function getId(): ?string
    {
        return $this->id = isset($this->id) ? $this->id : null;
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
        return isset($this->course) ? $this->course : null;
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
     * getStudents
     *
     * 5/10/2020 15:05
     * @return Collection|null
     */
    public function getStudents(): ?Collection
    {
        if (empty($this->students))
            $this->students = new ArrayCollection();

        if ($this->students instanceof PersistentCollection)
            $this->students->initialize();

        return $this->students;
    }

    /**
     * setStudents
     *
     * 5/10/2020 15:05
     * @param Collection $students
     * @return $this
     */
    public function setStudents(Collection $students): CourseClass
    {
        $this->students = $students;
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
     * getTutors
     *
     * 16/09/2020 10:49
     * @return Collection|CourseClassTutor[]|null
     */
    public function getTutors(): Collection
    {
        if (!isset($this->tutors)) $this->tutors = new ArrayCollection();

        if ($this->tutors instanceof PersistentCollection) $this->tutors->initialize();

        $iterator = $this->tutors->getIterator();

        $iterator->uasort(
            function (CourseClassTutor $a, CourseClassTutor $b) {
                return $a->getSortOrder() <= $b->getSortOrder() ? -1 : 1;
            }
        );

        $this->setTutors(new ArrayCollection(iterator_to_array($iterator, false)));

        return $this->tutors;
    }

    /**
     * @param Staff[]|Collection|null $tutors
     * @return CourseClass
     */
    public function setTutors(?Collection $tutors)
    {
        $this->tutors = $tutors;
        return $this;
    }

    /**
     * addTutor
     *
     * 16/09/2020 10:51
     * @param CourseClassTutor $tutor
     * @return CourseClass
     */
    public function addTutor(CourseClassTutor $tutor): CourseClass
    {
        if ($this->getTutors()->contains($tutor)) return $this;

        $this->tutors->add($tutor);

        return $this;
    }

    /**
     * __toString
     * @return string
     */
    public function __toString(): string
    {
        return $this->getFullName();
    }

    /**
     * toArray
     *
     * 7/10/2020 16:04
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
            'participantCount' => $this->getStudents()->count(),
            'course_id' => $this->getCourse()->getId(),
            'canDelete' => $this->canDelete(),
        ];
        return [];
    }

    /**
     * getAbbreviatedName
     *
     * 4/09/2020 09:23
     * @return string
     */
    public function getAbbreviatedName(): string
    {
        return $this->getCourse() ? $this->getCourse()->getAbbreviation() . '.' . ($this->getAbbreviation() ?: '?') : '????.' . ($this->getAbbreviation() ?: '?');
    }

    /**
     * getFullName
     *
     * 4/09/2020 09:23
     * @return string
     */
    public function getFullName(): string
    {
        return $this->getCourse() ? $this->getCourse()->getName() . '.' . ($this->getName() ?: '?') . ' (' . $this->getAbbreviatedName() . ')' : '????.' . ($this->getName() ?: '?');
    }

    /**
     * getClassNameWithCount
     *
     * 7/10/2020 16:04
     * @return string
     */
    public function getClassNameWithCount(): string
    {
        $result = $this->getAbbreviatedName();
        $result .= $this->getTutors()->first() ? ' - ' . $this->getTutors()->first()->getStaff()->getFullName('Initial') : ' - '.TranslationHelper::translate('No Teacher Assigned',[],'Enrolment');
        $result .= ' - ' . TranslationHelper::translate('count_students', ['count' => $this->getStudents()->count()], 'Enrolment');
        return $result;
    }

    /**
     * canDelete
     *
     * 7/10/2020 16:04
     * @return bool
     */
    public function canDelete(): bool
    {
        return $this->getPeriodClasses()->count() + $this->getStudents()->count() + $this->getTutors()->count() === 0;
    }

    /**
     * clearCache
     *
     * 24/09/2020 09:19
     * @ORM\PostPersist()
     * @ORM\PostUpdate()
     * @ORM\PostRemove()
     */
    public function clearCache()
    {
        CacheHelper::clearCacheValue('course_class_choices');
        CacheHelper::setCacheDirty('course_class_choices');
    }

    /**
     * getTutorNames
     *
     * 13/10/2020 16:05
     * @return array
     */
    public function getTutorNames(): array
    {
        $result = [];
        foreach ($this->getTutors() as $tutor)
        {
            $result[] = $tutor->getStaff()->getFullName('Formal');
        }
        return $result;
    }
}
