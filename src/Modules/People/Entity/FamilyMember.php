<?php
/**
 * Created by PhpStorm.
 *
 * quoll
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

use App\Manager\EntityInterface;
use App\Manager\Traits\BooleanList;
use App\Modules\Students\Util\StudentHelper;
use App\Util\ImageHelper;
use App\Util\TranslationHelper;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class FamilyMember
 * @package App\Modules\People\Entity
 * @ORM\Entity(repositoryClass="App\Modules\People\Repository\FamilyMemberRepository")
 * @ORM\Table(name="FamilyMember",options={"auto_increment": 1})
 * @ORM\MappedSuperclass()
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="member_type", type="string", length=191)
 * @ORM\DiscriminatorMap({"adult" = "FamilyMemberAdult", "student" = "FamilyMemberChild", "member" = "FamilyMember"})
 */
class FamilyMember implements EntityInterface
{
    use BooleanList;

    /**
     * @var integer|null
     * @ORM\Id()
     * @ORM\Column(type="integer", columnDefinition="UNSIGNED")
     * @ORM\GeneratedValue
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
     * @var Person|null
     * @ORM\ManyToOne(targetEntity="App\Modules\People\Entity\Person", inversedBy="members")
     * @ORM\JoinColumn(name="person",referencedColumnName="id",nullable=false)
     * @Assert\NotBlank()
     */
    private $person;

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
        $this->setRelationships(new ArrayCollection());
    }

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param int|null $id
     * @return FamilyMember
     */
    public function setId(?int $id): FamilyMember
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
     * @return Person|null
     */
    public function getPerson(): ?Person
    {
        return $this->person;
    }

    /**
     * @param Person|null $person
     * @return FamilyMember
     */
    public function setPerson(?Person $person): FamilyMember
    {
        $this->person = $person;
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
        if ($this->getFamily() && $this->getPerson())
            return $this->getFamily()->getName() . ': ' . $this->getPerson()->formatName();
        if ($this->getFamily())
            return $this->getFamily()->getName() . ': UunKnown ' . $this->getId();
        if ($this->getPerson())
            return 'Unknown : ' . $this->getPerson()->formatName() . ' ' . $this->getId();
        return 'No Idea';
    }

    /**
     * toArray
     * @return array
     */
    public function toArray(?string $name = null): array
    {
        $person = $this->getPerson();
        if ($name === 'adult') {
            return [
                'photo' => ImageHelper::getAbsoluteImageURL('File', $person->getImage240()),
                'fullName' => $person->formatName(['title' => false, 'preferred' => false]),
                'status' => TranslationHelper::translate($person->getStatus(), [], 'People'),
                'roll' => StudentHelper::getCurrentRollGroup($person),
                'comment' => $this->getComment(),
                'family_id' => $this->getFamily()->getId(),
                'adult_id' => $this->getId(),
                'person_id' => $this->getPerson()->getId(),
                'id' => $this->getId(),
                'childDataAccess' => TranslationHelper::translate($this->isChildDataAccess() ? 'Yes' : 'No', [], 'messages'),
                'contactPriority' => $this->getContactPriority(),
                'phone' => TranslationHelper::translate($this->isContactCall() ? 'Yes' : 'No', [], 'messages'),
                'sms' => TranslationHelper::translate($this->isContactSMS() ? 'Yes' : 'No', [], 'messages'),
                'email' => TranslationHelper::translate($this->isContactEmail() ? 'Yes' : 'No', [], 'messages'),
                'mail' => TranslationHelper::translate($this->isContactMail() ? 'Yes' : 'No', [], 'messages'),
            ];

        }
        if ($name === 'child') {
            return [
                'photo' => ImageHelper::getAbsoluteImageURL('File', $person->getImage240()),
                'fullName' => $person->formatName(['title' => false, 'preferred' => false]),
                'status' => TranslationHelper::translate($person->getStatus(), [], 'People'),
                'roll' => StudentHelper::getCurrentRollGroup($person),
                'comment' => $this->getComment(),
                'family_id' => $this->getFamily()->getId(),
                'child_id' => $this->getId(),
                'person_id' => $this->getPerson()->getId(),
                'id' => $this->getId(),
            ];

        }
        return [
            'photo' => ImageHelper::getAbsoluteImageURL('File', $person->getImage240()),
            'fullName' => $person->formatName(['title' => false, 'preferred' => false]),
            'status' => TranslationHelper::translate($person->getStatus(), [], 'People'),
            'roll' => StudentHelper::getCurrentRollGroup($person),
            'comment' => $this->getComment(),
            'family_id' => $this->getFamily()->getId(),
            'person_id' => $this->getPerson()->getId(),
            'id' => $this->getId(),
        ];
    }

    public function create(): string
    {
        return "CREATE TABLE `__prefix__FamilyMember` (id INT(10) UNSIGNED, family INT(7) UNSIGNED, person INT(10) UNSIGNED AUTO_INCREMENT, comment LONGTEXT DEFAULT NULL, member_type VARCHAR(255) NOT NULL, childDataAccess VARCHAR(1) DEFAULT NULL, contactPriority INT(2), contactCall VARCHAR(1) DEFAULT NULL, contactSMS VARCHAR(1) DEFAULT NULL, contactEmail VARCHAR(1) DEFAULT NULL, contactMail VARCHAR(1) DEFAULT NULL, INDEX person (person), INDEX family (family), UNIQUE INDEX family_member (family, person), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB AUTO_INCREMENT = 1";
    }

    public function foreignConstraints(): string
    {
        return "ALTER TABLE `__prefix__FamilyMember` 
         ADD CONSTRAINT FOREIGN KEY (family) REFERENCES __prefix__Family (id),
         ADD CONSTRAINT FOREIGN KEY (person) REFERENCES __prefix__Person (id);";
    }

    public function coreData(): string
    {
        return '';
    }
    
    /**
     * isEqualTo
     * @param FamilyMember $member
     * @return bool
     */
    public function isEqualTo(FamilyMember $member): bool
    {
        if($this->getPerson() === null || $member->getPerson() === null || $this->getFamily() === null || $member->getFamily() === null)
            return false;
        if (!$member->getPerson()->isEqualTo($this->getPerson()))
            return false;
        if (!$member->getFamily()->isEqualTo($this->getFamily()))
            return false;
        return true;
    }
}