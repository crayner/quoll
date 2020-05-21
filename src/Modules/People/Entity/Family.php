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
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use App\Modules\People\Manager\FamilyManager;
use Doctrine\ORM\PersistentCollection;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class Family
 * @package App\Modules\People\Entity
 * @ORM\Entity(repositoryClass="App\Modules\People\Repository\FamilyRepository")
 * @ORM\Table(name="Family",
 *     uniqueConstraints={@ORM\UniqueConstraint(name="name",columns={"name"}),
 *     @ORM\UniqueConstraint(name="familySync",columns={"familySync"})},
 *     indexes={@ORM\Index(name="physical_address", columns={"physical_address"}),
 *     @ORM\Index(name="postall_address", columns={"postal_address"})})
 * @ORM\HasLifecycleCallbacks()
 * @UniqueEntity(
 *     fields={"familySync"},
 *     ignoreNull=true
 * )
 * @UniqueEntity(fields={"name"})
 */
class Family implements EntityInterface
{
    CONST VERSION = '20200401';
/**
     * @var string|null
     * @ORM\Id()
     * @ORM\Column(type="guid")
     * @ORM\GeneratedValue(strategy="UUID")
     */
    private $id;

    /**
     * @var string|null
     * @ORM\Column(length=100,unique=true)
     * @Assert\NotBlank()
     * @Assert\Length(max=100)
     */
    private $name;

    /**
     * @var string|null
     * @ORM\Column(length=100,name="formal_name",options={"comment": "The formal name to be used for addressing the family (e.g. Mr. & Mrs. Smith)"})
     * @Assert\NotBlank()
     * @Assert\Length(max=100)
     */
    private $formalName;

    /**
     * @var Address|null
     * @ORM\ManyToOne(targetEntity="App\Modules\People\Entity\Address")
     * @ORM\JoinColumn(name="physical_address", referencedColumnName="id",nullable=true)
     */
    private $physicalAddress;

    /**
     * @var Address|null
     * Only if necessary
     * @ORM\ManyToOne(targetEntity="App\Modules\People\Entity\Address")
     * @ORM\JoinColumn(name="postal_address", referencedColumnName="id",nullable=true)
     */
    private $postalAddress;

    /**
     * @var string|null
     * @ORM\Column(length=12)
     * @Assert\Choice({"Married","Separated","Divorced","De Facto","Other"})
     */
    private $status = 'Unknown';

    /**
     * @var array
     */
    private static $statusList = ['Married', 'Separated', 'Divorced', 'De Facto', 'Other'];

    /**
     * @var string|null
     * @ORM\Column(length=30, name="languageHomePrimary")
     * @Assert\Language()
     * @Assert\NotBlank()
     */
    private $languageHomePrimary;

    /**
     * @var string|null
     * @ORM\Column(length=30, name="languageHomeSecondary", nullable=true)
     * @Assert\Language()
     */
    private $languageHomeSecondary;

    /**
     * @var string|null
     * @ORM\Column(length=50, name="familySync", nullable=true, unique=true)
     */
    private $familySync;

    /**
     * @var Collection|null
     * @ORM\ManyToMany(targetEntity="App\Modules\People\Entity\Phone")
     * @ORM\JoinTable(name="FamilyPhone",
     *      joinColumns={@ORM\JoinColumn(name="family",referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="phone",referencedColumnName="id")}
     *      )
     */
    private $familyPhones;

    /**
     * @var Collection|FamilyMember[]|null
     * @ORM\OneToMany(targetEntity="App\Modules\People\Entity\FamilyMember", mappedBy="family")
     */
    private $members;

    /**
     * @return Collection
     */
    public function getFamilyPhones(): Collection
    {
        if (null === $this->familyPhones)
            $this->familyPhones = new ArrayCollection();

        if ($this->familyPhones instanceof PersistentCollection)
            $this->familyPhones->initialize();

        return $this->familyPhones;
    }

    /**
     * familyPhones.
     *
     * @param Collection|null $familyPhones
     * @return Person
     */
    public function setFamilyPhones(?Collection $familyPhones): Family
    {
        $this->familyPhones = $familyPhones;
        return $this;
    }

    /**
     * addfamilyPhone
     * @param Phone $phone
     * @return $this
     */
    public function addFamilyPhone(Phone $phone): Family
    {
        if ($this->getFamilyPhones()->contains($phone))
            return $this;

        $this->familyPhones->add($phone);

        return $this;
    }

    /**
     * removefamilyPhone
     * @param Phone $phone
     * @return $this
     */
    public function removeFamilyPhone(Phone $phone): Family
    {
        $this->getFamilyPhones()->removeElement($phone);
        return $this;
    }

    /**
     * __toString
     * @return string
     */
    public function __toString(): string
    {
        return $this->getName() ?: '';
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
     * @return Family
     */
    public function setId(?int $id): Family
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string|null $name
     * @return Family
     */
    public function setName(?string $name): Family
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getFormalName(): ?string
    {
        return $this->formalName;
    }

    /**
     * FormalName.
     *
     * @param string|null $formalName
     * @return Family
     */
    public function setFormalName(?string $formalName): Family
    {
        $this->formalName = $formalName;
        return $this;
    }

    /**
     * @return Address|null
     */
    public function getPhysicalAddress(): ?Address
    {
        return $this->physicalAddress;
    }

    /**
     * PhysicalAddress.
     *
     * @param Address|null $physicalAddress
     * @return Family
     */
    public function setPhysicalAddress(?Address $physicalAddress): Family
    {
        $this->physicalAddress = $physicalAddress;
        return $this;
    }

    /**
     * @return Address|null
     */
    public function getPostalAddress(): ?Address
    {
        return $this->postalAddress;
    }

    /**
     * PostalAddress.
     *
     * @param Address|null $postalAddress
     * @return Family
     */
    public function setPostalAddress(?Address $postalAddress): Family
    {
        $this->postalAddress = $postalAddress;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getStatus(): ?string
    {
        return $this->status;
    }

    /**
     * @param string|null $status
     * @return Family
     */
    public function setStatus(?string $status): Family
    {
        $this->status = in_array($status, self::getStatusList()) ? $status : 'Unknown';
        return $this;
    }

    /**
     * @return string|null
     */
    public function getLanguageHomePrimary(): ?string
    {
        return $this->languageHomePrimary;
    }

    /**
     * @param string|null $languageHomePrimary
     * @return Family
     */
    public function setLanguageHomePrimary(?string $languageHomePrimary): Family
    {
        $this->languageHomePrimary = $languageHomePrimary;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getLanguageHomeSecondary(): ?string
    {
        return $this->languageHomeSecondary;
    }

    /**
     * @param string|null $languageHomeSecondary
     * @return Family
     */
    public function setLanguageHomeSecondary(?string $languageHomeSecondary): Family
    {
        $this->languageHomeSecondary = $languageHomeSecondary;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getFamilySync(): ?string
    {
        return $this->familySync;
    }

    /**
     * @param string|null $familySync
     * @return Family
     */
    public function setFamilySync(?string $familySync): Family
    {
        $this->familySync = $familySync;
        return $this;
    }

    /**
     * @return array
     */
    public static function getStatusList(): array
    {
        return self::$statusList;
    }

    /**
     * @return FamilyMember[]|Collection|null
     */
    public function getMembers()
    {
        return $this->members;
    }

    /**
     * Members.
     *
     * @param FamilyMember[]|Collection|null $members
     * @return Family
     */
    public function setMembers($members)
    {
        $this->members = $members;
        return $this;
    }

    /**
     * @return FamilyMember[]|Collection|null
     */
    public function getAdults(): Collection
    {
        return $this->getMembers()->filter(function (FamilyMember $member) {
            if ($member instanceof FamilyMemberAdult)
                return $member;
        });
    }

    /**
     * @return FamilyMember[]|Collection|null
     */
    public function getChildren(): Collection
    {
        return $this->getMembers()->filter(function (FamilyMember $member) {
            if ($member instanceof FamilyMemberChild)
                return $member;
        });
    }

    /**
     * toArray
     * @param string|null $name
     * @return array
     */
    public function toArray(?string $name = NULL): array
    {
        return [
            'name' => $this->getName(),
            'status' => $this->getStatus(),
            'adults' => FamilyManager::getAdultNames($this),
            'children' => FamilyManager::getChildrenNames($this),
        ];
    }

    /**
     * isEqualTo
     * @param Family $family
     * @return bool
     */
    public function isEqualTo(Family $family)
    {
        if ($this->getId() !== $family->getId())
            return false;

        return true;
    }

    public function create(): string
    {
        return "CREATE TABLE `__prefix__Family` (
                    `id` CHAR(36) NOT NULL COMMENT '(DC2Type:guid)',
                    `name` CHAR(100) NOT NULL,
                    `formal_name` CHAR(100) NOT NULL COMMENT 'The formal name to be used for addressing the family (e.g. Mr. & Mrs. Smith)',
                    `physical_address` CHAR(36) DEFAULT NULL,
                    `postal_address` CHAR(36) DEFAULT NULL,
                    `status` CHAR(12) NOT NULL,
                    `languageHomePrimary` CHAR(30) DEFAULT NULL,
                    `languageHomeSecondary` CHAR(30) DEFAULT NULL,
                    `familySync` CHAR(50) DEFAULT NULL,
                    PRIMARY KEY (`id`),
                    UNIQUE KEY `name` (`name`),
                    UNIQUE KEY `familySync` (`familySync`),
                    KEY `physical_address` (`physical_address`),
                    KEY `postal_address` (`postal_address`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=ut8mb4_unicode_ci;
                CREATE TABLE `__prefix__FamilyPhone` (
                    `family` CHAR(36) NOT NULL,
                    `phone` CHAR(36) NOT NULL,
                    PRIMARY KEY (`family`,`phone`),
                    KEY `family` (`family`),
                    KEY `phone` (`phone`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=ut8mb4_unicode_ci;";
    }

    public function foreignConstraints(): string
    {
        return 'ALTER TABLE `__prefix__Family`
                    ADD CONSTRAINT FOREIGN KEY (`postal_address`) REFERENCES `__prefix__Address` (`id`),
                    ADD CONSTRAINT FOREIGN KEY (`physical_address`) REFERENCES `__prefix__Address` (`id`);
                ALTER TABLE `__prefix__FamilyPhone`
                    ADD CONSTRAINT FOREIGN KEY (`family`) REFERENCES `__prefix__Family` (`id`),
                    ADD CONSTRAINT FOREIGN KEY (`phone`) REFERENCES `__prefix__Phone` (`id`);';
    }

    public function coreData(): string
    {
        return '';
    }

    public static function getVersion(): string
    {
        return self::VERSION;
    }
}