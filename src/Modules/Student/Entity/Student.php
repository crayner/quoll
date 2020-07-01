<?php
/**
 * Created by PhpStorm.
 *
 * Project: Kookaburra
 * Build: __prefix__
 *
 * (c) 2020 Craig Rayner <craig@craigrayner.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * User: craig
 * Date: 1/07/2020
 * Time: 15:20
 */
namespace App\Modules\Student\Entity;

use App\Manager\AbstractEntity;
use App\Modules\Enrolment\Entity\StudentEnrolment;
use App\Modules\People\Entity\Person;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\PersistentCollection;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * Class Student
 * @package App\Modules\Student\Entity
 * @author Craig Rayner <craig@craigrayner.com>
 * @ORM\Entity(repositoryClass="App\Modules\Student\Repository\StudentRepository")
 * @ORM\Table(name="Student",
 *     uniqueConstraints={@ORM\UniqueConstraint(name="student_identifier",columns={"student_identifier"}),
 *     @ORM\UniqueConstraint("person", columns={"person"})}
 * )
 * @UniqueEntity("studentIdentifier")
 */
class Student extends AbstractEntity
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
     * @var Person|null
     * @ORM\OneToOne(targetEntity="App\Modules\People\Entity\Person",inversedBy="student")
     * @ORM\JoinColumn(name="person",referencedColumnName="id")
     */
    private $person;

    /**
     * @var string|null
     * @ORM\Column(length=20,nullable=true)
     */
    private $studentIdentifier;

    /**
     * @var array|null
     * @ORM\Column(type="simple_array",nullable=true)
     */
    private $studentAgreements;

    /**
     * @var StudentEnrolment[]|Collection||null
     * @ORM\OneToMany(targetEntity="App\Modules\Enrolment\Entity\StudentEnrolment", mappedBy="student")
     */
    private $studentEnrolments;

    /**
     * @return string|null
     */
    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * @param string|null $id
     * @return Student
     */
    public function setId(?string $id): Student
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
     * @return Student
     */
    public function setPerson(?Person $person): Student
    {
        $this->person = $person;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getStudentIdentifier(): ?string
    {
        return $this->studentIdentifier;
    }

    /**
     * @param string|null $studentIdentifier
     * @return Student
     */
    public function setStudentIdentifier(?string $studentIdentifier): Student
    {
        $this->studentIdentifier = $studentIdentifier;
        return $this;
    }

    /**
     * @return array|null
     */
    public function getStudentAgreements(): ?array
    {
        return $this->studentAgreements;
    }

    /**
     * @param array|null $studentAgreements
     * @return Student
     */
    public function setStudentAgreements(?array $studentAgreements): Student
    {
        $this->studentAgreements = $studentAgreements;
        return $this;
    }

    /**
     * getStudentEnrolments
     * @return Collection|null
     */
    public function getStudentEnrolments(): ?Collection
    {
        if (null === $this->studentEnrolments)
            $this->studentEnrolments = new ArrayCollection();

        if ($this->studentEnrolments instanceof PersistentCollection)
            $this->studentEnrolments->initialize();

        return $this->studentEnrolments;
    }

    /**
     * StudentEnrolments.
     *
     * @param StudentEnrolment[]|Collection $studentEnrolments
     * @return Student
     */
    public function setStudentEnrolments(Collection $studentEnrolments): Student
    {
        $this->studentEnrolments = $studentEnrolments;
        return $this;
    }

    public function toArray(?string $name = null): array
    {
        // TODO: Implement toArray() method.
    }

    public function create(): array
    {
        return ["CREATE TABLE `__prefix__Student` (
            `id` CHAR(36) NOT NULL COMMENT '(DC2Type:guid)', 
            `person` CHAR(36) DEFAULT NULL COMMENT '(DC2Type:guid)', 
            `student_identifier` VARCHAR(20) DEFAULT NULL, 
            UNIQUE INDEX `person` (`person`), 
            UNIQUE INDEX `student_identifier` (`student_identifier`), 
            PRIMARY KEY(`id`)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB;"];
    }

    public function foreignConstraints(): string
    {
        return "ALTER TABLE `__prefix__Student`
                    ADD CONSTRAINT FOREIGN KEY (`person`) REFERENCES `__prefix__Person` (`id`);";
    }

    public static function getVersion(): string
    {
        // TODO: Implement getVersion() method.
    }
}
