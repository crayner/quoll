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

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\PersistentCollection;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class FamilyMemberAdult
 * @package App\Modules\People\Entity
 * @ORM\Entity(repositoryClass="App\Modules\People\Repository\FamilyMemberAdultRepository")
 * @UniqueEntity({"contactPriority","family"})
 */
class FamilyMemberAdult extends FamilyMember
{
    /**
     * @var string|null
     * @ORM\Column(length=1)
     * @Assert\Choice({"Y","N"})
     */
    private $childDataAccess = 'N';

    /**
     * @var int|null
     * @ORM\Column(type="smallint",options={"default": 1})
     * @Assert\NotBlank()
     * @Assert\Range(min=1,max=99)
     */
    private $contactPriority;

    /**
     * @var string|null
     * @ORM\Column(length=1)
     * @Assert\Choice(callback="getBooleanList")
     */
    private $contactCall = 'N';

    /**
     * @var string|null
     * @ORM\Column(length=1, name="contact_SMS")
     * @Assert\Choice(callback="getBooleanList")
     */
    private $contactSMS = 'N';

    /**
     * @var string|null
     * @ORM\Column(length=1)
     * @Assert\Choice(callback="getBooleanList")
     */
    private $contactEmail = 'N';

    /**
     * @var string|null
     * @ORM\Column(length=1)
     * @Assert\Choice(callback="getBooleanList")
     */
    private $contactMail = 'N';

    /**
     * @var Collection|FamilyRelationship[]
     * @ORM\OneToMany(targetEntity="FamilyRelationship",mappedBy="adult",orphanRemoval=true)
     */
    private $relationships;

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
     * @return FamilyMemberAdult
     */
    public function setChildDataAccess(?string $childDataAccess): FamilyMemberAdult
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
     * @return FamilyMemberAdult
     */
    public function setContactPriority(?int $contactPriority): FamilyMemberAdult
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
     * @return FamilyMemberAdult
     */
    public function setContactCall(?string $contactCall): FamilyMemberAdult
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
     * @return FamilyMemberAdult
     */
    public function setContactSMS(?string $contactSMS): FamilyMemberAdult
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
     * @return FamilyMemberAdult
     */
    public function setContactEmail(?string $contactEmail): FamilyMemberAdult
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
     * @return FamilyMemberAdult
     */
    public function setContactMail(?string $contactMail): FamilyMemberAdult
    {
        $this->contactMail = $contactMail;
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
     * @return FamilyMemberAdult
     */
    public function setRelationships(Collection $relationships): FamilyMemberAdult
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
        return parent::toArray('adult');
    }

    public function create(): array
    {
        return [];
    }
}