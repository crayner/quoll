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
 * Date: 9/05/2020
 * Time: 09:20
 */

namespace App\Modules\People\Entity;


use App\Manager\EntityInterface;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class Phone
 * @package App\Modules\People\Entity
 * @ORM\Entity(repositoryClass="App\Modules\People\Repository\PhoneRepository")
 * @ORM\Table(options={"auto_increment": 1}, name="Phone")
 */
class Phone implements EntityInterface
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
     * @ORM\Column(length=16)
     * @Assert\Choice(callback="getPhoneTypeList")
     */
    private $type;

    /**
     * @var string|null
     * @ORM\Column(length=20)
     * @Assert\NotBlank()
     */
    private $phoneNumber;

    /**
     * @var string|null
     * @ORM\Column(length=3)
     * @Assert\NotBlank()
     * @Assert\Country(alpha3=true)
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
     * Id.
     *
     * @param int|null $id
     * @return Phone
     */
    public function setId(?int $id): Phone
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getType(): ?string
    {
        return $this->type;
    }

    /**
     * Type.
     *
     * @param string|null $type
     * @return Phone
     */
    public function setType(?string $type): Phone
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @return array
     */
    public static function getPhoneTypeList(): array
    {
        return self::$phoneTypeList;
    }

    /**
     * @var array
     */
    private static $phoneTypeList = ['','Mobile','Home','Work','Fax','Pager','Other'];

    /**
     * @return string|null
     */
    public function getPhoneNumber(): ?string
    {
        return $this->phoneNumber;
    }

    /**
     * PhoneNumber.
     *
     * @param string|null $phoneNumber
     * @return Phone
     */
    public function setPhoneNumber(?string $phoneNumber): Phone
    {
        $this->phoneNumber = $phoneNumber;
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
     * @return Phone
     */
    public function setCountry(?string $country): Phone
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
        return [];
    }

    /**
     * create
     * @return string
     */
    public function create(): string
    {
        return "CREATE TABLE `__prefix__Phone` (
                    `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                    `type` varchar(16) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
                    `phone_number` varchar(20) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
                    `country` varchar(3) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
                    PRIMARY KEY (`id`)
                ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
    }

    /**
     * foreignConstraints
     * @return string
     */
    public function foreignConstraints(): string
    {
        return '';
    }

    /**
     * coreData
     * @return string
     */
    public function coreData(): string
    {
        return '';
    }

}