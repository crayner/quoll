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
use App\Modules\People\Manager\PhoneCodes;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class Phone
 * @package App\Modules\People\Entity
 * @ORM\Entity(repositoryClass="App\Modules\People\Repository\PhoneRepository")
 * @ORM\Table(name="Phone",
 *     uniqueConstraints={@ORM\UniqueConstraint("number_country",columns={"phone_number","country"})})
 * @UniqueEntity(fields={"phoneNumber","country"})
 * @ORM\HasLifecycleCallbacks()
 * @\App\Modules\People\Validator\Phone()
 */
class Phone implements EntityInterface
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
     */
    private $country;

    /**
     * @return string|null
     */
    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * Id.
     *
     * @param string|null $id
     * @return Phone
     */
    public function setId(?string $id): Phone
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
        $this->phoneNumber = $this->trimPhoneNumber($phoneNumber);
        return $this;
    }

    /**
     * trimPhoneNumber
     * @param string|null $phoneNumber
     * @return string|null
     */
    public function trimPhoneNumber(?string $phoneNumber): ?string
    {
        return preg_replace('/[^0-9.]/', '', $phoneNumber);
    }

    /**
     * trimPhone
     * @return Phone
     * @ORM\PrePersist()
     * @ORM\PreUpdate()
     */
    public function trimPhone(): Phone
    {
        $this->setPhoneNumber($this->trimPhoneNumber($this->getPhoneNumber()));

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
                    `id` CHAR(36) NOT NULL COMMENT '(DC2Type:guid)',
                    `type` CHAR(16) DEFAULT NULL,
                    `phone_number` CHAR(20) DEFAULT NULL,
                    `country` CHAR(3) DEFAULT NULL,
                    PRIMARY KEY (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=ut8mb4_unicode_ci;";
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

    /**
     * __toString
     * @return string
     */
    public function __toString(): string
    {
        return PhoneCodes::formatPhoneNumber($this);
    }

    public static function getVersion(): string
    {
        return self::VERSION;
    }
}
