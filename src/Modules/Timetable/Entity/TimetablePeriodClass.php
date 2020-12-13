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
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class timetableDayRowClass
 * @package App\Modules\Timetable\Entity
 * @ORM\Entity(repositoryClass="App\Modules\Timetable\Repository\TimetablePeriodClassRepository")
 * @ORM\Table(name="TimetablePeriodClass", indexes={
 *     @ORM\Index(name="course_class", columns={"course_class"}),
 *     @ORM\Index(name="facility", columns={"facility"}),
 *     @ORM\Index(name="period", columns={"period"})
 * },
 *     uniqueConstraints={@ORM\UniqueConstraint(name="period_class",columns={"period","course_class"})}
 *     )
 * @ORM\HasLifecycleCallbacks()
 * @UniqueEntity({"period","courseClass"})
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
    private ?string $id;

    /**
     * @var TimetablePeriod|null
     * @ORM\ManyToOne(targetEntity="TimetablePeriod",inversedBy="periodClasses")
     * @ORM\JoinColumn(name="period",referencedColumnName="id",nullable=false)
     * @Assert\NotBlank()
     */
    private ?TimetablePeriod $period;

    /**
     * @var CourseClass|null
     * @ORM\ManyToOne(targetEntity="App\Modules\Enrolment\Entity\CourseClass",inversedBy="periodClasses")
     * @ORM\JoinColumn(name="course_class",referencedColumnName="id",nullable=false)
     * @Assert\NotBlank()
     */
    private ?CourseClass $courseClass;

    /**
     * @var Facility|null
     * @ORM\ManyToOne(targetEntity="App\Modules\School\Entity\Facility")
     * @ORM\JoinColumn(name="facility", referencedColumnName="id",nullable=false)
     * @Assert\NotBlank()
     */
    private ?Facility $facility;

    /**
     * TimetablePeriodClass constructor.
     *
     * 13/10/2020 16:29
     * @param TimetablePeriod|null $period
     */
    public function __construct(?TimetablePeriod $period = null)
    {
        $this->setPeriod($period);
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
        return isset($this->period) ? $this->period : null;
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
        return isset($this->courseClass) ? $this->courseClass : null;
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
        return isset($this->facility) ? $this->facility : null;
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
            'tutors' => implode(",\n<br />", $this->getCourseClass()->getTutorNames()),
            'name' => $this->getCourseClass()->getFullName(false),
            'abbreviation' => $this->getCourseClass()->getAbbreviatedName(),
            'location' => $this->getFacility()->getName(),
            'period' => $this->getPeriod()->getId(),
        ];
    }

    /**
     * getPeriodName
     *
     * 14/11/2020 07:50
     * @return string
     */
    public function getPeriodName(): string
    {
        return $this->getPeriod() ? $this->getPeriod()->getName() . ' ('.$this->getPeriod()->getAbbreviation().')': '';
    }
}
