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
namespace App\Modules\Department\Entity;

use App\Manager\AbstractEntity;
use App\Modules\Staff\Entity\Staff;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class DepartmentStaff
 * @package App\Modules\Department\Entity
 * @ORM\Entity(repositoryClass="App\Modules\Department\Repository\DepartmentStaffRepository")
 * @ORM\Table(name="DepartmentStaff",
 *     indexes={@ORM\Index(name="staff",columns={"staff"}),
 *     @ORM\Index(name="department",columns={"department"})},
 *     uniqueConstraints={@ORM\UniqueConstraint(name="department_staff",columns={"staff","department"})})
 * @UniqueEntity({"staff","department"},message="This staff member is already used in this department.")
 */
class DepartmentStaff extends AbstractEntity
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
     * @var Department|null
     * @ORM\ManyToOne(targetEntity="Department", inversedBy="staff")
     * @ORM\JoinColumn(name="department",referencedColumnName="id",nullable=false)
     * @Assert\NotBlank()
     */
    private $department;

    /**
     * @var Department|null
     */
    private static $dept;

    /**
     * @var Staff|null
     * @ORM\ManyToOne(targetEntity="App\Modules\Staff\Entity\Staff")
     * @ORM\JoinColumn(name="staff",referencedColumnName="id",nullable=false)
     * @Assert\NotBlank()
     */
    private $staff;

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
    private static $roleList = ['Learning Area' => ['Coordinator','Assistant Coordinator','Teacher (Curriculum)','Teacher', 'Other'], 'Administration' => ['Director','Manager','Administrator','Other']];

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
        self::$dept = $department;
        return $this;
    }

    /**
     * @return Staff|null
     */
    public function getStaff(): ?Staff
    {
        return $this->staff;
    }

    /**
     * @param Staff|null $staff
     * @return DepartmentStaff
     */
    public function setStaff(?Staff $staff): DepartmentStaff
    {
        $this->staff = $staff;
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
        return static::$dept->getType() === 'Administration' ? self::$roleList['Administration'] : self::$roleList['Learning Area'];
    }

    /**
     * __toString
     * @return string
     */
    public function __toString(): string
    {
        return $this->getDepartment()->__toString() . ': ' . ($this->getStaff() !== null ? $this->getStaff()->getPerson()->getFullName() : '');
    }

    /**
     * toArray
     * @param string|null $name
     * @return array
     */
    public function toArray(?string $name = null): array
    {
        return [
            'name' => $this->getStaff()->getPerson()->getFullNameReversed(),
            'role' => $this->getRole(),
            'id' => $this->getId(),
            'departmentId' => $this->getDepartment()->getId(),
            'canDelete' => true,
        ];
    }

    public function getPerson()
    {
        throw new \Exception('Stopping here as this should call getStaff()');
    }
}
