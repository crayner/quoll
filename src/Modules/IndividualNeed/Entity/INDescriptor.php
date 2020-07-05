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
namespace App\Modules\IndividualNeed\Entity;

use App\Manager\AbstractEntity;
use App\Provider\ProviderFactory;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class INDescriptor
 * @package App\Modules\IndividualNeed\Entity
 * @ORM\Entity(repositoryClass="App\Modules\IndividualNeed\Repository\INDescriptorRepository")
 * @ORM\Table(name="INDescriptor",
 *     uniqueConstraints={@ORM\UniqueConstraint(name="name",columns={"name"}),
 *      @ORM\UniqueConstraint(name="abbr",columns={"abbreviation"}),
 *     @ORM\UniqueConstraint(name="sort_order",columns={"sort_order"})
 * })
 * @UniqueEntity({"name"})
 * @UniqueEntity({"abbreviation"})
 * @UniqueEntity({"sortOrder"})
 * @ORM\HasLifecycleCallbacks()
 */
class INDescriptor extends AbstractEntity
{
    CONST VERSION = '1.0.00';

    /**
     * @var string|null
     * @ORM\Id()
     * @ORM\Column(type="guid")
     * @ORM\GeneratedValue(strategy="UUID")
     */
    private $id;

    /**
     * @var string|null
     * @ORM\Column(length=50)
     * @Assert\Length(max=50)
     * @Assert\NotBlank()
     */
    private $name;

    /**
     * @var string|null
     * @ORM\Column(length=5)
     * @Assert\Length(max=5)
     * @Assert\NotBlank()
     */
    private $abbreviation;

    /**
     * @var string|null
     * @ORM\Column(type="text",nullable=true)
     */
    private $description;

    /**
     * @var integer|null
     * @ORM\Column(type="smallint")
     * @Assert\Range(min=1,max=99)
     */
    private $sortOrder;

    /**
     * @return string|null
     */
    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * @param string|null $id
     * @return INDescriptor
     */
    public function setId(?string $id): INDescriptor
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
     * @return INDescriptor
     */
    public function setName(?string $name): INDescriptor
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
     * @return INDescriptor
     */
    public function setAbbreviation(?string $abbreviation): INDescriptor
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
     * @return INDescriptor
     */
    public function setDescription(?string $description): INDescriptor
    {
        $this->description = $description;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getSortOrder(): ?int
    {
        return $this->sortOrder;
    }

    /**
     * @param int|null $sortOrder
     * @return INDescriptor
     */
    public function setSortOrder(?int $sortOrder): INDescriptor
    {
        $this->sortOrder = $sortOrder;
        return $this;
    }

    /**
     * getNextSortOrder
     * @return INDescriptor
     * @ORM\PrePersist()
     * 10/06/2020 10:08
     */
    public function getNextSortOrder(): INDescriptor
    {
        if (empty($this->getSortOrder())) {
            $this->setSortOrder(ProviderFactory::getRepository(INDescriptor::class)->nextSortOrder());
        }
        return $this;
    }

    /**
     * toArray
     * @param string|null $name
     * @return array
     */
    public function toArray(?string $name = null): array
    {
        return [
            'name' => $this->getName(),
            'sortOrder' => $this->getSortOrder(),
            'abbr' => $this->getAbbreviation(),
            'description' => $this->getDescription(),
            'canDelete' => ProviderFactory::create(INDescriptor::class)->canDelete($this),
            'id' => $this->getId(),
        ];
    }

    /**
     * create
     * @return array|string[]
     * 9/06/2020 12:46
     */
    public function create(): array
    {
        return ["CREATE TABLE `__prefix__INDescriptor` (
                    `id` CHAR(36) NOT NULL COMMENT '(DC2Type:guid)', 
                    `name` VARCHAR(50) NOT NULL, 
                    `abbreviation` VARCHAR(5) NOT NULL, 
                    `description` LONGTEXT DEFAULT NULL, 
                    `sort_order` SMALLINT NOT NULL, 
                    UNIQUE INDEX `name` (`name`), 
                    UNIQUE INDEX `abbr` (`abbreviation`), 
                    UNIQUE INDEX `sort_order` (`sort_order`), 
                    PRIMARY KEY(id)
                ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB;"];
    }

    /**
     * foreignConstraints
     * @return string
     * 9/06/2020 12:46
     */
    public function foreignConstraints(): string
    {
        return '';
    }

    /**
     * getVersion
     * @return string
     * 9/06/2020 12:46
     */
    public static function getVersion(): string
    {
        return static::VERSION;
    }
}
