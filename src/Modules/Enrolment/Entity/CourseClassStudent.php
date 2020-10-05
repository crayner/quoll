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
use App\Modules\Student\Entity\Student;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class CourseClassPerson
 * @package App\Modules\Enrolment\Entity
 * @ORM\Entity(repositoryClass="App\Modules\Enrolment\Repository\CourseClassStudentRepository")
 * @ORM\Table(name="CourseClassStudent",
 *     indexes={@ORM\Index(name="course_class", columns={"course_class"}),
 *     @ORM\Index(name="student", columns={"student"})},
 *     uniqueConstraints={@ORM\UniqueConstraint(name="course_class_student",columns={ "course_class", "student"})})
 * @UniqueEntity({"student","courseClass"})
 */
class CourseClassStudent extends AbstractEntity
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
     * @var CourseClass|null
     * @ORM\ManyToOne(targetEntity="CourseClass", inversedBy="students")
     * @ORM\JoinColumn(name="course_class",referencedColumnName="id",nullable=false)
     * @Assert\NotBlank()
     */
    private ?CourseClass $courseClass;

    /**
     * @var Student|null
     * @ORM\ManyToOne(targetEntity="App\Modules\Student\Entity\Student")
     * @ORM\JoinColumn(name="student",referencedColumnName="id",nullable=false)
     * @Assert\NotBlank()
    */
    private ?Student $student;

    /**
     * @var bool
     * @ORM\Column(type="boolean", options={"default": 1})
     */
    private bool $reportable;

    /**
     * CourseClassPerson constructor.
     * @param CourseClass|null $courseClass
     */
    public function __construct(?CourseClass $courseClass = null)
    {
        $this->setCourseClass($courseClass);
    }

    /**
     * getId
     *
     * 3/09/2020 11:23
     * @return string|null
     */
    public function getId(): ?string
    {
        return isset($this->id) ? $this->id : null;
    }

    /**
     * setId
     *
     * 3/09/2020 11:23
     * @param string|null $id
     * @return $this
     */
    public function setId(?string $id): CourseClassStudent
    {
        $this->id = $id;
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
     * @return CourseClassStudent
     */
    public function setCourseClass(?CourseClass $courseClass): CourseClassStudent
    {
        $this->courseClass = $courseClass;
        return $this;
    }

    /**
     * @return Student|null
     */
    public function getStudent(): ?Student
    {
        return $this->student;
    }

    /**
     * @param Student|null $student
     * @return CourseClassStudent
     */
    public function setStudent(?Student $student): CourseClassStudent
    {
        $this->student = $student;
        return $this;
    }

    /**
     * @return bool
     */
    public function isReportable(): bool
    {
        return $this->reportable = isset($this->reportable) ? $this->reportable : $this->getCourseClass()->isReportable();
    }

    /**
     * @param bool $reportable
     * @return CourseClassStudent
     */
    public function setReportable(bool $reportable): CourseClassStudent
    {
        $this->reportable = $reportable;
        return $this;
    }

    /**
     * mirrorReportable
     *
     * Will mirror the class reportable stattus to the individual.
     * 12/09/2020 09:16
     * @return $this
     */
    public function mirrorReportable(): CourseClassStudent
    {
        $this->reportable = !isset($this->reportable) ? $this->getCourseClass()->isReportable() : $this->reportable;
        return $this;
    }

    /**
     * __toString
     * @return string
     */
    public function __toString(): string
    {
        return $this->getCourseClass()->courseClassName(true) . ': ' . $this->getStudent()->getFullName();
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
}
