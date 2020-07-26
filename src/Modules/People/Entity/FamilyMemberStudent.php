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
 * Date: 11/05/2020
 * Time: 16:24
 */
namespace App\Modules\People\Entity;

use App\Modules\Student\Entity\Student;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\PersistentCollection;

/**
 * Class FamilyMemberStudent
 * @package App\Modules\People\Entity
 * @ORM\Entity(repositoryClass="App\Modules\People\Repository\FamilyMemberStudentRepository")
 */
class FamilyMemberStudent extends FamilyMember
{
    /**
     * @var Student|null
     * @ORM\ManyToOne(targetEntity="App\Modules\Student\Entity\Student",inversedBy="memberOfFamilies")
     * @ORM\JoinColumn(name="student",referencedColumnName="id",nullable=true)
     */
    private $student;

    /**
     * @var Collection|FamilyRelationship[]
     * @ORM\OneToMany(targetEntity="FamilyRelationship",mappedBy="student",orphanRemoval=true)
     */
    private $relationships;

    /**
     * FamilyMemberStudent constructor.
     * @param Family|null $family
     */
    public function __construct(?Family $family = null)
    {
        $this->setRelationships(new ArrayCollection());
        parent::__construct($family);
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
     * @return FamilyMember
     */
    public function setStudent(?Student $student): FamilyMemberStudent
    {
        $this->student = $student;
        return $this;
    }

    /**
     * @return Collection|FamilyRelationship[]
     */
    public function getRelationships(): Collection
    {
        if (null === $this->relationships)
            $this->relationships = new ArrayCollection();

        if ($this->relationships instanceof PersistentCollection)
            $this->relationships->initialize();

        return $this->relationships;
    }

    /**
     * Relationships.
     *
     * @param Collection|FamilyRelationship[] $relationships
     * @return FamilyMemberStudent
     */
    public function setRelationships(Collection $relationships): FamilyMemberStudent
    {
        if ($relationships instanceof PersistentCollection)
            $relationships->initialize();

        $this->relationships = $relationships;
        return $this;
    }

    /**
     * toArray
     * @param string|null $name
     * @return array
     */
    public function toArray(?string $name = null): array
    {
        return parent::toArray('student');
    }

    /**
     * isEqualTo
     * @param FamilyMemberStudent $student
     * @return bool
     * 26/07/2020 09:40
     */
    public function isEqualTo(FamilyMemberStudent $student): bool
    {
        return $this->getFamily()->isEqualTo($student->getFamily()) && $this->getStudent()->isEqualTo($student->getStudent());
    }
}
