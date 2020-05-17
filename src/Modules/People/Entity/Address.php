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
 * Date: 5/05/2020
 * Time: 11:14
 */
namespace App\Modules\People\Entity;

use App\Manager\EntityInterface;
use App\Modules\People\Validator\PostCode;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Intl\Countries;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class Address
 * @package App\Modules\People\Entity
 * @ORM\Entity(repositoryClass="App\Modules\People\Repository\AddressRepository")
 * @ORM\Table(options={"auto_increment": 1}, name="Address",
 *     indexes={@ORM\Index("locality",columns={"locality"})},
 *     uniqueConstraints={@ORM\UniqueConstraint("address_in_locality",columns={"street_name","property_name","flat_unit_details","street_number","locality","postCode"})})
 * @UniqueEntity(fields={"streetName","propertyName","flatUnitDetails","streetNumber","locality","postCode"},message="This address is a load of not unique.")
 * @\App\Modules\People\Validator\Address()
 * @PostCode()
 */
class Address implements EntityInterface
{
    /**
     * @var integer|null
     * @ORM\Id()
     * @ORM\Column(type="integer",columnDefinition="INT(10) UNSIGNED")
     * @ORM\GeneratedValue
     */
    private $id;

    /**
     * @var string|null
     * @ORM\Column(length=30,nullable=true,name="flat_unit_details")
     * @Assert\Length(max=30)
     */
    private $flatUnitDetails;

    /**
     * @var string|null
     * @ORM\Column(length=15,nullable=true, name="street_number")
     * @Assert\Length(max=15)
     */
    private $streetNumber;

    /**
     * @var string|null
     * @ORM\Column(length=70,nullable=true)
     * @Assert\NotBlank()
     * @Assert\Length(max=70)
     */
    private $streetName;

    /**
     * @var string|null
     * @ORM\Column(length=50,nullable=true)
     * @Assert\Length(max=50)
     */
    private $propertyName;

    /**
     * @var string|null
     * @ORM\Column(length=10,nullable=true,name="post_code")
     * @Assert\Length(max=10)
     */
    private $postCode;

    /**
     * @var Locality|null
     * @ORM\ManyToOne(targetEntity="App\Modules\People\Entity\Locality")
     * @ORM\JoinColumn(name="locality",referencedColumnName="id",nullable=true)
     * @Assert\NotBlank()
     */
    private $locality;

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Id.
     *
     * @param int|null $id
     * @return Address
     */
    public function setId(?int $id): Address
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getFlatUnitDetails(): ?string
    {
        return $this->flatUnitDetails;
    }

    /**
     * FlatUnitDetails.
     *
     * @param string|null $flatUnitDetails
     * @return Address
     */
    public function setFlatUnitDetails(?string $flatUnitDetails): Address
    {
        $this->flatUnitDetails = $flatUnitDetails;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getStreetNumber(): ?string
    {
        return $this->streetNumber;
    }

    /**
     * StreetNumber.
     *
     * @param string|null $streetNumber
     * @return Address
     */
    public function setStreetNumber(?string $streetNumber): Address
    {
        $this->streetNumber = $streetNumber;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getStreetName(): ?string
    {
        return $this->streetName;
    }

    /**
     * StreetName.
     *
     * @param string|null $streetName
     * @return Address
     */
    public function setStreetName(?string $streetName): Address
    {
        $this->streetName = $streetName;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getPropertyName(): ?string
    {
        return $this->propertyName;
    }

    /**
     * PropertyName.
     *
     * @param string|null $propertyName
     * @return Address
     */
    public function setPropertyName(?string $propertyName): Address
    {
        $this->propertyName = $propertyName;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getPostCode(): ?string
    {
        return $this->postCode;
    }

    /**
     * PostCode.
     *
     * @param string|null $postCode
     * @return Address
     */
    public function setPostCode(?string $postCode): Address
    {
        $this->postCode = $postCode !== null ? strtoupper(str_replace(['(',')',' ','-','.',',','[',']',"\n","\r","\t"], '', $postCode)) : null;
        return $this;
    }

    /**
     * @return Locality|null
     */
    public function getLocality(): ?Locality
    {
        return $this->locality;
    }

    /**
     * Locality.
     *
     * @param Locality|null $locality
     * @return Address
     */
    public function setLocality(?Locality $locality): Address
    {
        $this->locality = $locality;
        return $this;
    }

    /**
     * toArray
     * @param string|null $name
     * @return array
     */
    public function toArray(?string $name = null): array
    {
        if ($name === 'style') {
            return [
                'propertyName' => $this->getPropertyName(),
                'flatUnit' => $this->getFlatUnitDetails(),
                'streetNumber' => $this->getStreetNumber(),
                'streetName' => $this->getStreetName(),
                'territory' => $this->getLocality() ? $this->getLocality()->getTerritory() : '',
                'locality' => $this->getLocality() ? $this->getLocality()->getName() : '',
                'country' => $this->getLocality() ? Countries::getAlpha3Names($this->getLocality()->getCountry()) : '',
                'postCode' => $this->getPostCode() . ($this->getLocality() ? $this->getLocality()->getPostCode() : ''),
            ];
        }
        return [
            'flatUnitDetails' => $this->getFlatUnitDetails(),
            'streetNumber' => $this->getStreetNumber(),
            'streetName' => $this->getStreetName(),
            'id' => $this->getId(),
            'propertyName' => $this->getPropertyName(),
            'locality' => $this->getLocality() ? $this->getLocality()->toString() : null,
            'canDelete' => $this->canDelete(),
        ];
    }

    /**
     * create
     * @return string
     */
    public function create(): string
    {
        return "CREATE TABLE `__prefix__Address` (
                    `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                    `streetName` varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
                    `propertyName` varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
                    `locality` int(6) UNSIGNED DEFAULT NULL,
                    PRIMARY KEY (`id`),
                    KEY `locality` (`locality`)
                ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";
    }

    /**
     * foreignConstraints
     * @return string
     */
    public function foreignConstraints(): string
    {
        return "ALTER TABLE `__prefix__Address`
                    ADD CONSTRAINT `FOREIGN KEY (`locality`) REFERENCES `__prefix__Locality` (`id`);";
    }

    /**
     * coreData
     * @return string
     */
    public function coreData(): string
    {
        return '';
    }

    /**
     * canDelete
     * @return bool
     */
    public function canDelete()
    {
        return true;
    }

    /**
     * toString
     * @param string|null $style
     * @return string
     */
    public function toString(?string $style = null): string
    {
        $result = $this->getFlatUnitDetails() . '/' . $this->getStreetNumber() . ' ' . $this->getStreetName() . ' ' . $this->getPropertyName() . ' ' . ($this->getLocality() ? $this->getLocality()->toString() : null) . ' ' . $this->getPostCode();
        $result = str_replace('  ',' ', trim(trim($result), '/'));
        if ($result === '')
            return '';
        if (!is_null($style)) {
            $result = str_replace(array_keys($this->toArray('style')), array_values($this->toArray('style')), $style);
        }
        return trim($result);
    }

    /**
     * isEqualTo
     * @param Address $address
     * @return bool
     */
    public function isEqualTo(Address $address): bool
    {
        return $this->getId() === $address->getId();
    }
}