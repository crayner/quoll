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
     * @var boolean|null
     * @ORM\Column(type="boolean")
     */
    private $childDataAccess = false;

    /**
     * @var boolean|null
     * @ORM\Column(type="boolean")
     */
    private $contactCall = false;

    /**
     * @var boolean|null
     * @ORM\Column(type="boolean", name="contact_SMS")
     */
    private $contactSMS = false;

    /**
     * @var boolean|null
     * @ORM\Column(type="boolean")
     */
    private $contactEmail = false;

    /**
     * @var boolean|null
     * @ORM\Column(type="boolean")
     */
    private $contactMail = false;

    /**
     * @var Collection|FamilyRelationship[]
     * @ORM\OneToMany(targetEntity="FamilyRelationship",mappedBy="adult",orphanRemoval=true)
     */
    private $relationships;

    /**
     * FamilyMemberAdult constructor.
     * @param Family|null $family
     */
    public function __construct(?Family $family = null)
    {
         $this->setRelationships(new ArrayCollection());
         parent::__construct($family);
    }

    /**
     * @return bool
     */
    public function getChildDataAccess(): bool
    {
        return (bool)$this->childDataAccess;
    }

    /**
     * @param bool|null $childDataAccess
     * @return FamilyMemberAdult
     */
    public function setChildDataAccess(?bool $childDataAccess): FamilyMemberAdult
    {
        $this->childDataAccess = (bool)$childDataAccess;
        return $this;
    }

    /**
     * @return bool
     */
    public function getContactCall(): bool
    {
        return (bool)$this->contactCall;
    }

    /**
     * @param bool|null $contactCall
     * @return FamilyMemberAdult
     */
    public function setContactCall(?bool $contactCall): FamilyMemberAdult
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
     * @return FamilyMemberAdult
     */
    public function setContactSMS(?bool $contactSMS): FamilyMemberAdult
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
     * @return FamilyMemberAdult
     */
    public function setContactEmail(?bool $contactEmail): FamilyMemberAdult
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
     * @return FamilyMemberAdult
     */
    public function setContactMail(?bool $contactMail): FamilyMemberAdult
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