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
use Doctrine\ORM\PersistentCollection;
use App\Modules\People\Manager\FamilyManager;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class Family
 * @package App\Modules\People\Entity
 * @ORM\Entity(repositoryClass="App\Modules\People\Repository\FamilyRepository")
 * @ORM\Table(options={"auto_increment": 1}, name="Family",
 *     uniqueConstraints={@ORM\UniqueConstraint(name="name",columns={"name"}),
 *     @ORM\UniqueConstraint(name="familySync",columns={"familySync"})},
 *     indexes={@ORM\Index(name="homeAddressDistrict", columns={"homeAddressDistrict"})})
 * @ORM\HasLifecycleCallbacks()
 */
class Family implements EntityInterface
{
    /**
     * @var integer|null
     * @ORM\Id
     * @ORM\Column(type="integer", columnDefinition="INT(7) UNSIGNED")
     * @ORM\GeneratedValue
     */
    private $id;

    /**
     * @var string|null
     * @ORM\Column(length=100, unique=true)
     * @Assert\NotBlank()
     * @Assert\Length(max=100)
     */
    private $name;

    /**
     * @var string|null
     * @ORM\Column(length=100, name="nameAddress", options={"comment": "The formal name to be used for addressing the family (e.g. Mr. & Mrs. Smith)"})
     * @Assert\NotBlank()
     * @Assert\Length(max=100)
     */
    private $nameAddress;

    /**
     * @var string|null
     * @ORM\Column(type="text", name="homeAddress")
     */
    private $homeAddress;

    /**
     * @var District|null
     * @ORM\ManyToOne(targetEntity="App\Modules\People\Entity\District")
     * @ORM\JoinColumn(name="homeAddressDistrict", referencedColumnName="id", nullable=true)
     */
    private $homeAddressDistrict;

    /**
     * @var string|null
     * @ORM\Column(name="homeAddressCountry")
     * @Assert\Country()
     */
    private $homeAddressCountry;

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
    public function getNameAddress(): ?string
    {
        return $this->nameAddress ?: trim(str_replace('<br />', ' & ', FamilyManager::getAdultNames($this)), ' &');
    }

    /**
     * @param string|null $nameAddress
     * @return Family
     */
    public function setNameAddress(?string $nameAddress): Family
    {
        $this->nameAddress = $nameAddress;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getHomeAddress(): ?string
    {
        return $this->homeAddress;
    }

    /**
     * @param string|null $homeAddress
     * @return Family
     */
    public function setHomeAddress(?string $homeAddress): Family
    {
        $this->homeAddress = $homeAddress;
        return $this;
    }

    /**
     * @return District|null
     */
    public function getHomeAddressDistrict(): ?District
    {
        return $this->homeAddressDistrict;
    }

    /**
     * HomeAddressDistrict.
     *
     * @param District|null $homeAddressDistrict
     * @return Family
     */
    public function setHomeAddressDistrict(?District $homeAddressDistrict): Family
    {
        $this->homeAddressDistrict = $homeAddressDistrict;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getHomeAddressCountry(): ?string
    {
        return $this->homeAddressCountry;
    }

    /**
     * @param string|null $homeAddressCountry
     * @return Family
     */
    public function setHomeAddressCountry(?string $homeAddressCountry): Family
    {
        $this->homeAddressCountry = $homeAddressCountry;
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
        return 'CREATE TABLE `__prefix__Famliy` (
                    `id` int(7) UNSIGNED NOT NULL AUTO_INCREMENT,
                    `name` varchar(100) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
                    `nameAddress` varchar(100) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL COMMENT \'The formal name to be used for addressing the family (e.g. Mr. & Mrs. Smith)\',
                    `homeAddress` longtext CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
                    `homeAddressDistrict` int(6) UNSIGNED DEFAULT NULL,
                    `homeAddressCountry` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
                    `status` varchar(12) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
                    `languageHomePrimary` varchar(30) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
                    `languageHomeSecondary` varchar(30) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
                    `familySync` varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
                    PRIMARY KEY (`id`),
                    UNIQUE KEY `name` (`name`),
                    UNIQUE KEY `familySync` (`familySync`),
                    KEY `homeAddressDistrict` (`homeAddressDistrict`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;';
    }

    public function foreignConstraints(): string
    {
        return 'ALTER TABLE `__prefix__Famliy`
                    ADD CONSTRAINT FOREIGN KEY (`homeAddressDistrict`) REFERENCES `__prefix__District` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT;';
    }

    public function coreData(): string
    {
        return '';
    }
}