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
use App\Modules\People\Entity\Person;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\PersistentCollection;

/**
 * Class INArchive
 * @package App\Modules\IndividualNeed\Entity
 * @ORM\Entity(repositoryClass="App\Modules\IndividualNeed\Repository\INArchiveRepository")
 * @ORM\Table(name="INArchive",
 *     indexes={@ORM\Index(name="person",columns={"person"})})
 */
class INArchive extends AbstractEntity
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
     * @var Person|null
     * @ORM\ManyToOne(targetEntity="App\Modules\People\Entity\Person")
     * @ORM\JoinColumn(name="person", referencedColumnName="id")
     */
    private $person;

    /**
     * @var string|null
     * @ORM\Column(type="text")
     */
    private $strategies;

    /**
     * @var string|null
     * @ORM\Column(type="text")
     */
    private $targets;

    /**
     * @var string|null
     * @ORM\Column(type="text")
     */
    private $notes;

    /**
     * @var INDescriptor[]|Collection
     * @ORM\ManyToMany(targetEntity="INDescriptor")
     * @ORM\JoinTable(name="INArchiveDescriptors",
     *      joinColumns={@ORM\JoinColumn(name="in_archive", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="in_descriptor", referencedColumnName="id")}
     *      )
     */
    private $descriptors;

    /**
     * @var string|null
     * @ORM\Column(length=50)
     */
    private $archiveTitle;

    /**
     * @var \DateTimeImmutable|null
     * @ORM\Column(type="datetime_immutable",nullable=true)
     */
    private $archivedOn;

    /**
     * INArchive constructor.
     */
    public function __construct()
    {
        $this->descriptors = new ArrayCollection();
    }

    /**
     * @return string|null
     */
    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * @param string|null $id
     * @return INArchive
     */
    public function setId(?string $id): INArchive
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return Person|null
     */
    public function getPerson(): ?Person
    {
        return $this->person;
    }

    /**
     * @param Person|null $person
     * @return INArchive
     */
    public function setPerson(?Person $person): INArchive
    {
        $this->person = $person;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getStrategies(): ?string
    {
        return $this->strategies;
    }

    /**
     * @param string|null $strategies
     * @return INArchive
     */
    public function setStrategies(?string $strategies): INArchive
    {
        $this->strategies = $strategies;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getTargets(): ?string
    {
        return $this->targets;
    }

    /**
     * @param string|null $targets
     * @return INArchive
     */
    public function setTargets(?string $targets): INArchive
    {
        $this->targets = $targets;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getNotes(): ?string
    {
        return $this->notes;
    }

    /**
     * @param string|null $notes
     * @return INArchive
     */
    public function setNotes(?string $notes): INArchive
    {
        $this->notes = $notes;
        return $this;
    }

    /**
     * @return Collection|INDescriptor[]
     */
    public function getDescriptors(): Collection
    {
        if (null === $this->descriptors)
            $this->descriptors = new ArrayCollection();
        if ($this->descriptors instanceof PersistentCollection)
            $this->descriptors->initialize();

        return $this->descriptors;
    }

    /**
     * Descriptors.
     *
     * @param Collection|INDescriptor[] $descriptors
     * @return INArchive
     */
    public function setDescriptors($descriptors)
    {
        $this->descriptors = $descriptors;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getArchiveTitle(): ?string
    {
        return $this->archiveTitle;
    }

    /**
     * @param string|null $archiveTitle
     * @return INArchive
     */
    public function setArchiveTitle(?string $archiveTitle): INArchive
    {
        $this->archiveTitle = $archiveTitle;
        return $this;
    }

    /**
     * @return \DateTimeImmutable|null
     */
    public function getArchivedOn(): ?\DateTimeImmutable
    {
        return $this->archivedOn;
    }

    /**
     * @param \DateTimeImmutable|null $archivedOn
     * @return INArchive
     */
    public function setArchivedOn(?\DateTimeImmutable $archivedOn): INArchive
    {
        $this->archivedOn = $archivedOn;
        return $this;
    }

    public function toArray(?string $name = null): array
    {
        return [];
    }

    /**
     * create
     * @return array|string[]
     * 9/06/2020 15:31
     */
    public function create(): array
    {
        return ["CREATE TABLE `__prefix__INArchive` (
                    `id` CHAR(36) NOT NULL COMMENT '(DC2Type:guid)', 
                    `person` CHAR(36) DEFAULT NULL COMMENT '(DC2Type:guid)', 
                    `strategies` LONGTEXT NOT NULL, 
                    `targets` LONGTEXT NOT NULL, 
                    `notes` LONGTEXT NOT NULL, 
                    `archive_title` VARCHAR(50) NOT NULL, 
                    `archived_on` DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)', 
                    INDEX `person` (`person`), 
                    PRIMARY KEY(`id`)
                ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB;",
                "CREATE TABLE `__prefix__INArchiveDescriptors` (
                    `in_archive` CHAR(36) NOT NULL COMMENT '(DC2Type:guid)', 
                    `in_descriptor` CHAR(36) NOT NULL COMMENT '(DC2Type:guid)', 
                    PRIMARY KEY(`in_archive`, `in_descriptor`),
                    KEY `in_archive` (`in_archive`), 
                    KEY `in_descriptor` (`in_descriptor`)
                ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB;"];
    }

    /**
     * foreignConstraints
     * @return string
     * 9/06/2020 15:31
     */
    public function foreignConstraints(): string
    {
        return "ALTER TABLE `__prefix__INArchive` ADD CONSTRAINT FOREIGN KEY (`person`) REFERENCES `__prefix__Person` (`id`);
                 ALTER TABLE `__prefix__INArchiveDescriptors` 
                     ADD CONSTRAINT FOREIGN KEY (`in_archive`) REFERENCES `__prefix__INArchive` (`id`),
                     ADD CONSTRAINT FOREIGN KEY (`in_descriptor`) REFERENCES `__prefix__INDescriptor` (`id`);";
    }

    /**
     * getVersion
     * @return string
     * 9/06/2020 15:31
     */
    public static function getVersion(): string
    {
        return static::VERSION;
    }

}