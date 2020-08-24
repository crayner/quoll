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

use App\Modules\Student\Util\StudentHelper;
use App\Provider\ProviderFactory;
use App\Util\ImageHelper;
use App\Util\StringHelper;
use App\Util\TranslationHelper;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\PersistentCollection;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * Class FamilyMemberAdult
 * @package App\Modules\People\Entity
 * @ORM\Entity(repositoryClass="App\Modules\People\Repository\FamilyMemberCareGiverRepository")
 * @UniqueEntity({"contactPriority","family"})
 */
class FamilyMemberCareGiver extends FamilyMember
{
    /**
     * @var boolean|null
     * @ORM\Column(type="boolean")
     */
    private bool $childDataAccess = false;

    /**
     * @var boolean|null
     * @ORM\Column(type="boolean")
     */
    private bool $contactCall = false;

    /**
     * @var boolean|null
     * @ORM\Column(type="boolean", name="contact_SMS")
     */
    private bool $contactSMS = false;

    /**
     * @var boolean|null
     * @ORM\Column(type="boolean")
     */
    private bool $contactEmail = false;

    /**
     * @var boolean|null
     * @ORM\Column(type="boolean")
     */
    private bool $contactMail = false;

    /**
     * @var Collection|FamilyRelationship[]|null
     * @ORM\OneToMany(targetEntity="FamilyRelationship",mappedBy="careGiver",orphanRemoval=true)
     */
    private ?Collection $relationships;

    /**
     * FamilyMemberCareGiver constructor.
     * @param Family|null $family
     */
    public function __construct(?Family $family = null)
    {
        $this->setContactPriority(ProviderFactory::getRepository(FamilyMemberCareGiver::class)->getNextContactPriority($family));
        $this->setRelationships(new ArrayCollection());
        parent::__construct($family);
    }

    /**
     * @return bool
     */
    public function isChildDataAccess(): bool
    {
        return (bool) $this->childDataAccess;
    }

    /**
     * @param bool|null $childDataAccess
     * @return FamilyMemberCareGiver
     */
    public function setChildDataAccess(?bool $childDataAccess): FamilyMemberCareGiver
    {
        $this->childDataAccess = (bool)$childDataAccess;
        return $this;
    }

    /**
     * @return bool
     */
    public function isContactCall(): bool
    {
        return (bool)$this->contactCall;
    }

    /**
     * @param bool|null $contactCall
     * @return FamilyMemberCareGiver
     */
    public function setContactCall(?bool $contactCall): FamilyMemberCareGiver
    {
        $this->contactCall = (bool)$contactCall;
        return $this;
    }

    /**
     * @return bool
     */
    public function isContactSMS(): bool
    {
        return (bool)$this->contactSMS;
    }

    /**
     * @param bool|null $contactSMS
     * @return FamilyMemberCareGiver
     */
    public function setContactSMS(?bool $contactSMS): FamilyMemberCareGiver
    {
        $this->contactSMS = (bool)$contactSMS;
        return $this;
    }

    /**
     * @return bool
     */
    public function isContactEmail(): bool
    {
        return (bool)$this->contactEmail;
    }

    /**
     * @param bool|null $contactEmail
     * @return FamilyMemberCareGiver
     */
    public function setContactEmail(?bool $contactEmail): FamilyMemberCareGiver
    {
        $this->contactEmail = (bool)$contactEmail;
        return $this;
    }

    /**
     * @return bool
     */
    public function isContactMail(): bool
    {
        return (bool)$this->contactMail;
    }

    /**
     * @param bool|null $contactMail
     * @return FamilyMemberCareGiver
     */
    public function setContactMail(?bool $contactMail): FamilyMemberCareGiver
    {
        $this->contactMail = (bool)$contactMail;
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
     * @return FamilyMemberCareGiver
     */
    public function setRelationships(Collection $relationships): FamilyMemberCareGiver
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
        $person = $this->getPerson();
        return [
            'id' => $this->getId(),
            'photo' => ImageHelper::getAbsoluteImageURL('File', $person->getPersonalDocumentation()->getPersonalImage()),
            'fullName' => $person->formatName('Standard'),
            'status' => TranslationHelper::translate($person->getStatus(), [], 'People'),
            'roll' => StudentHelper::getCurrentRollGroup($person),
            'comment' => $this->getComment(),
            'family_id' => $this->getFamily()->getId(),
            'care_giver_id' => $this->getCareGiver()->getId(),
            'person_id' => $person->getId(),
            'childDataAccess' => TranslationHelper::translate($this->isChildDataAccess() ? 'Yes' : 'No', [], 'messages'),
            'contactPriority' => $this->getContactPriority(),
            'phone' => StringHelper::getYesNo($this->isContactCall()),
            'sms' => TranslationHelper::translate($this->isContactSMS() ? 'Yes' : 'No', [], 'messages'),
            'email' => TranslationHelper::translate($this->isContactEmail() ? 'Yes' : 'No', [], 'messages'),
            'mail' => TranslationHelper::translate($this->isContactMail() ? 'Yes' : 'No', [], 'messages'),
        ];
    }

    /**
     * isEqualTo
     * @param FamilyMemberCareGiver $careGiver
     * @return bool
     * 26/07/2020 09:39
     */
    public function isEqualTo(FamilyMemberCareGiver $careGiver): bool
    {
        return $this->getFamily()->isEqualTo($careGiver->getFamily()) && $this->getCareGiver()->isEqualTo($careGiver->getCareGiver());
    }
}
