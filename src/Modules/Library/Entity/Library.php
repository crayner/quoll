<?php
/**
 * Created by PhpStorm.
 *
 * kookaburra
 * (c) 2019 Craig Rayner <craig@craigrayner.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * User: craig
 * Date: 8/10/2019
 * Time: 13:14
 */
namespace App\Modules\Library\Entity;

use App\Manager\AbstractEntity;
use App\Modules\Department\Entity\Department;
use App\Modules\School\Entity\Facility;
use App\Util\ImageHelper;
use App\Validator as Validator;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class Library
 * @package Kookaburra\Library\Entity
 * @ORM\Entity(repositoryClass="App\Modules\Library\Repository\LibraryRepository")
 * @ORM\Table(name="Library",
 *     uniqueConstraints={@ORM\UniqueConstraint(name="name", columns={"name"}),
 *     @ORM\UniqueConstraint(name="abbr", columns={"abbr"})},
 *     indexes={@ORM\Index(name="facility", columns={"facility"}),
 *     @ORM\Index(name="department", columns="department") })
 */
class Library extends AbstractEntity
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
     * @ORM\Column(length=50, options={"comment": "The library name should be unique."},unique=true)
     * @Assert\NotBlank()
     * @Assert\Length(max=50)
     */
    private $name;

    /**
     * @var string|null
     * @ORM\Column(length=6, options={"comment": "The library Abbreviation should be unique."},unique=true)
     * @Assert\NotBlank()
     * @Assert\Length(max=6)
     */
    private $abbr;

    /**
     * @var Facility|null
     * @ORM\ManyToOne(targetEntity="App\Modules\School\Entity\Facility")
     * @ORM\JoinColumn(name="facility",referencedColumnName="id",nullable=true)
     */
    private $facility;

    /**
     * @var Department|null
     * @ORM\ManyToOne(targetEntity="App\Modules\Department\Entity\Department")
     * @ORM\JoinColumn(name="department",referencedColumnName="id",nullable=true)
     */
    private $department;

    /**
     * @var bool
     * @ORM\Column(type="boolean")
     */
    private $active = true;

    /**
     * @var bool
     * @ORM\Column(type="boolean")
     */
    private $main = false;

    /**
     * @var integer
     * @ORM\Column(type="smallint",options={"comment": "Lending period default for this library in days."})
     * @Assert\Range(min=1,max=365)
     */
    private $lendingPeriod = 14;

    /**
     * @var string
     * @ORM\Column(length=32, options={"default": "white"})
     * @Validator\Colour()
     */
    private $bgColour = 'white';

    /**
     * @var File|null
     * @ORM\Column(length=191,nullable=true)
     * @Validator\ReactImage(
     *     maxSize = "1025k",
     *     mimeTypes = {"image/jpg","image/gif","image/png","image/jpeg"},
     *     maxRatio = 1.78,
     *     minRatio = 1,
     *     minWidth = 1600,
     *     allowPortrait=false
     * )
     */
    private $bgImage;

    /**
     * @var integer|null
     * @ORM\Column(type="smallint", nullable=true)
     * @Assert\Range(max = 99)
     */
    private $borrowLimit;

    /**
     * @var array
     */
    private static $borrowerTypes = [
        'Student',
        'Staff',
        'Parent',
        'Other',
    ];

    /**
     * @return string|null
     */
    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * @param string|null $id
     * @return Library
     */
    public function setId(?string $id): Library
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
     * Name.
     *
     * @param string|null $name
     * @return Library
     */
    public function setName(?string $name): Library
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getAbbr(): ?string
    {
        return $this->abbr;
    }

    /**
     * Abbr.
     *
     * @param string|null $abbr
     * @return Library
     */
    public function setAbbr(?string $abbr): Library
    {
        $this->abbr = $abbr;
        return $this;
    }

    /**
     * @return Facility|null
     */
    public function getFacility(): ?Facility
    {
        return $this->facility;
    }

    /**
     * Facility.
     *
     * @param Facility|null $facility
     * @return Library
     */
    public function setFacility(?Facility $facility): Library
    {
        $this->facility = $facility;
        return $this;
    }

    /**
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->active = $this->active ? true : false;
    }

    /**
     * Active.
     *
     * @param bool $active
     * @return Library
     */
    public function setActive(bool $active): Library
    {
        $this->active = $active ? true : false;
        return $this;
    }

    /**
     * @return bool
     */
    public function isMain(): bool
    {
        return $this->main;
    }

    /**
     * @param bool $main
     * @return Library
     */
    public function setMain(bool $main): Library
    {
        $this->main = $main;
        return $this;
    }

    /**
     * @return null|int
     */
    public function getLendingPeriod(?int $default = 14): ?int
    {
        return $this->lendingPeriod ?: $default;
    }

    /**
     * LendingPeriod.
     *
     * @param int $lendingPeriod
     * @return Library
     */
    public function setLendingPeriod(int $lendingPeriod): Library
    {
        $this->lendingPeriod = $lendingPeriod;
        return $this;
    }

    /**
     * @return Department|null
     */
    public function getDepartment(): ?Department
    {
        return $this->department;
    }

    /**
     * Department.
     *
     * @param Department|null $department
     * @return Library
     */
    public function setDepartment(?Department $department): Library
    {
        $this->department = $department;
        return $this;
    }

    /**
     * @return string
     */
    public function getBgColour(): string
    {
        return $this->bgColour;
    }

    /**
     * BgColour.
     *
     * @param string $bgColour
     * @return Library
     */
    public function setBgColour(string $bgColour): Library
    {
        $this->bgColour = $bgColour;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getBgImage(): ?string
    {
        return $this->bgImage;
    }

    /**
     * BgImage.
     *
     * @param null|string $bgImage
     * @return Library
     */
    public function setBgImage(?string $bgImage): Library
    {
        $this->bgImage = ImageHelper::getRelativePath($bgImage);;
        return $this;
    }

    /**
     * @return int
     */
    public function getBorrowLimit(): int
    {
        return intval($this->borrowLimit);
    }

    /**
     * BorrowLimit.
     *
     * @param int|null $borrowLimit
     * @return Library
     */
    public function setBorrowLimit(?int $borrowLimit): Library
    {
        $this->borrowLimit = $borrowLimit;
        return $this;
    }

    /**
     * @return array
     */
    public static function getBorrowerTypes(): array
    {
        return self::$borrowerTypes;
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
     * @return array|string[]
     * 8/06/2020 09:11
     */
    public function create(): array
    {
        return ["CREATE TABLE `__prefix__Library` (
                    `id` CHAR(36) NOT NULL COMMENT '(DC2Type:guid)', 
                    `facility` CHAR(36) DEFAULT NULL COMMENT '(DC2Type:guid)', 
                    `department` CHAR(36) DEFAULT NULL COMMENT '(DC2Type:guid)', 
                    `name` VARCHAR(50) NOT NULL COMMENT 'The library name should be unique.', 
                    `abbr` VARCHAR(6) NOT NULL COMMENT 'The library Abbreviation should be unique.', 
                    `active` TINYINT(1) NOT NULL, 
                    `main` TINYINT(1) NOT NULL, 
                    `lending_period` SMALLINT NOT NULL COMMENT 'Lending period default for this library in days.', 
                    `bg_colour` VARCHAR(32) DEFAULT 'white' NOT NULL, 
                    `bg_image` VARCHAR(191) DEFAULT NULL, 
                    `borrow_limit` SMALLINT UNSIGNED, 
                    INDEX `facility` (`facility`), 
                    INDEX `department` (`department`), 
                    UNIQUE INDEX `name` (`name`), 
                    UNIQUE INDEX `abbr` (`abbr`), 
                    PRIMARY KEY(id)
                ) DEFAULT CHARACTER SET `utf8mb4` COLLATE `utf8mb4_general_ci` ENGINE = InnoDB;"];
    }

    /**
     * foreignConstraints
     * @return string
     * 8/06/2020 09:12
     */
    public function foreignConstraints(): string
    {
        return "ALTER TABLE `__prefix__Library` ADD CONSTRAINT FOREIGN KEY (`facility`) REFERENCES `__prefix__Facility` (`id`)
                    ADD CONSTRAINT FOREIGN KEY (`department`) REFERENCES `__prefix__Department` (`id`);";
    }

    /**
     * getVersion
     * @return string
     * 8/06/2020 09:12
     */
    public static function getVersion(): string
    {
        return static::VERSION;
    }
}