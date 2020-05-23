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
namespace App\Modules\Enrolment\Entity;

use App\Manager\AbstractEntity;
use App\Manager\Traits\BooleanList;
use App\Modules\People\Entity\Person;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class CourseClassPerson
 * @package App\Modules\Enrolment\Entity
 * @ORM\Entity(repositoryClass="App\Modules\Enrolment\Repository\CourseClassPersonRepository")
 * @ORM\Table(name="CourseClassPerson", indexes={@ORM\Index(name="course_class", columns={"course_class"}), @ORM\Index(name="person_role", columns={"person", "role"})}, uniqueConstraints={@ORM\UniqueConstraint(name="courseClassPerson",columns={ "course_class", "person"})})
 * @UniqueEntity({"courseClass","person"})
 */
class CourseClassPerson extends AbstractEntity
{
    CONST VERSION = '1.0.00';

    use BooleanList;

    /**
     * @var string|null
     * @ORM\Id()
     * @ORM\Column(type="guid")
     * @ORM\GeneratedValue(strategy="UUID")
     */
    private $id;

    /**
     * @var CourseClass|null
     * @ORM\ManyToOne(targetEntity="CourseClass", inversedBy="courseClassPeople")
     * @ORM\JoinColumn(name="course_class", referencedColumnName="id", nullable=false)
     * @Assert\NotBlank()
     */
    private $courseClass;

    /**
     * @var Person|null
     * @ORM\ManyToOne(targetEntity="App\Modules\People\Entity\Person", inversedBy="courseClassPerson")
     * @ORM\JoinColumn(name="person", referencedColumnName="id", nullable=false)
     * @Assert\NotBlank()
     */
    private $person;

    /**
     * @var string|null
     * @ORM\Column(length=16)
     * @Assert\NotBlank()
     * @Assert\Choice({"Student","Teacher","Assistant","Technician","Parent","Student - Left","Teacher - Left"})
     */
    private $role = '';

    /**
     * @var array
     */
    private static $roleList = ['Student','Teacher','Assistant','Technician','Parent','Student - Left','Teacher - Left'];

    /**
     * @var string|null
     * @ORM\Column(length=1, options={"default": "Y"})
     * @Assert\Choice({"Y","N"})
     */
    private $reportable = 'Y';

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
     * @return CourseClassPerson
     */
    public function setId(?string $id): CourseClassPerson
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
     * @return CourseClassPerson
     */
    public function setCourseClass(?CourseClass $courseClass): CourseClassPerson
    {
        $this->courseClass = $courseClass;
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
     * @return CourseClassPerson
     */
    public function setPerson(?Person $person): CourseClassPerson
    {
        $this->person = $person;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getRole(): ?string
    {
        return $this->role;
    }

    /**
     * @param string|null $role
     * @return CourseClassPerson
     */
    public function setRole(?string $role): CourseClassPerson
    {
        $this->role = in_array($role, self::getRoleList()) ? $role : '';
        return $this;
    }

    /**
     * @return string|null
     */
    public function getReportable(): ?string
    {
        return $this->reportable;
    }

    /**
     * @param string|null $reportable
     * @return CourseClassPerson
     */
    public function setReportable(?string $reportable): CourseClassPerson
    {
        $this->reportable = self::checkBoolean($reportable, 'Y');
        return $this;
    }

    /**
     * @return array
     */
    public static function getRoleList(): array
    {
        return self::$roleList;
    }

    /**
     * __toString
     * @return string
     */
    public function __toString(): string
    {
        return $this->getCourseClass()->courseClassName(true) . ': ' . $this->getPerson()->formatName();
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
    public function create(): array
    {
        return ["CREATE TABLE `__prefix__CourseClassPerson` (
                    `id` CHAR(36) NOT NULL COMMENT '(DC2Type:guid)',
                    `role` CHAR(16) NOT NULL,
                    `reportable` CHAR(1) NOT NULL DEFAULT 'Y',
                    `course_class` CHAR(36) DEFAULT NULL,
                    `person` CHAR(36) DEFAULT NULL,
                    PRIMARY KEY (`id`),
                    UNIQUE KEY `courseClassPerson` (`course_class`,`person`),
                    KEY `person` (`person`),
                    KEY `course_class` (`course_class`),
                    KEY `person_role` (`person`,`role`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;"];
    }

    /**
     * foreignConstraints
     * @return string
     */
    public function foreignConstraints(): string
    {
        return "ALTER TABLE `__prefix__CourseClassPerson`
                    ADD CONSTRAINT FOREIGN KEY (`course_class`) REFERENCES `__prefix__CourseClass` (`id`),
                    ADD CONSTRAINT FOREIGN KEY (`person`) REFERENCES `__prefix__Person` (`id`);";
    }

    /**
     * getVersion
     * @return string
     */
    public static function getVersion(): string
    {
        return self::VERSION;
    }
}
