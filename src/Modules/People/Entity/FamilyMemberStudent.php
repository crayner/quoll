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
use App\Modules\Student\Util\StudentHelper;
use App\Util\ImageHelper;
use App\Util\TranslationHelper;
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
     *
     * 22/08/2020 10:36
     * @param string|null $name
     * @return array
     */
    public function toArray(?string $name = null): array
    {
        return [
            'id' => $this->getId(),
            'photo' => ImageHelper::getAbsoluteImageURL('File', $this->getPerson()->getPersonalDocumentation()->getPersonalImage() ?: '/build/static/DefaultPerson.png'),
            'fullName' => $this->getPerson()->formatName('Standard'),
            'status' => TranslationHelper::translate($this->getPerson()->getStatus(), [], 'People'),
            'roll' => StudentHelper::getCurrentRollGroup($this->getStudent()),
            'comment' => $this->getComment(),
            'family_id' => $this->getFamily()->getId(),
            'student_id' => $this->getId(),
            'person_id' => $this->getPerson()->getId(),
        ];
;
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
