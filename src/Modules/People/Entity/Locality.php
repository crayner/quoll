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
use App\Modules\People\Validator\Country;
use App\Provider\ProviderFactory;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Intl\Countries;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class Locality
 * @package App\Modules\People\Entity
 * @ORM\Entity(repositoryClass="App\Modules\People\Repository\LocalityRepository")
 * @ORM\Table(options={"auto_increment": 1}, name="Locality",
 *     indexes={@ORM\Index(name="country",columns={"country"})},
 *     uniqueConstraints={@ORM\UniqueConstraint(name="locality",columns={"name","territory","post_code","country"})}
 * )
 * @UniqueEntity({"name","territory","postCode","country"})
 */
class Locality implements EntityInterface
{
    /**
     * @var integer|null
     * @ORM\Id()
     * @ORM\Column(type="integer", columnDefinition="INT(6) UNSIGNED")
     * @ORM\GeneratedValue
     */
    private $id;

    /**
     * @var string|null
     * @ORM\Column(length=30)
     * @Assert\NotBlank()
     * @Assert\Length(max=30)
     */
    private $name;

    /**
     * @var string|null
     * @ORM\Column(length=30,nullable=true)
     * @Assert\Length(max=30)
     */
    private $territory;

    /**
     * @var string|null
     * @ORM\Column(length=10,nullable=true,name="post_code")
     * @Assert\Length(max=10)
     */
    private $postCode;

    /**
     * @var string|null
     * @ORM\Column(length=3,nullable=true)
     * @Assert\Length(max=3)
     * @Country(alpha3=true)
     */
    private $country;

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param int|null $id
     * @return District
     */
    public function setId(?int $id): Locality
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
     * @return District
     */
    public function setName(?string $name): Locality
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getTerritory(): ?string
    {
        return $this->territory;
    }

    /**
     * Territory.
     *
     * @param string|null $territory
     * @return District
     */
    public function setTerritory(?string $territory): Locality
    {
        $this->territory = $territory;
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
     * PostalCode.
     *
     * @param string|null $postalCode
     * @return District
     */
    public function setPostCode(?string $postCode): Locality
    {
        $this->postCode = $postCode;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getCountry(): ?string
    {
        return $this->country;
    }

    /**
     * Country.
     *
     * @param string|null $country
     * @return Locality
     */
    public function setCountry(?string $country): Locality
    {
        $this->country = $country;
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
                'name' => $this->getName(),
                'territory' => $this->getTerritory(),
                'country' => Countries::getAlpha3Name($this->getCountry()),
                'postCode' => $this->getPostCode(),
            ];
        }
        if ($name === 'full') {
            return [
                'id' => $this->getId(),
                'name' => $this->getName(),
                'territory' => $this->getTerritory(),
                'country' => $this->getCountry(),
                'postCode' => $this->getPostCode(),
            ];
        }
        return [
            'name' => $this->getName(),
            'id' => $this->getId(),
            'territory' => $this->getTerritory(),
            'postCode' => $this->getPostCode(),
            'canDelete' => ProviderFactory::create(District::class)->canDelete($this) === 0,
        ];
    }

    /**
     * getFullName
     * @return string
     */
    public function getFullName(): string
    {
        return trim($this->getName().' '.$this->getTerritory().' '.$this->getPostCode());
    }

    public function create(): string
    {
        return "CREATE TABLE `__prefix__Locality` (
                    `id` int(6) UNSIGNED NOT NULL AUTO_INCREMENT,
                    `name` varchar(30) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
                    `territory` varchar(30) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
                    `post_code` varchar(10) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
                    `country` varchar(3) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
                    PRIMARY KEY (`id`),
                    UNIQUE KEY `locality` (`name`,`territory`,`post_code`,`country`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
    }

    public function foreignConstraints(): string
    {
        return '';
    }

    public function coreData(): string
    {
        return '';
    }

    /**
     * toString
     * @param string|null $style
     * @return string
     */
    public function toString(?string $style = null): string
    {
        $result = $this->getName() . ' ' . $this->getTerritory() . ' ' . $this->getPostCode();
        $result .= ' ' . Countries::getAlpha3Name($this->getCountry());
        if (trim($result) === '')
            return '';
        if (!is_null($style)) {
            $result = str_replace(array_keys($this->toArray('style')), array_values($this->toArray('style')), $style);
        }
        return trim($result);
    }
}