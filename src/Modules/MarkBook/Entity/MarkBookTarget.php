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
namespace App\Modules\MarkBook\Entity;

use App\Manager\AbstractEntity;
use App\Modules\Enrolment\Entity\CourseClass;
use App\Modules\People\Entity\Person;
use App\Modules\School\Entity\ScaleGrade;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class MarkBookTarget
 * @package App\Modules\MarkBook\Entity
 * @ORM\Entity(repositoryClass="App\Modules\MarkBook\Repository\MarkBookTargetRepository")
 * @ORM\Table(name="MarkBookTarget",
 *     indexes={@ORM\Index(name="course_class",columns={"course_class"}),
 *     @ORM\Index(name="student",columns={"student"}),
 *     @ORM\Index(name="scale_grade",columns={"scale_grade"})},
 *     uniqueConstraints={@ORM\UniqueConstraint(name="course_class_person", columns={"course_class", "student"})})
 * @UniqueEntity({"courseClass","student"})
 */
class MarkBookTarget extends AbstractEntity
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
     * @var CourseClass|null
     * @ORM\ManyToOne(targetEntity="App\Modules\Enrolment\Entity\CourseClass")
     * @ORM\JoinColumn(name="course_class", referencedColumnName="id")
     * @Assert\NotBlank()
     */
    private $courseClass;

    /**
     * @var Person|null
     * @ORM\ManyToOne(targetEntity="App\Modules\People\Entity\Person")
     * @ORM\JoinColumn(name="student", referencedColumnName="id")
     * @Assert\NotBlank()
     */
    private $student;

    /**
     * @var ScaleGrade|null
     * @ORM\ManyToOne(targetEntity="App\Modules\School\Entity\ScaleGrade")
     * @ORM\JoinColumn(name="scale_grade", referencedColumnName="id")
     * @Assert\NotBlank()
     */
    private $scaleGrade;

    /**
     * @return string|null
     */
    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * @param string|null $id
     * @return MarkbookTarget
     */
    public function setId(?string $id): MarkbookTarget
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
     * @return MarkbookTarget
     */
    public function setCourseClass(?CourseClass $courseClass): MarkbookTarget
    {
        $this->courseClass = $courseClass;
        return $this;
    }

    /**
     * @return Person|null
     */
    public function getStudent(): ?Person
    {
        return $this->student;
    }

    /**
     * @param Person|null $student
     * @return MarkbookTarget
     */
    public function setStudent(?Person $student): MarkbookTarget
    {
        $this->student = $student;
        return $this;
    }

    /**
     * @return ScaleGrade|null
     */
    public function getScaleGrade(): ?ScaleGrade
    {
        return $this->scaleGrade;
    }

    /**
     * @param ScaleGrade|null $scaleGrade
     * @return MarkbookTarget
     */
    public function setScaleGrade(?ScaleGrade $scaleGrade): MarkbookTarget
    {
        $this->scaleGrade = $scaleGrade;
        return $this;
    }

    /**
     * toArray
     * @param string|null $name
     * @return array
     * 1/06/2020 12:02
     */
    public function toArray(?string $name = null): array
    {
        return [];
    }

    /**
     * create
     * @return array|string[]
     * 1/06/2020 12:02
     */
    public function create(): array
    {
        return ["CREATE TABLE `__prefix__MarkBookTarget` (
                    `id` char(36) NOT NULL COMMENT '(DC2Type:guid)',
                    `course_class` char(36) DEFAULT NULL COMMENT '(DC2Type:guid)',
                    `student` char(36) DEFAULT NULL COMMENT '(DC2Type:guid)',
                    `scale_grade` char(36) DEFAULT NULL COMMENT '(DC2Type:guid)',
                    PRIMARY KEY (`id`),
                    UNIQUE KEY `course_class_person` (`course_class`,`student`),
                    KEY `course_class` (`course_class`),
                    KEY `student` (`student`),
                    KEY `scale_grade` (`scale_grade`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;"];
    }

    /**
     * foreignConstraints
     * @return string
     * 1/06/2020 12:02
     */
    public function foreignConstraints(): string
    {
        return "ALTER TABLE `__prefix__MarkBookTarget`
                    ADD CONSTRAINT FOREIGN KEY (`scale_grade`) REFERENCES `__prefix__ScaleGrade` (`id`),
                    ADD CONSTRAINT FOREIGN KEY (`course_class`) REFERENCES `__prefix__CourseClass` (`id`),
                    ADD CONSTRAINT FOREIGN KEY (`student`) REFERENCES `__prefix__Person` (`id`);";
    }

    /**
     * getVersion
     * @return string
     * 1/06/2020 12:02
     */
    public static function getVersion(): string
    {
        return static::VERSION;
    }
}