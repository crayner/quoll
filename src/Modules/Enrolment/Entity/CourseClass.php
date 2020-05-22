<?php
/**
 * Created by PhpStorm.
 *
* Quoll
 *
 * (c) 2018 Craig Rayner <craig@craigrayner.com>
 *
 * User: craig
 * Date: 23/11/2018
 * Time: 15:27
 */
namespace App\Modules\Enrolment\Entity;

use App\Manager\EntityInterface;
use App\Manager\Traits\BooleanList;
use App\Modules\School\Entity\Scale;
use App\Modules\Timetable\Entity\TTDayRowClass;
use App\Util\EntityHelper;
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
 *     uniqueConstraints={@ORM\UniqueConstraint(name="nameCourse",columns={ "name", "course"}),
 *     @ORM\UniqueConstraint(name="nameShortCourse",columns={ "nameShort", "course"})})
 * @UniqueEntity({"name","course"})
 * @UniqueEntity({"nameShort","course"})
 */
class CourseClass implements EntityInterface
{
    CONST VERSION = '20200401';

    use BooleanList;

    /**
     * @var string|null
     * @ORM\Id()
     * @ORM\Column(type="guid")
     * @ORM\GeneratedValue(strategy="UUID")
     */
    private $id;

    /**
     * @var Course|null
     * @ORM\ManyToOne(targetEntity="Course", inversedBy="courseClasses")
     * @ORM\JoinColumn(name="course", referencedColumnName="id", nullable=true)
     */
    private $course;

    /**
     * @var string|null
     * @ORM\Column(length=30)
     * @Assert\NotBlank()
     */
    private $name;

    /**
     * @var string|null
     * @ORM\Column(length=8, name="nameShort")
     * @Assert\NotBlank()
     */
    private $nameShort;

    /**
     * @var string|null
     * @ORM\Column(length=1, options={"default": "Y"})
     * @Assert\Choice(callback="getBooleanList")
     */
    private $reportable = 'Y';

    /**
     * @var string|null
     * @ORM\Column(length=1, options={"default": "Y"})
     * @Assert\Choice({"Y","N"})
     */
    private $attendance = 'Y';

    /**
     * @var Scale|null
     * @ORM\ManyToOne(targetEntity="App\Modules\School\Entity\Scale")
     * @ORM\JoinColumn(name="scale", referencedColumnName="id")
     */
    private $scale;

    /**
     * @var Collection|CourseClassPerson[]|null
     * @ORM\OneToMany(targetEntity="CourseClassPerson", mappedBy="courseClass")
     */
    private $courseClassPeople;

    /**
     * @var Collection|TTDayRowClass[]|null
     * @ORM\OneToMany(targetEntity="App\Modules\Timetable\Entity\TTDayRowClass", mappedBy="courseClass")
     */
    private $TTDayRowClasses;

    /**
     * CourseClass constructor.
     */
    public function __construct()
    {
        $this->TTDayRowClasses = new ArrayCollection();
        $this->courseClassPeople = new ArrayCollection();
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
     * @return CourseClass
     */
    public function setId(?string $id): CourseClass
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
     * @param string|null $name
     * @return CourseClass
     */
    public function setName(?string $name): CourseClass
    {
        $this->name = $name;
        return $this;
    }

    /**
     * getNameShort
     * @param bool $withCourse
     * @return string|null
     */
    public function getNameShort(bool $withCourse = false): ?string
    {
        if ($withCourse)
            return $this->getCourse()->getNameShort() . '.' . $this->nameShort;
        return $this->nameShort;
    }

    /**
     * @param string|null $nameShort
     * @return CourseClass
     */
    public function setNameShort(?string $nameShort): CourseClass
    {
        $this->nameShort = $nameShort;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getReportable(): ?string
    {
        return $this->reportable;
    }

    /**
     * @param string|null $reportable
     * @return CourseClass
     */
    public function setReportable(?string $reportable): CourseClass
    {
        $this->reportable = self::checkBoolean($reportable, 'Y');
        return $this;
    }

    /**
     * @return boolean
     */
    public function isAttendance(): bool
    {
        return $this->getAttendance() === 'Y';
    }

    /**
     * @return string
     */
    public function getAttendance(): string
    {
        return $this->attendance = self::checkBoolean($this->attendance);
    }

    /**
     * @param string|null $attendance
     * @return CourseClass
     */
    public function setAttendance(?string $attendance): CourseClass
    {
        $this->attendance = self::checkBoolean($attendance, 'Y');
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
     * @param Scale|null $scale
     * @return CourseClass
     */
    public function setScale(?Scale $scale): CourseClass
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
     * @param Collection|null $courseClassPeople
     * @return CourseClass
     */
    public function setCourseClassPeople(?Collection $courseClassPeople): CourseClass
    {
        $this->courseClassPeople = $courseClassPeople;
        return $this;
    }

    /**
     * getTTDayRowClasses
     * @return Collection|null
     */
    public function getTTDayRowClasses(): ?Collection
    {
        if (empty($this->TTDayRowClasses))
            $this->TTDayRowClasses = new ArrayCollection();

        if ($this->TTDayRowClasses instanceof PersistentCollection)
            $this->TTDayRowClasses-> initialize();

        return $this->TTDayRowClasses;
    }

    /**
     * @param Collection|null $TTDayRowClasses
     * @return CourseClass
     */
    public function setTTDayRowClasses(?Collection $TTDayRowClasses): CourseClass
    {
        $this->TTDayRowClasses = $TTDayRowClasses;
        return $this;
    }

    /**
     * @var Collection
     */
    private $students;

    /**
     * getStudents
     * @return Collection
     */
    public function getStudents(): Collection
    {
        if (! $this->students instanceof Collection || $this->students->count() === 0)
        {
            $this->students = $this->getCourseClassPeople()->filter(function($entry) {
                return $entry->getRole() === 'Student';
            });
        }

        $iterator = $this->students->getIterator();
        $iterator->uasort(
            function ($a, $b) {
                return $a->getPerson()->getFullName() < $b->getPerson()->getFullName() ? -1 : 1 ;
            }
        );

        $this->students  = new ArrayCollection(iterator_to_array($iterator, false));


        return $this->students;
    }

    /**
     * @var Collection
     */
    private $staff;

    /**
     * getStudents
     * @return Collection
     */
    public function getStaff(): Collection
    {
        if (! $this->staff instanceof Collection || $this->staff->count() === 0)
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
     * __toArray
     * @param array $ignore
     * @return array
     */
    public function __toArray(array $ignore = []): array
    {
        return EntityHelper::__toArray(CourseClass::class, $this, $ignore);
    }

    /**
     * courseClassName
     * @param bool $short
     * @return string
     */
    public function courseClassName(bool $short = false): string
    {
        return $short ? $this->getCourse()->getNameShort() . '.' . $this->getNameShort() : $this->getCourse()->getName() . '.' . $this->getName();
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
        return [];
    }

    public function create(): array
    {
        return ["CREATE TABLE `__prefix__CourseClass` (
                    `id` CHAR(36) NOT NULL COMMENT '(DC2Type:guid)',
                    `name` CHAR(30) NOT NULL,
                    `nameShort` CHAR(8) NOT NULL,
                    `reportable` CHAR(1) NOT NULL DEFAULT 'Y',
                    `attendance` CHAR(1) NOT NULL DEFAULT 'Y',
                    `course` CHAR(36) DEFAULT NULL,
                    `scale` CHAR(36) DEFAULT NULL,
                    PRIMARY KEY (`id`),
                    UNIQUE KEY `nameCourse` (`name`,`course`),
                    UNIQUE KEY `nameShortCourse` (`nameShort`,`course`),
                    KEY `scale` (`scale`),
                    KEY `course` (`course`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;"];
    }

    public function foreignConstraints(): string
    {
        return 'ALTER TABLE `__prefix__CourseClass`
                    ADD CONSTRAINT FOREIGN KEY (`course`) REFERENCES `__prefix__Course` (`id`),
                    ADD CONSTRAINT FOREIGN KEY (`scale`) REFERENCES `__prefix__Scale` (`id`);';
    }

    public function coreData(): string
    {
        return '';
    }

    public static function getVersion(): string
    {
        return self::VERSION;
    }
}
