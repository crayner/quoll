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
 * Time: 17:00
 */
namespace App\Modules\Timetable\Entity;

use App\Manager\EntityInterface;
use App\Modules\Enrolment\Entity\CourseClass;
use App\Modules\School\Entity\Facility;
use App\Util\EntityHelper;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class TTDayRowClass
 * @package App\Modules\Timetable\Entity
 * @ORM\Entity(repositoryClass="App\Modules\Timetable\Repository\TTDayRowClassRepository")
 * @ORM\Table(name="TTDayRowClass", indexes={
 *     @ORM\Index(name="course_class", columns={"course_class"}),
 *     @ORM\Index(name="facility", columns={"facility"}),
 *     @ORM\Index(name="timetable_column_row", columns={"timetable_column_row"}),
 *     @ORM\Index(name="timetable_day", columns={"timetable_day"})
 * })
 * @ORM\HasLifecycleCallbacks()
 */
class TTDayRowClass implements EntityInterface
{
    CONST VERSION = '20200401';

    /**
     * @var string|null
     * @ORM\Id()
     * @ORM\Column(type="guid")
     * @ORM\GeneratedValue(strategy="UUID")
     */
    private $id;

    /**
     * @var TTColumnRow|null
     * @ORM\ManyToOne(targetEntity="TTColumnRow", inversedBy="TTDayRowClasses")
     * @ORM\JoinColumn(name="timetable_column_row", referencedColumnName="id", nullable=false)
     */
    private $TTColumnRow;

    /**
     * @var TTDay|null
     * @ORM\ManyToOne(targetEntity="TTDay", inversedBy="TTDayRowClasses")
     * @ORM\JoinColumn(name="timetable_day", referencedColumnName="id", nullable=false)
     */
    private $TTDay;

    /**
     * @var CourseClass|null
     * @ORM\ManyToOne(targetEntity="App\Modules\Enrolment\Entity\CourseClass", inversedBy="TTDayRowClasses")
     * @ORM\JoinColumn(name="course_class", referencedColumnName="id", nullable=false)
     */
    private $courseClass;

    /**
     * @var Facility|null
     * @ORM\ManyToOne(targetEntity="App\Modules\School\Entity\Facility")
     * @ORM\JoinColumn(name="facility", referencedColumnName="id")
     */
    private $facility;

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
     * @return TTDayRowClass
     */
    public function setId(?string $id): TTDayRowClass
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return TTColumnRow|null
     */
    public function getTTColumnRow(): ?TTColumnRow
    {
        return $this->TTColumnRow;
    }

    /**
     * @param TTColumnRow|null $TTColumnRow
     * @return TTDayRowClass
     */
    public function setTTColumnRow(?TTColumnRow $TTColumnRow): TTDayRowClass
    {
        $this->TTColumnRow = $TTColumnRow;
        return $this;
    }

    /**
     * @return TTDay|null
     */
    public function getTTDay(): ?TTDay
    {
        return $this->TTDay;
    }

    /**
     * @param TTDay|null $TTDay
     * @return TTDayRowClass
     */
    public function setTTDay(?TTDay $TTDay): TTDayRowClass
    {
        $this->TTDay = $TTDay;
        return $this;
    }

    /**
     * @return CourseClass|null
     */
    public function getCourseClass(): ?CourseClass
    {
        return $this->courseClass;
    }

    /**
     * @param CourseClass|null $courseClass
     * @return TTDayRowClass
     */
    public function setCourseClass(?CourseClass $courseClass): TTDayRowClass
    {
        $this->courseClass = $courseClass;
        return $this;
    }

    /**
     * @return Facility|null
     */
    public function getFacility(): ?Facility
    {
        return $this->facility;
    }

    /**
     * Facility.
     *
     * @param Facility|null $facility
     * @return TTDayRowClass
     */
    public function setFacility(?Facility $facility): TTDayRowClass
    {
        $this->facility = $facility;
        return $this;
    }

    /**
     * __toArray
     * @param array $ignore
     * @return array
     */
    public function __toArray(array $ignore = []): array
    {
        return EntityHelper::__toArray(TTDayRowClass::class, $this, $ignore);
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
        return "CREATE TABLE `__prefix__TTDayRowClass` (
                    `id` CHAR(36) NOT NULL COMMENT '(DC2Type:guid)',
                    `timetable_column_row` CHAR(36) DEFAULT NULL,
                    `timetable_day` CHAR(36) DEFAULT NULL,
                    `course_class` CHAR(36) DEFAULT NULL,
                    `facility` CHAR(36) DEFAULT NULL,
                    PRIMARY KEY (`id`),
                    KEY `facility` (`facility`),
                    KEY `timetable_day` (`timetable_day`),
                    KEY `timetable_column_row` (`timetable_column_row`),
                    KEY `course_class` (`course_class`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=ut8mb4_unicode_ci;";
    }

    public function foreignConstraints(): string
    {
        return "ALTER TABLE `__prefix__TTDayRowClass`
                    ADD CONSTRAINT FOREIGN KEY (`course_class`) REFERENCES `__prefix__CourseClass` (`id`),
                    ADD CONSTRAINT FOREIGN KEY (`facility`) REFERENCES `__prefix__Facility` (`id`),
                    ADD CONSTRAINT FOREIGN KEY (`timetable_column_row`) REFERENCES `__prefix__TTColumnRow` (`id`),
                    ADD CONSTRAINT FOREIGN KEY (`timetable_day`) REFERENCES `__prefix__TTDay` (`id`);";
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
