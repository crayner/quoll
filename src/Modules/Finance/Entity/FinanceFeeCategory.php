<?php
/**
 * Created by PhpStorm.
 *
 * Kookaburra
 *
 * (c) 2018 Craig Rayner <craig@craigrayner.com>
 *
 * User: craig
 * Date: 23/11/2018
 * Time: 15:27
 */
namespace App\Modules\Finance\Entity;

use App\Manager\AbstractEntity;
use App\Manager\Traits\BooleanList;
use App\Modules\People\Entity\Person;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class FinanceFeeCategory
 * @package App\Modules\Finance\Entity
 * @ORM\Entity(repositoryClass="App\Modules\Finance\Repository\FinanceFeeCategoryRepository")
 * @ORM\Table(name="FinanceFeeCategory",
 *     indexes={@ORM\Index(name="creator",columns={"creator"}),
 *     @ORM\Index(name="updater",columns={"updater"})})
 * @ORM\HasLifecycleCallbacks
 */
class FinanceFeeCategory extends AbstractEntity
{
    CONST VERSION = '1.0.00';

    use BooleanList;

    /**
     * @var string|null
     * @ORM\Id()
     * @ORM\Column(type="guid")
     * @ORM\GeneratedValue(strategy="UUID")
     */
    private $id;

    /**
     * @var string|null
     * @ORM\Column(length=100)
     */
    private $name;

    /**
     * @var string|null
     * @ORM\Column(length=6, name="abbreviation")
     */
    private $abbreviation;

    /**
     * @var string|null
     * @ORM\Column(type="text")
     */
    private $description;

    /**
     * @var string|null
     * @ORM\Column(length=1)
     */
    private $active = 'Y';

    /**
     * @var Person|null
     * @ORM\ManyToOne(targetEntity="App\Modules\People\Entity\Person")
     * @ORM\JoinColumn(name="creator", referencedColumnName="id")
     */
    private $creator;

    /**
     * @var \DateTimeImmutable|null
     * @ORM\Column(type="datetime_immutable", name="created_on", nullable=true)
     */
    private $createdOn;

    /**
     * @var Person|null
     * @ORM\ManyToOne(targetEntity="App\Modules\People\Entity\Person")
     * @ORM\JoinColumn(name="updater",referencedColumnName="id",nullable=true)
     */
    private $updater;

    /**
     * @var \DateTimeImmutable|null
     * @ORM\Column(type="datetime_immutable",name="updated_on",nullable=true)
     */
    private $updatedOn;

    /**
     * @return string|null
     */
    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * @param string|null $id
     * @return FinanceFeeCategory
     */
    public function setId(?string $id): FinanceFeeCategory
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
     * @return FinanceFeeCategory
     */
    public function setName(?string $name): FinanceFeeCategory
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getAbbreviation(): ?string
    {
        return $this->abbreviation;
    }

    /**
     * @param string|null $abbreviation
     * @return FinanceFeeCategory
     */
    public function setAbbreviation(?string $abbreviation): FinanceFeeCategory
    {
        $this->abbreviation = $abbreviation;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @param string|null $description
     * @return FinanceFeeCategory
     */
    public function setDescription(?string $description): FinanceFeeCategory
    {
        $this->description = $description;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getActive(): ?string
    {
        return $this->active;
    }

    /**
     * setActive
     * @param string|null $active
     * @return FinanceFeeCategory
     */
    public function setActive(?string $active): FinanceFeeCategory
    {
        $this->active = self::checkBoolean($active, 'Y');
        return $this;
    }

    /**
     * @return Person|null
     */
    public function getCreator(): ?Person
    {
        return $this->creator;
    }

    /**
     * @param Person|null $creator
     * @return FinanceFeeCategory
     */
    public function setCreator(?Person $creator): FinanceFeeCategory
    {
        $this->creator = $creator;
        return $this;
    }

    /**
     * @return \DateTimeImmutable|null
     */
    public function getCreatedOn(): ?\DateTimeImmutable
    {
        return $this->createdOn;
    }

    /**
     * @param \DateTimeImmutable|null $createdOn
     * @return FinanceFeeCategory
     */
    public function setCreatedOn(?\DateTimeImmutable $createdOn): FinanceFeeCategory
    {
        $this->createdOn = $createdOn;
        return $this;
    }

    /**
     * @return Person|null
     */
    public function getUpdater(): ?Person
    {
        return $this->updater;
    }

    /**
     * @param Person|null $updater
     * @return FinanceFeeCategory
     */
    public function setUpdater(?Person $updater): FinanceFeeCategory
    {
        $this->updater = $updater;
        return $this;
    }

    /**
     * @return \DateTimeImmutable|null
     */
    public function getUpdatedOn(): ?\DateTimeImmutable
    {
        return $this->updatedOn;
    }

    /**
     * @param \DateTimeImmutable|null $updatedOn
     * @return FinanceFeeCategory
     */
    public function setUpdatedOn(?\DateTimeImmutable $updatedOn): FinanceFeeCategory
    {
        $this->updatedOn = $updatedOn;
        return $this;
    }

    /**
     * toArray
     * @param string|null $name
     * @return array
     * 4/06/2020 09:51
     */
    public function toArray(?string $name = null): array
    {
        return [];
    }

    /**
     * create
     * @return array
     * 4/06/2020 09:51
     */
    public function create(): array
    {
        return ["CREATE TABLE `__prefix__FinanceFeeCategory` (
                    `id` char(36) NOT NULL COMMENT '(DC2Type:guid)',
                    `creator` char(36) DEFAULT NULL COMMENT '(DC2Type:guid)',
                    `updater` char(36) DEFAULT NULL COMMENT '(DC2Type:guid)',
                    `name` varchar(100) NOT NULL,
                    `abbreviation` varchar(6) NOT NULL,
                    `description` longtext NOT NULL,
                    `active` varchar(1) NOT NULL,
                    `created_on` datetime DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)',
                    `updated_on` datetime DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)',
                    PRIMARY KEY (`id`),
                    KEY `creator` (`creator`),
                    KEY `updater` (`updater`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;"];
    }

    /**
     * foreignConstraints
     * @return string
     * 4/06/2020 09:52
     */
    public function foreignConstraints(): string
    {
        return "ALTER TABLE `__prefix__FinanceFeeCategory`
                    ADD CONSTRAINT FOREIGN KEY (`updater`) REFERENCES `__prefix__Person` (`id`),
                    ADD CONSTRAINT FOREIGN KEY (`creator`) REFERENCES `__prefix__Person` (`id`);";
    }

    /**
     * getVersion
     * @return string
     * 4/06/2020 09:53
     */
    public static function getVersion(): string
    {
        return static::VERSION;
    }
}