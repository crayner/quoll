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
use App\Modules\Enrolment\Validator\ClassPerson;
use App\Modules\People\Entity\Person;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class CourseClassPerson
 * @package App\Modules\Enrolment\Entity
 * @ORM\Entity(repositoryClass="App\Modules\Enrolment\Repository\CourseClassPersonRepository")
 * @ORM\Table(name="CourseClassPerson",
 *     indexes={@ORM\Index(name="course_class", columns={"course_class"}),
 *     @ORM\Index(name="person_role", columns={"person", "role"}),
 *     @ORM\Index(name="person", columns={"person"})},
 *     uniqueConstraints={@ORM\UniqueConstraint(name="course_class_person",columns={ "course_class", "person"})})
 * @UniqueEntity({"person","courseClass"})
 * @ClassPerson()
 */
class CourseClassPerson extends AbstractEntity
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
     * @ORM\ManyToOne(targetEntity="CourseClass", inversedBy="courseClassPeople")
     * @ORM\JoinColumn(name="course_class",referencedColumnName="id",nullable=false)
     * @Assert\NotBlank()
     */
    private ?CourseClass $courseClass;

    /**
     * @var Person|null
     * @ORM\ManyToOne(targetEntity="App\Modules\People\Entity\Person")
     * @ORM\JoinColumn(name="person",referencedColumnName="id",nullable=false)
     * @Assert\NotBlank()
    */
    private ?Person $person;

    /**
     * @var string|null
     * @ORM\Column(length=16)
     * @Assert\Choice(callback="getRoleList")
     */
    private string $role;

    /**
     * @var array
     */
    private static array $roleList = ['Student','Teacher','Assistant','Technician','Volunteer','Student - Left','Teacher - Left'];

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
     * @return string
     */
    public function getRole(): string
    {
        return $this->role;
    }

    /**
     * setRole
     *
     * 3/09/2020 11:24
     * @param string $role
     * @return CourseClassPerson
     */
    public function setRole(string $role): CourseClassPerson
    {
        $this->role = in_array($role, self::getRoleList()) ? $role : '';
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
     * @return CourseClassPerson
     */
    public function setReportable(bool $reportable): CourseClassPerson
    {
        $this->reportable = $reportable;
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
}
