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
namespace App\Modules\Staff\Entity;

use App\Manager\EntityInterface;
use App\Modules\People\Entity\Person;
use App\Modules\School\Entity\Department;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class DepartmentStaff
 * @package App\Modules\Staff\Entity
 * @ORM\Entity(repositoryClass="App\Modules\Staff\Repository\DepartmentStaffRepository")
 * @ORM\Table(name="DepartmentStaff",
 *     indexes={@ORM\Index(name="person",columns={"person"}),@ORM\Index(name="department",columns={"department"})},
 *     uniqueConstraints={@ORM\UniqueConstraint(name="department_person",columns={"department","person"})})
 * @UniqueEntity({"department","person"})
 */
class DepartmentStaff implements EntityInterface
{
    CONST VERSION = '20200401';

    /**
     * @var string|null
     * @ORM\Id()
     * @ORM\Column(type="guid")
     * @ORM\GeneratedValue(strategy="UUID")
     */
    private $id;

    /**
     * @var Department|null
     * @ORM\ManyToOne(targetEntity="App\Modules\School\Entity\Department", inversedBy="staff")
     * @ORM\JoinColumn(name="department",referencedColumnName="id", nullable=false)
     * @Assert\NotBlank()
     */
    private $department;

    /**
     * @var Person|null
     * @ORM\ManyToOne(targetEntity="App\Modules\People\Entity\Person")
     * @ORM\JoinColumn(name="person", referencedColumnName="id", nullable=false)
     * @Assert\NotBlank()
     */
    private $person;

    /**
     * @var string|null
     * @ORM\Column(length=24)
     * @Assert\NotBlank()
     * @Assert\Choice({"Coordinator","Assistant Coordinator","Teacher (Curriculum)","Teacher","Director","Manager","Administrator","Other"})
     */
    private $role;

    /**
     * @var array
     */
    private static $roleList = ['Coordinator','Assistant Coordinator','Teacher (Curriculum)','Teacher','Director','Manager','Administrator','Other'];

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
     * @return DepartmentStaff
     */
    public function setId(?string $id): DepartmentStaff
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return Department|null
     */
    public function getDepartment(): ?Department
    {
        return $this->department;
    }

    /**
     * @param Department|null $department
     * @return DepartmentStaff
     */
    public function setDepartment(?Department $department): DepartmentStaff
    {
        $this->department = $department;
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
     * @return DepartmentStaff
     */
    public function setPerson(?Person $person): DepartmentStaff
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
     * @return DepartmentStaff
     */
    public function setRole(?string $role): DepartmentStaff
    {
        $this->role = in_array($role, self::getRoleList()) ? $role : '';
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
        return $this->getDepartment()->__toString() . ': ' . $this->getPerson()->formatName();
    }

    /**
     * toArray
     * @param string|null $name
     * @return array
     */
    public function toArray(?string $name = null): array
    {
        return [
            'name' => $this->getPerson()->formatName(['style' => 'long', 'reverse' => true, 'preferred' => false]),
            'role' => $this->getRole(),
            'canDelete' => true,
        ];
    }

    public function create(): array
    {
        return ["CREATE TABLE `__prefix__DepartmentStaff` (
                    `id` CHAR(36) NOT NULL COMMENT '(DC2Type:guid)',
                    `role` CHAR(24) NOT NULL,
                    `department` CHAR(36) DEFAULT NULL,
                    `person` CHAR(36) DEFAULT NULL,
                    PRIMARY KEY (`id`),
                    UNIQUE KEY `department_person` (`department`,`person`),
                    KEY `department` (`department`),
                    KEY `person` (`person`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;"];
    }

    public function foreignConstraints(): string
    {
        return "ALTER TABLE `__prefix__DepartmentStaff`
                    ADD CONSTRAINT FOREIGN KEY (`department`) REFERENCES `__prefix__Department` (`id`),
                    ADD CONSTRAINT FOREIGN KEY (`person`) REFERENCES `__prefix__Person` (`id`);";
    }

    public function coreData(): string
    {
        return '';
    }

    public static function getVersion(): string
    {
        return self::VERSION;
    }
}
