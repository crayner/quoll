<?php
/**
 * Created by PhpStorm.
 *
 * Project: Kookaburra
 * Build: Quoll
 *
 * (c) 2020 Craig Rayner <craig@craigrayner.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * User: craig
 * Date: 16/09/2020
 * Time: 16:00
 */
namespace App\Modules\Enrolment\Entity;

use App\Manager\AbstractEntity;
use App\Modules\Staff\Entity\Staff;
use App\Provider\ProviderFactory;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class CourseClassStaff
 * @package App\Modules\Enrolment\Entity
 * @author Craig Rayner <craig@craigrayner.com>
 * @ORM\Entity(repositoryClass="App\Modules\Enrolment\Repository\CourseClassTutorRepository")
 * @ORM\Table(name="CourseClassTutor",
 *     indexes={@ORM\Index(name="course_class",columns={"course_class"}),
 *      @ORM\Index(name="staff",columns={"staff"})},
 *     uniqueConstraints={@ORM\UniqueConstraint(name="course_class_staff",columns={"course_class","staff"}),
 *     @ORM\UniqueConstraint(name="course_class_sort_order",columns={"course_class","sort_order"})})
 * @UniqueEntity({"staff","courseClass"},message="This staff member is already attached to this class.")
 * @UniqueEntity({"sortOrder","courseClass"},message="This sort order is already attached to this class.")
 * @ORM\HasLifecycleCallbacks()
 */
class CourseClassTutor extends AbstractEntity
{
    CONST VERSION = '1.0.00';

    /**
     * @var string|null
     * @ORM\Id()
     * @ORM\Column(type="guid")
     * @ORM\GeneratedValue(strategy="UUID")
     */
    private ?string $id;

    /**
     * @var CourseClass|null
     * @ORM\ManyToOne(targetEntity="App\Modules\Enrolment\Entity\CourseClass",inversedBy="tutors")
     * @ORM\JoinColumn(name="course_class",referencedColumnName="id",nullable=false)
     * @Assert\NotBlank()
     */
    private ?CourseClass $courseClass;

    /**
     * @var Staff|null
     * @ORM\ManyToOne(targetEntity="App\Modules\Staff\Entity\Staff")
     * @ORM\JoinColumn(name="staff",referencedColumnName="id",nullable=false)
     * @Assert\NotBlank()
     */
    private ?Staff $staff;

    /**
     * @var string|null
     * @ORM\Column(length=20,nullable=true)
     * @Assert\Choice(callback="getRoleList")
     */
    private ?string $role = null;

    /**
     * @var int
     * @ORM\Column(type="smallint")
     * @Assert\Range(min=1,max=99)
     */
    private int $sortOrder;

    /**
     * CourseClassStaff constructor.
     * @param CourseClass|null $courseClass
     */
    public function __construct(?CourseClass $courseClass = null)
    {
        $this->setCourseClass($courseClass);
        if (null !== $courseClass) {
            $courseClass->addTutor($this);
            $this->setSortOrder(ProviderFactory::getRepository(CourseClassTutor::class)->nextSortOrder($courseClass));
        }
    }

    /**
     * @return string|null
     */
    public function getId(): ?string
    {
        return $this->id = isset($this->id) ? $this->id : null;
    }

    /**
     * @param string|null $id
     * @return CourseClassTutor
     */
    public function setId(?string $id): CourseClassTutor
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
     * @return CourseClassTutor
     */
    public function setCourseClass(?CourseClass $courseClass): CourseClassTutor
    {
        $this->courseClass = $courseClass;
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
     * @return CourseClassTutor
     */
    public function setStaff(?Staff $staff): CourseClassTutor
    {
        $this->staff = $staff;
        return $this;
    }

    /**
     * getRole
     *
     * 21/09/2020 11:24
     * @return string|null
     */
    public function getRole(): ?string
    {
        if (!isset($this->role) || is_null($this->role) || $this->role === '') return $this->getStaff()->getType();
        return $this->role;
    }

    /**
     * @param string|null $role
     * @return CourseClassTutor
     */
    public function setRole(?string $role): CourseClassTutor
    {
        $this->role = $role === '' || $this->getStaff()->getType() === $role ? null : $role;
        return $this;
    }

    /**
     * getRoleList
     *
     * 18/09/2020 12:36
     * @return array
     */
    public static function getRoleList(): array
    {
        return array_merge([null,''],Staff::getTypeList());
    }

    /**
     * @return int
     * @ORM\PrePersist()
     */
    public function getSortOrder(): int
    {
        if (!isset($this->sortOrder) || $this->sortOrder === null) $this->sortOrder = ProviderFactory::getRepository(CourseClassTutor::class)->nextSortOrder($this->getCourseClass());
        return $this->sortOrder;
    }

    /**
     * @param int $sortOrder
     * @return CourseClassTutor
     */
    public function setSortOrder(int $sortOrder): CourseClassTutor
    {
        $this->sortOrder = $sortOrder;
        return $this;
    }

    /**
     * toArray
     *
     * 21/09/2020 10:23
     * @param string|null $name
     * @return array
     */
    public function toArray(?string $name = null): array
    {
        return [
            'tutor' => $this->getStaff()->getFullName(),
            'type' => $this->getStaff()->getType(),
            'sortOrder' => $this->getSortOrder(),
            'id' => $this->getId(),
            'course_class_id' => $this->getCourseClass()->getId(),
            'canDelete' => true,
        ];
    }
}