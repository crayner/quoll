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

use App\Manager\EntityInterface;
use App\Manager\Traits\BooleanList;
use App\Modules\People\Entity\Person;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class CourseClassPerson
 * @package App\Modules\Enrolment\Entity
 * @ORM\Entity(repositoryClass="App\Modules\Enrolment\Repository\CourseClassPersonRepository")
 * @ORM\Table(options={"auto_increment": 1}, name="CourseClassPerson", indexes={@ORM\Index(name="course_class", columns={"course_class"}), @ORM\Index(name="person_role", columns={"person", "role"})}, uniqueConstraints={@ORM\UniqueConstraint(name="courseClassPerson",columns={ "course_class", "person"})})
 * @UniqueEntity({"courseClass","person"})
 */
class CourseClassPerson implements EntityInterface
{
    use BooleanList;

    /**
     * @var integer|null
     * @ORM\Id()
     * @ORM\Column(type="integer",columnDefinition="INT(10) UNSIGNED AUTO_INCREMENT")
     * @ORM\GeneratedValue
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
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param int|null $id
     * @return CourseClassPerson
     */
    public function setId(?int $id): CourseClassPerson
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
    public function create(): string
    {
        return "CREATE TABLE `__prefix__CourseClassPerson` (
                    `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                    `role` varchar(16) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
                    `reportable` varchar(1) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Y',
                    `course_class` int(8) UNSIGNED DEFAULT NULL,
                    `person` int(10) UNSIGNED DEFAULT NULL,
                    PRIMARY KEY (`id`),
                    UNIQUE KEY `courseClassPerson` (`course_class`,`person`),
                    KEY `person` (`person`),
                    KEY `course_class` (`course_class`),
                    KEY `person_role` (`person`,`role`)
                ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
    }

    /**
     * foreignConstraints
     * @return string
     */
    public function foreignConstraints(): string
    {
        return "ALTER TABLE `__prefix__CcourseClassPerson`
  ADD CONSTRAINT FOREIGN KEY (`course_class`) REFERENCES `__prefix__CourseClass` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT FOREIGN KEY (`person`) REFERENCES `__prefix__Person` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT;
";
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