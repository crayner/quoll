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

use App\Manager\AbstractEntity;
use App\Manager\Traits\BooleanList;
use App\Modules\People\Validator\FamilyMemberNotBlank;
use App\Modules\Student\Entity\Student;
use App\Modules\Student\Util\StudentHelper;
use App\Util\ImageHelper;
use App\Util\TranslationHelper;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class FamilyMember
 * @package App\Modules\People\Entity
 * @ORM\Entity(repositoryClass="App\Modules\People\Repository\FamilyMemberRepository")
 * @ORM\Table(name="FamilyMember",
 *     uniqueConstraints={
 *     @ORM\UniqueConstraint(name="family_care_giver",columns={"family","care_giver"}),
 *     @ORM\UniqueConstraint(name="family_student",columns={"family","student"}),
 *     @ORM\UniqueConstraint(name="family_contact_priority",columns={"family","contact_priority"})},
 *     indexes={
 *     @ORM\Index(name="care_giver",columns={"care_giver"}),
 *     @ORM\Index(name="student",columns={"student"}),
 *     @ORM\Index(name="family",columns={"family"}),
 *     @ORM\Index(name="member_type",columns={"member_type"})}
 * )
 * @UniqueEntity({"student","family"},ignoreNull=true)
 * @UniqueEntity({"careGiver","family"},ignoreNull=true)
 * @UniqueEntity({"contactPriority","family"},ignoreNull=true)
 * @ORM\MappedSuperclass()
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="member_type",type="string",length=191)
 * @ORM\DiscriminatorMap({"care_giver" = "FamilyMemberCareGiver", "student" = "FamilyMemberStudent", "member" = "FamilyMember"})
 * @FamilyMemberNotBlank()
 */
class FamilyMember extends AbstractEntity
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
     * @ORM\ManyToOne(targetEntity="App\Modules\People\Entity\Family", inversedBy="members")
     * @ORM\JoinColumn(name="family",referencedColumnName="id",nullable=false)
     * @Assert\NotBlank()
     */
    private $family;

    /**
     * @var string|null
     * @ORM\Column(type="text",nullable=true)
     */
    private $comment;

    /**
     * FamilyMember constructor.
     * @param Family|null $family
     */
    public function __construct(?Family $family = null)
    {
        $this->setFamily($family);
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
     * @return FamilyMember
     */
    public function setId(?string $id): FamilyMember
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
     * @return FamilyMember
     */
    public function setFamily(?Family $family): FamilyMember
    {
        $this->family = $family;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getComment(): ?string
    {
        return $this->comment;
    }

    /**
     * @param string|null $comment
     * @return FamilyMember
     */
    public function setComment(?string $comment): FamilyMember
    {
        $this->comment = $comment;
        return $this;
    }

    /**
     * __toString
     * @return string
     */
    public function __toString(): string
    {
        if ($this->getFamily() && $this->getCareGiver())
            return $this->getFamily()->getName() . ': ' . $this->getCareGiver()->getPerson()->getFullName();
        if ($this->getFamily() && $this->getStudent())
            return $this->getFamily()->getName() . ': ' . $this->getStudent()->getPerson()->getFullName();
        if ($this->getFamily())
            return $this->getFamily()->getName() . ': UnKnown ' . $this->getId();
        if ($this->getCareGiver())
            return 'Unknown : ' . $this->getCareGiver()->getPerson()->getFullName() . ' ' . $this->getId();
        if ($this->getStudent())
            return 'Unknown : ' . $this->getStudent()->getPerson()->getFullName() . ' ' . $this->getId();
        return 'No Idea, so ' . $this->getId();
    }

    /**
     * toArray
     * @param string|null $name
     * @return array
     */
    public function toArray(?string $name = null): array
    {
        $person = $this->getPerson();
        if ($name === 'care_giver') {
            return [
                'photo' => ImageHelper::getAbsoluteImageURL('File', $person->getPersonalDocumentation()->getPersonalImage()),
                'fullName' => $person->formatName('Standard'),
                'status' => TranslationHelper::translate($person->getStatus(), [], 'People'),
                'roll' => StudentHelper::getCurrentRollGroup($person),
                'comment' => $this->getComment(),
                'family_id' => $this->getFamily()->getId(),
                'care_giver_id' => $this->getId(),
                'person_id' => $person->getId(),
                'id' => $this->getId(),
                'childDataAccess' => TranslationHelper::translate($this->isChildDataAccess() ? 'Yes' : 'No', [], 'messages'),
                'contactPriority' => $this->getContactPriority(),
                'phone' => TranslationHelper::translate($this->isContactCall() ? 'Yes' : 'No', [], 'messages'),
                'sms' => TranslationHelper::translate($this->isContactSMS() ? 'Yes' : 'No', [], 'messages'),
                'email' => TranslationHelper::translate($this->isContactEmail() ? 'Yes' : 'No', [], 'messages'),
                'mail' => TranslationHelper::translate($this->isContactMail() ? 'Yes' : 'No', [], 'messages'),
            ];

        }
        if ($name === 'student') {
            return [
                'photo' => ImageHelper::getAbsoluteImageURL('File', $person->getPersonalDocumentation()->getPersonalImage()),
                'fullName' => $person->formatName('Standard'),
                'status' => TranslationHelper::translate($person->getStatus(), [], 'People'),
                'roll' => StudentHelper::getCurrentRollGroup($this->getStudent()),
                'comment' => $this->getComment(),
                'family_id' => $this->getFamily()->getId(),
                'student_id' => $this->getId(),
                'person_id' => $person->getId(),
                'id' => $this->getId(),
            ];

        }
        return [
            'photo' => ImageHelper::getAbsoluteImageURL('File', $person->getImage240()),
            'fullName' => $person->formatName('Standard'),
            'status' => TranslationHelper::translate($person->getStatus(), [], 'People'),
            'roll' => StudentHelper::getCurrentRollGroup($person),
            'comment' => $this->getComment(),
            'family_id' => $this->getFamily()->getId(),
            'person_id' => $person->getId(),
            'id' => $this->getId(),
        ];
    }

    /**
     * getPerson
     * @return Person|null
     * 24/07/2020 12:56
     */
    public function getPerson(): ?Person
    {
        if (method_exists($this, 'getCaregiver') && $this->getCareGiver()) return $this->getCaregiver()->getPerson();
        if (method_exists($this, 'getStudent') && $this->getStudent()) return $this->getStudent()->getPerson();
        return null;
    }

}
