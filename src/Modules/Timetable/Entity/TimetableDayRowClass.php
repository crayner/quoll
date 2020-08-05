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
 * Time: 17:00
 */
namespace App\Modules\Timetable\Entity;

use App\Manager\AbstractEntity;
use App\Modules\Enrolment\Entity\CourseClass;
use App\Modules\School\Entity\Facility;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class timetableDayRowClass
 * @package App\Modules\Timetable\Entity
 * @ORM\Entity(repositoryClass="App\Modules\Timetable\Repository\TimetableDayRowClassRepository")
 * @ORM\Table(name="TimetableDayRowClass", indexes={
 *     @ORM\Index(name="course_class", columns={"course_class"}),
 *     @ORM\Index(name="facility", columns={"facility"}),
 *     @ORM\Index(name="timetable_column_row", columns={"timetable_column_row"}),
 *     @ORM\Index(name="timetable_day", columns={"timetable_day"})
 * })
 * @ORM\HasLifecycleCallbacks()
 */
class TimetableDayRowClass extends AbstractEntity
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
     * @var TimetableColumnPeriod|null
     * @ORM\ManyToOne(targetEntity="TimetableColumnPeriod",inversedBy="timetableDayRowClasses")
     * @ORM\JoinColumn(name="timetable_column_row",referencedColumnName="id")
     */
    private $timetableColumnPeriod;

    /**
     * @var TimetableDay|null
     * @ORM\ManyToOne(targetEntity="TimetableDay",inversedBy="timetableDayRowClasses")
     * @ORM\JoinColumn(name="timetable_day",referencedColumnName="id")
     */
    private $timetableDay;

    /**
     * @var CourseClass|null
     * @ORM\ManyToOne(targetEntity="App\Modules\Enrolment\Entity\CourseClass",inversedBy="timetableDayRowClasses")
     * @ORM\JoinColumn(name="course_class",referencedColumnName="id")
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
     * @return TimetableDayRowClass
     */
    public function setId(?string $id): TimetableDayRowClass
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return TimetableColumnPeriod|null
     */
    public function getTimetableColumnPeriod(): ?TimetableColumnPeriod
    {
        return $this->timetableColumnPeriod;
    }

    /**
     * @param TimetableColumnPeriod|null $timetableColumnPeriod
     * @return TimetableDayRowClass
     */
    public function setTimetableColumnPeriod(?TimetableColumnPeriod $timetableColumnPeriod): TimetableDayRowClass
    {
        $this->timetableColumnPeriod = $timetableColumnPeriod;
        return $this;
    }

    /**
     * @return TimetableDay|null
     */
    public function getTimetableDay(): ?TimetableDay
    {
        return $this->timetableDay;
    }

    /**
     * @param TimetableDay|null $timetableDay
     * @return TimetableDayRowClass
     */
    public function setTimetableDay(?TimetableDay $timetableDay): TimetableDayRowClass
    {
        $this->timetableDay = $timetableDay;
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
     * @return TimetableDayRowClass
     */
    public function setCourseClass(?CourseClass $courseClass): TimetableDayRowClass
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
     * @return TimetableDayRowClass
     */
    public function setFacility(?Facility $facility): TimetableDayRowClass
    {
        $this->facility = $facility;
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
        ];
    }
}
