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
 * @ORM\Entity(repositoryClass="App\Modules\Timetable\Repository\TimetablePeriodClassRepository")
 * @ORM\Table(name="TimetablePeriodClass", indexes={
 *     @ORM\Index(name="course_class", columns={"course_class"}),
 *     @ORM\Index(name="facility", columns={"facility"}),
 *     @ORM\Index(name="period", columns={"period"})
 * })
 * @ORM\HasLifecycleCallbacks()
 */
class TimetablePeriodClass extends AbstractEntity
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
     * @var TimetablePeriod|null
     * @ORM\ManyToOne(targetEntity="TimetablePeriod",inversedBy="periodClasses")
     * @ORM\JoinColumn(name="period",referencedColumnName="id")
     */
    private $period;

    /**
     * @var CourseClass|null
     * @ORM\ManyToOne(targetEntity="App\Modules\Enrolment\Entity\CourseClass",inversedBy="periodClasses")
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
     * @return TimetablePeriodClass
     */
    public function setId(?string $id): TimetablePeriodClass
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return TimetablePeriod|null
     */
    public function getPeriod(): ?TimetablePeriod
    {
        return $this->period;
    }

    /**
     * @param TimetablePeriod|null $period
     * @return TimetablePeriodClass
     */
    public function setPeriod(?TimetablePeriod $period): TimetablePeriodClass
    {
        $this->period = $period;
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
     * @return TimetablePeriodClass
     */
    public function setCourseClass(?CourseClass $courseClass): TimetablePeriodClass
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
     * @return TimetablePeriodClass
     */
    public function setFacility(?Facility $facility): TimetablePeriodClass
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
