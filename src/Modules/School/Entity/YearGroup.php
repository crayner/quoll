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
namespace App\Modules\School\Entity;

use App\Manager\EntityInterface;
use App\Modules\People\Entity\Person;
use App\Provider\ProviderFactory;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class YearGroup
 * @package App\Modules\School\Entity
 * @ORM\Entity(repositoryClass="App\Modules\School\Repository\YearGroupRepository")
 * @ORM\Table(options={"auto_increment": 1}, name="YearGroup", uniqueConstraints={@ORM\UniqueConstraint(name="name", columns={"name"}),
 *     @ORM\UniqueConstraint(name="nameShort", columns={"nameShort"}),
 *     @ORM\UniqueConstraint(name="sequenceNumber", columns={"sequenceNumber"})},
 *     indexes={@ORM\Index(name="headOfYear", columns={"head_of_year"})})
 * @UniqueEntity({"name"})
 * @UniqueEntity({"nameShort"})
 * @UniqueEntity({"sequenceNumber"})
 */
class YearGroup implements EntityInterface
{
    /**
     * @var integer|null
     * @ORM\Id
     * @ORM\Column(type="smallint", columnDefinition="INT(3) UNSIGNED AUTO_INCREMENT")
     * @ORM\GeneratedValue
     */
    private $id;

    /**
     * @var string|null
     * @ORM\Column(length=15,unique=true)
     * @Assert\NotBlank(message="Your request failed because your inputs were invalid.")
     */
    private $name;

    /**
     * @var string|null
     * @ORM\Column(length=4,name="nameShort",unique=true)
     * @Assert\NotBlank(message="Your request failed because your inputs were invalid.")
     */
    private $nameShort;

    /**
     * @var integer
     * @ORM\Column(type="smallint",columnDefinition="INT(3) UNSIGNED",name="sequenceNumber",unique=true)
     * @Assert\NotBlank(message="Your request failed because your inputs were invalid.")
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
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param int|null $id
     * @return YearGroup
     */
    public function setId(?int $id): YearGroup
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
    public function getNameShort(): ?string
    {
        return $this->nameShort;
    }

    /**
     * @param string|null $nameShort
     * @return YearGroup
     */
    public function setNameShort(?string $nameShort): YearGroup
    {
        $this->nameShort = $nameShort;
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
        return [
            'sequence' => $this->getSequenceNumber(),
            'name' => $this->getName(),
            'abbr' => $this->getNameShort(),
            'canDelete' => $this->canDelete(),
            'head' => $this->getHeadOfYear() ? $this->getHeadOfYear()->formatName(['style' => 'long', 'reverse' => false]) : '',
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

    public function create(): string
    {
        return 'CREATE TABLE `__prefix__YearGroup` (
                    `id` int(3) UNSIGNED NOT NULL AUTO_INCREMENT,
                    `name` varchar(15) NOT NULL,
                    `nameShort` varchar(4) NOT NULL,
                    `sequenceNumber` int(3) UNSIGNED NOT NULL,
                    `head_of_year` int(10) UNSIGNED DEFAULT NULL,
                    PRIMARY KEY (`id`),
                    UNIQUE KEY `name` (`name`),
                    UNIQUE KEY `nameShort` (`nameShort`),
                    UNIQUE KEY `sequenceNumber` (`sequenceNumber`),
                    KEY `headOfYear` (`head_of_year`) USING BTREE
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=ut8mb4_unicode_ci;';
    }

    public function foreignConstraints(): string
    {
        return 'ALTER TABLE `__prefix__YearGroup`
                    ADD CONSTRAINT FOREIGN KEY (`head_of_year`) REFERENCES `__prefix__person` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT;';
    }

    public function coreData(): string
    {
        return 'INSERT INTO `__prefix__YearGroup` (`name`, `nameShort`, `sequenceNumber`, `head_of_year`) VALUES
                    (\'Year 7\', \'Y07\', 1, NULL),
                    (\'Year 8\', \'Y08\', 2, NULL),
                    (\'Year 9\', \'Y09\', 3, NULL),
                    (\'Year 10\', \'Y10\', 4, NULL),
                    (\'Year 11\', \'Y11\', 5, NULL),
                    (\'Year 12\', \'Y12\', 6, NULL),
                    (\'Year 13\', \'Y13\', 7, NULL);';
    }
}