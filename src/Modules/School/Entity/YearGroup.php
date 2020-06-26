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
namespace App\Modules\School\Entity;

use App\Manager\AbstractEntity;
use App\Modules\People\Entity\Person;
use App\Provider\ProviderFactory;
use Doctrine\DBAL\Driver\PDOException;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Bundle\FrameworkBundle\Command\YamlLintCommand;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Yaml\Yaml;

/**
 * Class YearGroup
 * @package App\Modules\School\Entity
 * @ORM\Entity(repositoryClass="App\Modules\School\Repository\YearGroupRepository")
 * @ORM\Table(name="YearGroup",
 *     uniqueConstraints={@ORM\UniqueConstraint(name="name", columns={"name"}),
 *     @ORM\UniqueConstraint(name="abbreviation", columns={"abbreviation"}),
 *     @ORM\UniqueConstraint(name="sequence_number", columns={"sequence_number"})},
 *     indexes={@ORM\Index(name="head_of_year", columns={"head_of_year"})})
 * @UniqueEntity({"name"})
 * @UniqueEntity({"abbreviation"})
 * @UniqueEntity({"sequenceNumber"})
 */
class YearGroup extends AbstractEntity
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
     * @ORM\Column(length=15)
     * @Assert\NotBlank()
     */
    private $name;

    /**
     * @var string|null
     * @ORM\Column(length=4,name="abbreviation")
     * @Assert\NotBlank()
     */
    private $abbreviation;

    /**
     * @var integer
     * @ORM\Column(type="smallint")
     * @Assert\NotBlank()
     * @Assert\Range(min=1,max=999)
    ")
     */
    private $sequenceNumber;

    /**
     * @var Person|null
     * @ORM\ManyToOne(targetEntity="App\Modules\People\Entity\Person")
     * @ORM\JoinColumn(name="head_of_year",referencedColumnName="id")
     * @Assert\Valid
     */
    private $headOfYear;

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
     * @return YearGroup
     */
    public function setId(?string $id): YearGroup
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
     * @return YearGroup
     */
    public function setName(?string $name): YearGroup
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
     * @return YearGroup
     */
    public function setAbbreviation(?string $abbreviation): YearGroup
    {
        $this->abbreviation = $abbreviation;
        return $this;
    }

    /**
     * @return int
     */
    public function getSequenceNumber(): int
    {
        return intval($this->sequenceNumber);
    }

    /**
     * @param int $sequenceNumber
     * @return YearGroup
     */
    public function setSequenceNumber(int $sequenceNumber): YearGroup
    {
        $this->sequenceNumber = $sequenceNumber;
        return $this;
    }

    /**
     * @return Person|null
     */
    public function getHeadOfYear(): ?Person
    {
        return $this->headOfYear;
    }

    /**
     * @param Person|null $headOfYear
     * @return YearGroup
     */
    public function setHeadOfYear(?Person $headOfYear): YearGroup
    {
        $this->headOfYear = $headOfYear;
        return $this;
    }

    /**
     * __toString
     * @return string
     */
    public function __toString(): string
    {
        return $this->getId();
    }

    /**
     * toArray
     * @param string|null $name
     * @return array
     */
    public function toArray(?string $name = null): array
    {
        if ($name === 'short') {
            return [
                $this->getId(),
                $this->getName(),
                $this->getAbbreviation(),
                $this->getHeadOfYear() ? $this->getHeadOfYear()->fullName() : '',
            ];
        }
        return [
            'sequence' => $this->getSequenceNumber(),
            'name' => $this->getName(),
            'abbr' => $this->getAbbreviation(),
            'canDelete' => $this->canDelete(),
            'head' => $this->getHeadOfYear() ? $this->getHeadOfYear()->fullName() : '',
        ];
    }

    /**
     * canDelete
     * @return bool
     */
    public function canDelete(): bool
    {
        return ProviderFactory::create(YearGroup::class)->canDelete($this);
    }

    public function create(): array
    {
        return ["CREATE TABLE `__prefix__YearGroup` (
                    `id` CHAR(36) NOT NULL COMMENT '(DC2Type:guid)',
                    `name` CHAR(15) NOT NULL,
                    `abbreviation` CHAR(4) NOT NULL,
                    `sequence_number` smallint UNSIGNED NOT NULL,
                    `head_of_year` CHAR(36) DEFAULT NULL,
                    PRIMARY KEY (`id`),
                    UNIQUE KEY `name` (`name`),
                    UNIQUE KEY `abbreviation` (`abbreviation`),
                    UNIQUE KEY `sequence_number` (`sequence_number`),
                    KEY `headOfYear` (`head_of_year`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;"];
    }

    public function foreignConstraints(): string
    {
        return "ALTER TABLE `__prefix__YearGroup`
                    ADD CONSTRAINT FOREIGN KEY (`head_of_year`) REFERENCES `__prefix__person` (`id`);";
    }

    /**
     * coreData
     * @return array|string[]
     */
    public function coreData(): array
    {
        return Yaml::parse("
-
  name: 'Year 7'
  abbreviation: 'Y07'
  sequenceNumber: 1
-
  name: 'Year 8'
  abbreviation: 'Y08'
  sequenceNumber: 2
-
  name: 'Year 9'
  abbreviation: 'Y09'
  sequenceNumber: 3
-
  name: 'Year 10'
  abbreviation: 'Y10'
  sequenceNumber: 4
-
  name: 'Year 11'
  abbreviation: 'Y11'
  sequenceNumber: 5
-
  name: 'Year 12'
  abbreviation: 'Y12'
  sequenceNumber: 6
-
  name: 'Year 13'
  abbreviation: 'Y13'
  sequenceNumber: 7
");
    }

    public static function getVersion(): string
    {
        return self::VERSION;
    }

    /**
     * getNextSequence
     * @return int
     * 2/06/2020 16:53
     */
    public static function getNextSequence(): int
    {
        return ProviderFactory::getRepository(YearGroup::class)->findNextSequence();
    }
}
