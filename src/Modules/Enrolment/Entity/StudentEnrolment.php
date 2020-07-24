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
 * Time: 16:11
 */
namespace App\Modules\Enrolment\Entity;

use App\Manager\AbstractEntity;
use App\Modules\People\Entity\Person;
use App\Modules\School\Entity\AcademicYear;
use App\Modules\RollGroup\Entity\RollGroup;
use App\Modules\School\Entity\YearGroup;
use App\Modules\Student\Entity\Student;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class StudentEnrolment
 * @package App\Modules\Enrolment\Entity
 * @ORM\Entity(repositoryClass="App\Modules\Enrolment\Repository\StudentEnrolmentRepository")
 * @ORM\Table(name="StudentEnrolment",
 *     indexes={@ORM\Index(name="academic_year", columns={"academic_year"}),
 *     @ORM\Index(name="year_group", columns={"year_group"}),
 *     @ORM\Index(name="student", columns={"student"}),
 *     @ORM\Index(name="roll_group", columns={"roll_group"}),
 *     @ORM\Index(name="student_academic_year", columns={"student","academic_year"})})
 */
class StudentEnrolment extends AbstractEntity
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
     * @var Student|null
     * @ORM\ManyToOne(targetEntity="App\Modules\Student\Entity\Student",inversedBy="studentEnrolments")
     * @ORM\JoinColumn(name="student",referencedColumnName="id",nullable=false)
     * @Assert\NotBlank()
     */
    private $student;

    /**
     * @var AcademicYear|null
     * @ORM\ManyToOne(targetEntity="App\Modules\School\Entity\AcademicYear")
     * @ORM\JoinColumn(name="academic_year",referencedColumnName="id",nullable=false)
     * @Assert\NotBlank()
     *
     */
    private $academicYear;

    /**
     * @var YearGroup|null
     * @ORM\ManyToOne(targetEntity="App\Modules\School\Entity\YearGroup")
     * @ORM\JoinColumn(name="year_group",referencedColumnName="id",nullable=false)
     * @Assert\NotBlank()
     */
    private $yearGroup;

    /**
     * @var RollGroup|null
     * @ORM\ManyToOne(targetEntity="App\Modules\RollGroup\Entity\RollGroup",inversedBy="studentEnrolments")
     * @ORM\JoinColumn(name="roll_group",referencedColumnName="id",nullable=false)
     * @Assert\NotBlank()
     */
    private $rollGroup;

    /**
     * @var integer|null
     * @ORM\Column(type="smallint",nullable=true)
     * @Assert\Range(min=1,max=99)
     */
    private $rollOrder;

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
     * @return StudentEnrolment
     */
    public function setId(?string $id): StudentEnrolment
    {
        $this->id = $id;
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
     * @return StudentEnrolment
     */
    public function setStudent(?Student $student): StudentEnrolment
    {
        $this->student = $student;
        return $this;
    }

    /**
     * @return AcademicYear|null
     */
    public function getAcademicYear(): ?AcademicYear
    {
        return $this->academicYear;
    }

    /**
     * @param AcademicYear|null $academicYear
     * @return StudentEnrolment
     */
    public function setAcademicYear(?AcademicYear $academicYear): StudentEnrolment
    {
        $this->academicYear = $academicYear;
        return $this;
    }

    /**
     * @return YearGroup|null
     */
    public function getYearGroup(): ?YearGroup
    {
        return $this->yearGroup;
    }

    /**
     * @param YearGroup|null $yearGroup
     * @return StudentEnrolment
     */
    public function setYearGroup(?YearGroup $yearGroup): StudentEnrolment
    {
        $this->yearGroup = $yearGroup;
        return $this;
    }

    /**
     * @return RollGroup|null
     */
    public function getRollGroup(): ?RollGroup
    {
        return $this->rollGroup;
    }

    /**
     * @param RollGroup|null $rollGroup
     * @return StudentEnrolment
     */
    public function setRollGroup(?RollGroup $rollGroup): StudentEnrolment
    {
        $this->rollGroup = $rollGroup;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getRollOrder(): ?int
    {
        return $this->rollOrder;
    }

    /**
     * @param int|null $rollOrder
     * @return StudentEnrolment
     */
    public function setRollOrder(?int $rollOrder): StudentEnrolment
    {
        $this->rollOrder = $rollOrder;
        return $this;
    }

    /**
     * getName
     * @return string
     * 20/07/2020 09:23
     */
    public function getName(): string
    {
        return $this->getRollGroup() ? $this->getRollGroup()->getName() : '';
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
