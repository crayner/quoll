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
use App\Modules\School\Entity\Facility;
use App\Util\EntityHelper;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class TTDayRowClass
 * @package App\Modules\Timetable\Entity
 * @ORM\Entity(repositoryClass="App\Modules\Timetable\Repository\TTDayRowClassRepository")
 * @ORM\Table(options={"auto_increment": 1}, name="TTDayRowClass", indexes={
 *     @ORM\Index(name="course_class", columns={"course_class"}),
 *     @ORM\Index(name="facility", columns={"facility"}),
 *     @ORM\Index(name="timetable_column_row", columns={"timetable_column_row"}),
 *     @ORM\Index(name="timetable_day", columns={"timetable_day"})
 * })
 * @ORM\HasLifecycleCallbacks()
 */
class TTDayRowClass implements EntityInterface
{
    /**
     * @var integer|null
     * @ORM\Id
     * @ORM\Column(type="bigint", columnDefinition="INT(12) UNSIGNED AUTO_INCREMENT")
     * @ORM\GeneratedValue
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
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param int|null $id
     * @return TTDayRowClass
     */
    public function setId(?int $id): TTDayRowClass
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
        return 'CREATE TABLE `__prefix__TTDayRowClass` (
                    `id` int(12) UNSIGNED NOT NULL AUTO_INCREMENT,
                    `timetable_column_row` int(8) UNSIGNED DEFAULT NULL,
                    `timetable_day` int(10) UNSIGNED DEFAULT NULL,
                    `course_class` int(8) UNSIGNED DEFAULT NULL,
                    `facility` int(10) UNSIGNED DEFAULT NULL,
                    PRIMARY KEY (`id`),
                    KEY `facility` (`facility`),
                    KEY `timetable_day` (`timetable_day`) USING BTREE,
                    KEY `timetable_column_row` (`timetable_column_row`) USING BTREE,
                    KEY `course_class` (`course_class`) USING BTREE
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;';
    }

    public function foreignConstraints(): string
    {
        return 'ALTER TABLE `__prefix__TTDayRowClass`
                    ADD CONSTRAINT FOREIGN KEY (`course_class`) REFERENCES `__prefix__CourseClass` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
                    ADD CONSTRAINT FOREIGN KEY (`facility`) REFERENCES `__prefix__Facility` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
                    ADD CONSTRAINT FOREIGN KEY (`timetable_column_row`) REFERENCES `__prefix__TTColumnRow` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
                    ADD CONSTRAINT FOREIGN KEY (`timetable_day`) REFERENCES `__prefix__TTDay` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT;';
    }

    public function coreData(): string
    {
        return '';
    }
}