<?php
/**
 * Created by PhpStorm.
 *
* Quoll
 * (c) 2018 Craig Rayner <craig@craigrayner.com>
 *
 * User: craig
 * Date: 5/12/2018
 * Time: 16:11
 */
namespace App\Modules\Enrolment\Entity;

use App\Manager\EntityInterface;
use App\Modules\People\Entity\Person;
use App\Modules\School\Entity\RollGroup;
use App\Modules\School\Entity\AcademicYear;
use App\Modules\School\Entity\YearGroup;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class StudentEnrolment
 * @package App\Modules\Enrolment\Entity
 * @ORM\Entity(repositoryClass="App\Modules\Enrolment\Repository\StudentEnrolmentRepository")
 * @ORM\Table(options={"auto_increment": 1}, name="StudentEnrolment", indexes={@ORM\Index(name="academic_year", columns={"academic_year"}), @ORM\Index(name="year_group", columns={"year_group"}), @ORM\Index(name="person", columns={"person"}), @ORM\Index(name="roll_group", columns={"roll_group"}), @ORM\Index(name="person_academic_year", columns={"person","academic_year"})})
 */
class StudentEnrolment implements EntityInterface
{
    /**
     * @var integer|null
     * @ORM\Id
     * @ORM\Column(type="integer", name="id", columnDefinition="INT(8) UNSIGNED AUTO_INCREMENT")
     * @ORM\GeneratedValue
     */
    private $id;

    /**
     * @var Person|null
     * @ORM\ManyToOne(targetEntity="App\Modules\People\Entity\Person", inversedBy="studentEnrolments")
     * @ORM\JoinColumn(name="person",referencedColumnName="id", nullable=false)
     */
    private $person;

    /**
     * @var AcademicYear|null
     * @ORM\ManyToOne(targetEntity="App\Modules\School\Entity\AcademicYear")
     * @ORM\JoinColumn(name="academic_year", referencedColumnName="id", nullable=false)
     *
     */
    private $academicYear;

    /**
     * @var YearGroup|null
     * @ORM\ManyToOne(targetEntity="App\Modules\School\Entity\YearGroup")
     * @ORM\JoinColumn(name="year_group",referencedColumnName="id", nullable=false)
     */
    private $yearGroup;

    /**
     * @var RollGroup|null
     * @ORM\ManyToOne(targetEntity="App\Modules\School\Entity\RollGroup", inversedBy="studentEnrolments")
     * @ORM\JoinColumn(name="roll_group",referencedColumnName="id",nullable=false)
     */
    private $rollGroup;

    /**
     * @var integer|null
     * @ORM\Column(type="smallint", nullable=true, name="rollOrder", columnDefinition="INT(2)")
     */
    private $rollOrder;

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param int|null $id
     * @return StudentEnrolment
     */
    public function setId(?int $id): StudentEnrolment
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return Person|null
     */
    public function getPerson(): ?Person
    {
        return $this->person;
    }

    /**
     * @param Person|null $person
     * @return StudentEnrolment
     */
    public function setPerson(?Person $person): StudentEnrolment
    {
        $this->person = $person;
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
     * toArray
     * @param string|null $name
     * @return array
     */
    public function toArray(?string $name = null): array
    {
        return [];
    }

    /**
     * create
     * @return string
     */
    public function create(): string
    {
        return "CREATE TABLE IF NOT EXISTS `__prefix__StudentEnrolment` (
                    `id` int(8) UNSIGNED NOT NULL AUTO_INCREMENT,
                    `rollOrder` int(2) DEFAULT NULL,
                    `person` int(10) UNSIGNED DEFAULT NULL,
                    `academic_year` int(3) UNSIGNED DEFAULT NULL,
                    `year_group` int(3) UNSIGNED DEFAULT NULL,
                    `roll_group` int(5) UNSIGNED DEFAULT NULL,
                    PRIMARY KEY (`id`),
                    KEY `person` (`person`) USING BTREE,
                    KEY `academic_year` (`academic_year`) USING BTREE,
                    KEY `year_group` (`year_group`) USING BTREE,
                    KEY `roll_group` (`roll_group`) USING BTREE,
                    KEY `person_academic_year` (`person`,`academic_year`) USING BTREE
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=ut8mb4_unicode_ci;";
    }

    /**
     * foreignConstraints
     * @return string
     */
    public function foreignConstraints(): string
    {
        return "ALTER TABLE `__prefix__StudentEnrolment`
                    ADD CONSTRAINT FOREIGN KEY (`person`) REFERENCES `__prefix__Person` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
                    ADD CONSTRAINT FOREIGN KEY (`roll_group`) REFERENCES `__prefix__RollGroup` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
                    ADD CONSTRAINT FOREIGN KEY (`year_group`) REFERENCES `__prefix__YearGroup` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
                    ADD CONSTRAINT FOREIGN KEY (`academic_year`) REFERENCES `__prefix__AcademicYear` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT;";
    }

    /**
     * coreData
     * @return string
     */
    public function coreData(): string
    {
        return "";
    }
}