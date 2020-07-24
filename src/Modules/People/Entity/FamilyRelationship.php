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
namespace App\Modules\People\Entity;

use App\Manager\AbstractEntity;
use App\Util\TranslationHelper;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class FamilyRelationship
 * @package App\Modules\People\Entity
 * @ORM\Entity(repositoryClass="App\Modules\People\Repository\FamilyRelationshipRepository")
 * @ORM\Table(name="FamilyRelationship",
 *     uniqueConstraints={@ORM\UniqueConstraint(name="family_care_giver_student", columns={"family","care_giver","student"})},
 *     indexes={@ORM\Index(name="family",columns={"family"}),
 *     @ORM\Index(name="care_giver",columns={"care_giver"}),
 *     @ORM\Index(name="student",columns={"student"})})
 * @UniqueEntity({"family","careGiver","student"})
 */
class FamilyRelationship extends AbstractEntity
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
     * @var Family|null
     * @ORM\ManyToOne(targetEntity="App\Modules\People\Entity\Family")
     * @ORM\JoinColumn(name="family", referencedColumnName="id", nullable=false)
     * @Assert\NotBlank()
     */
    private $family;

    /**
     * @var FamilyMemberCareGiver|null
     * @ORM\ManyToOne(targetEntity="FamilyMemberCareGiver",inversedBy="relationships")
     * @ORM\JoinColumn(name="care_giver",referencedColumnName="id",nullable=false)
     * @Assert\NotBlank()
     */
    private $careGiver;

    /**
     * @var FamilyMemberStudent|null
     * @ORM\ManyToOne(targetEntity="FamilyMemberStudent",inversedBy="relationships")
     * @ORM\JoinColumn(name="student",referencedColumnName="id",nullable=false)
     * @Assert\NotBlank()
     */
    private $student;

    /**
     * @var string|null
     * @ORM\Column(length=31,nullable=true)
     * @Assert\Choice(callback="getRelationshipList")
     */
    private $relationship;

    /**
     * @var array
     */
    private static $relationshipList = [
        'Mother',
        'Father',
        'Step-Mother',
        'Step-Father',
        'Adoptive Parent',
        'Guardian',
        'Grandmother',
        'Grandfather',
        'Aunt',
        'Uncle',
        'Nanny/Helper',
        'Other',
    ];

    /**
     * FamilyRelationship constructor.
     * @param Family|null $family
     * @param FamilyMemberCareGiver|null $careGiver
     * @param FamilyMemberStudent|null $student
     */
    public function __construct(?Family $family = null, ?FamilyMemberCareGiver $careGiver = null, ?FamilyMemberStudent $student = null)
    {
        $this->family = $family;
        $this->careGiver = $careGiver;
        $this->student = $student;
    }

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
     * @return FamilyRelationship
     */
    public function setId(?string $id): FamilyRelationship
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return Family|null
     */
    public function getFamily(): ?Family
    {
        return $this->family;
    }

    /**
     * @param Family|null $family
     * @return FamilyRelationship
     */
    public function setFamily(?Family $family): FamilyRelationship
    {
        $this->family = $family;
        return $this;
    }

    /**
     * @return FamilyMemberCareGiver|null
     */
    public function getCareGiver(): ?FamilyMemberCareGiver
    {
        return $this->careGiver;
    }

    /**
     * @param FamilyMemberCareGiver|null $careGiver
     * @return FamilyRelationship
     */
    public function setCareGiver(?FamilyMemberCareGiver $careGiver): FamilyRelationship
    {
        $this->careGiver = $careGiver;
        return $this;
    }

    /**
     * @return FamilyMemberStudent|null
     */
    public function getStudent(): ?FamilyMemberStudent
    {
        return $this->student;
    }

    /**
     * @param FamilyMemberStudent|null $student
     * @return FamilyRelationship
     */
    public function setStudent(?FamilyMemberStudent $student): FamilyRelationship
    {
        $this->student = $student;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getRelationship(): ?string
    {
        return $this->relationship;
    }

    /**
     * @param string|null $relationship
     * @return FamilyRelationship
     */
    public function setRelationship(?string $relationship): FamilyRelationship
    {
        $this->relationship = $relationship;
        return $this;
    }

    /**
     * @return array
     */
    public static function getRelationshipList(): array
    {
        return self::$relationshipList;
    }

    /**
     * __toString
     * @return string
     */
    public function __toString(): string
    {
        return $this->getFamily()->__toString() . ': ' . $this->getAdult()->getPerson()->formatName(['style' => 'formal']) . ' is ' . $this->getRelationship() . ' of ' . $this->getChild()->getPerson()->formatName(['style' => 'long']);
    }

    /**
     * isEqualTo
     * @param FamilyRelationship $relationship
     * @return bool
     */
    public function isEqualTo(FamilyRelationship $relationship): bool
    {
        if (!$relationship->getFamily()->isEqualTo($this->getFamily()))
            return false;
        if (!$relationship->getCareGiver()->isEqualTo($this->getCareGiver()))
            return false;
        if (!$relationship->getStudent()->isEqualTo($this->getStudent()))
            return false;
        return true;
    }

    /**
     * toArray
     * @param string|null $name
     * @return array
     */
    public function toArray(?string $name = null): array
    {
        if ($name === 'form')
        {
            TranslationHelper::setDomain('People');
            return [
                'care_giver' => TranslationHelper::translate('{name} is the', ['{name}' => $this->getCareGiver()->getPerson()->getFullName()]),
                'student' => TranslationHelper::translate('of {name}', ['{name}' => $this->getStudent()->getPerson()->formatName(['title' => false, 'preferredName' => false])]),
            ];
        }
        return [];
    }
}
