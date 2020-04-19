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
namespace App\Modules\People\Entity;

use App\Manager\EntityInterface;
use App\Manager\Traits\BooleanList;
use App\Util\TranslationHelper;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class FamilyAdult
 * @package App\Modules\People\Entity
 * @ORM\Entity(repositoryClass="App\Modules\People\Repository\FamilyAdultRepository")
 * @ORM\Table(options={"auto_increment": 1}, name="FamilyAdult", indexes={@ORM\Index(name="person", columns={"person"})}, uniqueConstraints={@ORM\UniqueConstraint(name="familyContactPriority", columns={"family","contactPriority"}), @ORM\UniqueConstraint(name="familymember", columns={"family","person"})})
 * @UniqueEntity(fields={"family","person"},errorPath="person")
 * @UniqueEntity(fields={"family","contactPriority"},errorPath="contactPriority")
 */
class FamilyAdult implements EntityInterface
{
    use BooleanList;

    /**
     * @var integer|null
     * @ORM\Id()
     * @ORM\Column(type="integer",columnDefinition="INT(8) UNSIGNED")
     * @ORM\GeneratedValue
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
     * @var Person|null
     * @ORM\ManyToOne(targetEntity="App\Modules\People\Entity\Person", inversedBy="adults")
     * @ORM\JoinColumn(name="person", referencedColumnName="id", nullable=false)
     * @Assert\NotBlank()
     */
    private $person;

    /**
     * @var string|null
     * @ORM\Column(type="text",nullable=true)
     */
    private $comment;

    /**
     * @var string|null
     * @ORM\Column(length=1, name="childDataAccess")
     * @Assert\Choice({"Y","N"})
     */
    private $childDataAccess = 'N';

    /**
     * @var int|null
     * @ORM\Column(type="smallint", name="contactPriority", options={"default": 1}, columnDefinition="INT(2)")
     * @Assert\NotBlank()
     * @Assert\Range(min=1,max=99)
     */
    private $contactPriority;

    /**
     * @var string|null
     * @ORM\Column(length=1, name="contactCall")
     * @Assert\Choice({"Y","N"})
     */
    private $contactCall = 'N';

    /**
     * @var string|null
     * @ORM\Column(length=1, name="contactSMS")
     * @Assert\Choice({"Y","N"})
     */
    private $contactSMS = 'N';

    /**
     * @var string|null
     * @ORM\Column(length=1, name="contactEmail")
     * @Assert\Choice({"Y","N"})
     */
    private $contactEmail = 'N';

    /**
     * @var string|null
     * @ORM\Column(length=1, name="contactMail")
     * @Assert\Choice({"Y","N"})
     */
    private $contactMail = 'N';

    /**
     * @var Collection|FamilyRelationship[]
     * @ORM\OneToMany(targetEntity="App\Modules\People\Entity\FamilyRelationship",mappedBy="adult",orphanRemoval=true)
     */
    private $relationships;

    /**
     * FamilyAdult constructor.
     * @param Family|null $family
     */
    public function __construct(?Family $family = null)
    {
        $this->family = $family;
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
     * @return FamilyAdult
     */
    public function setId(?int $id): FamilyAdult
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
     * @return FamilyAdult
     */
    public function setFamily(?Family $family): FamilyAdult
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
     * @return FamilyAdult
     */
    public function setPerson(?Person $person): FamilyAdult
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
     * @return FamilyAdult
     */
    public function setComment(?string $comment): FamilyAdult
    {
        $this->comment = $comment;
        return $this;
    }

    /**
     * isChildDataAccess
     * @return bool
     */
    public function isChildDataAccess(): bool
    {
        return $this->getChildDataAccess() === 'Y';
    }

    /**
     * @return string|null
     */
    public function getChildDataAccess(): ?string
    {
        return self::checkBoolean($this->childDataAccess);
    }

    /**
     * @param string|null $childDataAccess
     * @return FamilyAdult
     */
    public function setChildDataAccess(?string $childDataAccess): FamilyAdult
    {
        $this->childDataAccess = $childDataAccess;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getContactPriority(): ?int
    {
        return $this->contactPriority;
    }

    /**
     * @param int|null $contactPriority
     * @return FamilyAdult
     */
    public function setContactPriority(?int $contactPriority): FamilyAdult
    {
        $this->contactPriority = $contactPriority;
        return $this;
    }

    /**
     * isContactCall
     * @return bool
     */
    public function isContactCall(): bool
    {
        return $this->getContactCall() === 'Y';
    }

    /**
     * getContactCall
     * @return string
     */
    public function getContactCall(): string
    {
        return self::checkBoolean($this->contactCall);
    }

    /**
     * @param string|null $contactCall
     * @return FamilyAdult
     */
    public function setContactCall(?string $contactCall): FamilyAdult
    {
        $this->contactCall = $contactCall;
        return $this;
    }

    /**
     * isContactSMS
     * @return bool
     */
    public function isContactSMS(): bool
    {
        return $this->getContactSMS() === 'Y';
    }

    /**
     * @return string
     */
    public function getContactSMS(): string
    {
        return self::checkBoolean($this->contactSMS);
    }

    /**
     * @param string|null $contactSMS
     * @return FamilyAdult
     */
    public function setContactSMS(?string $contactSMS): FamilyAdult
    {
        $this->contactSMS = $contactSMS;
        return $this;
    }

    /**
     * isContactEmail
     * @return bool
     */
    public function isContactEmail(): bool
    {
        return $this->getContactEmail() === 'Y';
    }

    /**
     * @return string
     */
    public function getContactEmail(): string
    {
        return self::checkBoolean($this->contactEmail);
    }

    /**
     * @param string|null $contactEmail
     * @return FamilyAdult
     */
    public function setContactEmail(?string $contactEmail): FamilyAdult
    {
        $this->contactEmail = $contactEmail;
        return $this;
    }

    /**
     * isContactMail
     * @return bool
     */
    public function isContactMail(): bool
    {
        return $this->getContactMail() === 'Y';
    }

    /**
     * @return string
     */
    public function getContactMail(): string
    {
        return self::checkBoolean($this->contactMail);
    }

    /**
     * @param string|null $contactMail
     * @return FamilyAdult
     */
    public function setContactMail(?string $contactMail): FamilyAdult
    {
        $this->contactMail = $contactMail;
        return $this;
    }

    /**
     * @return Collection|FamilyRelationship[]
     */
    public function getRelationships()
    {
        return $this->relationships;
    }

    /**
     * Relationships.
     *
     * @param Collection|FamilyRelationship[] $relationships
     * @return FamilyAdult
     */
    public function setRelationships($relationships)
    {
        $this->relationships = $relationships;
        return $this;
    }

    /**
     * __toString
     * @return string
     */
    public function __toString(): string
    {
        return $this->getFamily()->getName() . ': ' . $this->getPerson()->formatName();
    }

    /**
     * toArray
     * @param string|null $name
     * @return array
     */
    public function toArray(?string $name = null): array
    {
        $person = $this->getPerson();

        return [
            'fullName' => $person->formatName(['style' => 'formal']),
            'status' => TranslationHelper::translate($person->getStatus(), [], 'People'),
            'comment' => $this->getComment(),
            'person_id' => $this->getPerson()->getId(),
            'adult_id' => $this->getId(),
            'family_id' => $this->getFamily()->getId(),
            'childDataAccess' => TranslationHelper::translate(($this->isChildDataAccess() ? 'Yes' : 'No'), [], 'messages'),
            'phone' => TranslationHelper::translate(($this->isContactCall() ? 'Yes' : 'No'), [], 'messages'),
            'sms' => TranslationHelper::translate(($this->isContactSMS() ? 'Yes' : 'No'), [], 'messages'),
            'email' => TranslationHelper::translate(($this->isContactEmail() ? 'Yes' : 'No'), [], 'messages'),
            'mail' => TranslationHelper::translate(($this->isContactMail() ? 'Yes' : 'No'), [], 'messages'),
            'contactPriority' => $this->getContactPriority(),
            'id' => $this->getId(),
        ];
    }

    /**
     * isEqualTo
     * @param FamilyAdult $adult
     * @return bool
     */
    public function isEqualTo(FamilyAdult $adult): bool
    {
        if($this->getPerson() === null || $adult->getPerson() === null || $this->getFamily() === null || $adult->getFamily() === null)
            return false;
        if (!$adult->getPerson()->isEqualTo($this->getPerson()))
            return false;
        if (!$adult->getFamily()->isEqualTo($this->getFamily()))
            return false;
        return true;
    }

    /**
     * create
     * @return string
     */
    public function create(): string
    {
        return "CREATE TABLE `__prefix__FamilyAdult` (
                    `id` int(8) UNSIGNED NOT NULL AUTO_INCREMENT,
                    `comment` longtext CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
                    `childDataAccess` varchar(1) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
                    `contactPriority` int(2) DEFAULT NULL,
                    `contactCall` varchar(1) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
                    `contactSMS` varchar(1) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
                    `contactEmail` varchar(1) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
                    `contactMail` varchar(1) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
                    `family` int(7) UNSIGNED DEFAULT NULL,
                    `person` int(10) UNSIGNED DEFAULT NULL,
                    PRIMARY KEY (`id`),
                    UNIQUE KEY `familyMember` (`family`,`person`),
                    KEY `family` (`family`),
                    KEY `person` (`person`),
                    KEY `familyContactPriority` (`family`,`contactPriority`) USING BTREE
                ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
";
    }

    /**
     * foreignConstraints
     * @return string
     */
    public function foreignConstraints(): string
    {
        return "ALTER TABLE `__prefix__FamilyAdult`
                    ADD CONSTRAINT FOREIGN KEY (`family`) REFERENCES `__prefix__Family` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
                    ADD CONSTRAINT FOREIGN KEY (`person`) REFERENCES `__prefix__Person` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT;";
    }

    /**
     * coreData
     * @return string
     */
    public function coreData(): string
    {
        return "";
    }
}