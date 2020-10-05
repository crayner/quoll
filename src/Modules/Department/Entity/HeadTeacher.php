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
 * Date: 3/10/2020
 * Time: 09:18
 */
namespace App\Modules\Department\Entity;

use App\Manager\AbstractEntity;
use App\Modules\Enrolment\Entity\CourseClass;
use App\Modules\RollGroup\Entity\RollGroup;
use App\Modules\Staff\Entity\Staff;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\PersistentCollection;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class HeadTeacher
 * @package App\Modules\School\Entity
 * @ORM\Entity(repositoryClass="App\Modules\Department\Repository\HeadTeacherRepository")
 * @ORM\Table(name="HeadTeacher",
 *      uniqueConstraints={@ORM\UniqueConstraint("teacher",columns={"teacher"})}
 * )
 * @UniqueEntity({"teacher"})
 */
class HeadTeacher extends AbstractEntity
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
     * @var string
     * @ORM\Column(length=64,nullable=false)
     * @Assert\NotBlank()
     */
    private string $title;

    /**
     * @var Staff
     * @ORM\OneToOne(targetEntity="App\Modules\Staff\Entity\Staff")
     * @ORM\JoinColumn(name="teacher",nullable=false)
     */
    private Staff $teacher;

    /**
     * @var Collection
     * @ORM\ManyToMany(targetEntity="App\Modules\Enrolment\Entity\CourseClass")
     * @ORM\JoinTable(name="HeadTeacherCourseClass",
     *      joinColumns={@ORM\JoinColumn(name="head_teacher",referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="course_class",referencedColumnName="id")}
     *  )
     */
    private Collection $classes;

    /**
     * @var Collection
     * @ORM\ManyToMany(targetEntity="App\Modules\RollGroup\Entity\RollGroup")
     * @ORM\JoinTable(name="HeadTeacherRollGroup",
     *      joinColumns={@ORM\JoinColumn(name="head_teacher",referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="roll_group",referencedColumnName="id")}
     *  )
     */
    private Collection $rollGroups;

    /**
     * @return string|null
     */
    public function getId(): ?string
    {
        return isset($this->id) ? $this->id : null;
    }

    /**
     * @param string $id
     * @return HeadTeacher
     */
    public function setId(string $id): HeadTeacher
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getTitle(): ?string
    {
        return isset($this->title) ? $this->title : null;
    }

    /**
     * @param string $title
     * @return HeadTeacher
     */
    public function setTitle(string $title): HeadTeacher
    {
        $this->title = $title;
        return $this;
    }

    /**
     * @return ?Staff
     */
    public function getTeacher(): ?Staff
    {
        return isset($this->teacher) ? $this->teacher : null;
    }

    /**
     * @param Staff $teacher
     * @return HeadTeacher
     */
    public function setTeacher(Staff $teacher): HeadTeacher
    {
        $this->teacher = $teacher;
        return $this;
    }

    /**
     * getClasses
     *
     * 3/10/2020 09:36
     * @return Collection
     */
    public function getClasses(): Collection
    {
        if (!isset($this->classes)) $this->classes = new ArrayCollection();

        if ($this->classes instanceof PersistentCollection) $this->classes->initialize();

        return $this->classes;
    }

    /**
     * @param Collection|null $classes
     * @return HeadTeacher
     */
    public function setClasses(?Collection $classes): HeadTeacher
    {
        $this->classes = $classes ?: new ArrayCollection();
        return $this;
    }

    /**
     * addClass
     *
     * 3/10/2020 09:37
     * @param CourseClass $class
     * @return $this
     */
    public function addClass(CourseClass $class): HeadTeacher
    {
        if ($this->getClasses()->contains($class)) return $this;

        $this->classes->add($class);

        return $this;
    }

    /**
     * getRollGroups
     *
     * 5/10/2020 15:19
     * @return Collection
     */
    public function getRollGroups(): Collection
    {
        if (!isset($this->rollGroups)) $this->rollGroups = new ArrayCollection();

        if ($this->rollGroups instanceof PersistentCollection) $this->rollGroups->initialize();

        return $this->rollGroups;
    }

    /**
     * @param Collection $rollGroups
     * @return HeadTeacher
     */
    public function setRollGroups(Collection $rollGroups): HeadTeacher
    {
        $this->rollGroups = $rollGroups;
        return $this;
    }

    /**
     * addRollGroup
     *
     * 5/10/2020 15:18
     * @param RollGroup $rollGroup
     * @return HeadTeacher
     */
    public function addRollGroup(RollGroup $rollGroup): HeadTeacher
    {
        if ($this->getRollGroups()->contains($rollGroup)) return $this;

        $this->rollGroups->add($rollGroup);

        return $this;
    }

    /**
     * toArray
     *
     * 3/10/2020 09:37
     * @param string|null $name
     * @return array
     */
    public function toArray(?string $name = null): array
    {
        return [];
    }
}
