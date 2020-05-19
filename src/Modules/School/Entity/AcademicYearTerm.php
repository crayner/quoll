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
use Doctrine\ORM\Mapping as ORM;
use App\Modules\School\Validator as Check;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class AcademicYearTerm
 * @package App\Modules\School\Entity
 * @ORM\Entity(repositoryClass="App\Modules\School\Repository\AcademicYearTermRepository")
 * @ORM\Table(options={"auto_increment": 1}, name="AcademicYearTerm",
 *     uniqueConstraints={@ORM\UniqueConstraint(name="sequence_number", columns={"academic_year","sequenceNumber"}),
 *     @ORM\UniqueConstraint(name="abbr", columns={"academic_year","nameShort"}),
 *     @ORM\UniqueConstraint(name="name", columns={"academic_year","name"})})
 * @UniqueEntity({"academicYear","sequenceNumber"},errorPath="sequenceNumber")
 * @UniqueEntity({"academicYear","name"},errorPath="name")
 * @UniqueEntity({"academicYear","nameShort"},errorPath="nameShort")
 * @Check\Term()
 */
class AcademicYearTerm implements EntityInterface
{
    /**
     * @var integer|null
     * @ORM\Id
     * @ORM\Column(type="integer", name="id", columnDefinition="INT(5) UNSIGNED AUTO_INCREMENT")
     * @ORM\GeneratedValue
     */
    private $id;

    /**
     * @var AcademicYear|null
     * @ORM\ManyToOne(targetEntity="App\Modules\School\Entity\AcademicYear", inversedBy="terms")
     * @ORM\JoinColumn(name="academic_year",referencedColumnName="id",nullable=false)
     * @Assert\NotBlank()
     */
    private $academicYear;

    /**
     * @var integer
     * @ORM\Column(type="smallint",columnDefinition="INT(5)",name="sequenceNumber",unique=true)
     */
    private $sequenceNumber;

    /**
     * @var string|null
     * @ORM\Column(length=20)
     * @Assert\Length(max=20)
     * @Assert\NotBlank()
     */
    private $name;

    /**
     * @var string|null
     * @ORM\Column(length=4, name="nameShort")
     * @Assert\Length(max=4)
     * @Assert\NotBlank()
     */
    private $nameShort;

    /**
     * @var \DateTimeImmutable|null
     * @ORM\Column(type="date_immutable",name="firstDay",nullable=true)
     * @Assert\NotBlank()
     */
    private $firstDay;

    /**
     * @var \DateTimeImmutable|null
     * @ORM\Column(type="date_immutable",name="lastDay",nullable=true)
     * @Assert\NotBlank()
     */
    private $lastDay;

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param int|null $id
     * @return AcademicYearTerm
     */
    public function setId(?int $id): AcademicYearTerm
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return AcademicYear|null
     */
    public function getAcademicYear(): ?AcademicYear
    {
        return $this->academicYear;
    }

    /**
     * @param AcademicYear|null $academicYear
     * @return AcademicYearTerm
     */
    public function setAcademicYear(?AcademicYear $academicYear): AcademicYearTerm
    {
        $this->academicYear = $academicYear;
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
     * @return AcademicYearTerm
     */
    public function setSequenceNumber(int $sequenceNumber): AcademicYearTerm
    {
        $this->sequenceNumber = $sequenceNumber;
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
     * @return AcademicYearTerm
     */
    public function setName(?string $name): AcademicYearTerm
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
     * @return AcademicYearTerm
     */
    public function setNameShort(?string $nameShort): AcademicYearTerm
    {
        $this->nameShort = $nameShort;
        return $this;
    }

    /**
     * @return \DateTimeImmutable|null
     */
    public function getFirstDay(): ?\DateTimeImmutable
    {
        return $this->firstDay;
    }

    /**
     * FirstDay.
     *
     * @param \DateTimeImmutable|null $firstDay
     * @return AcademicYearTerm
     */
    public function setFirstDay(?\DateTimeImmutable $firstDay): AcademicYearTerm
    {
        $this->firstDay = $firstDay;
        return $this;
    }

    /**
     * @return \DateTimeImmutable|null
     */
    public function getLastDay(): ?\DateTimeImmutable
    {
        return $this->lastDay;
    }

    /**
     * LastDay.
     *
     * @param \DateTimeImmutable|null $lastDay
     * @return AcademicYearTerm
     */
    public function setLastDay(?\DateTimeImmutable $lastDay): AcademicYearTerm
    {
        $this->lastDay = $lastDay;
        return $this;
    }

    /**
     * toArray
     * @param string|null $name
     * @return array
     */
    public function toArray(?string $name = null): array
    {
        $dates = $this->getFirstDay()->format('d M Y') . ' - ' . $this->getLastDay()->format('d M Y');
        return [
            'name' => $this->getName(),
            'abbr' => $this->getNameShort(),
            'year' => $this->getAcademicYear()->getName(),
            'dates' => $dates,
            'canDelete' => true,
            'sequence' => $this->getSequenceNumber(),
        ];
    }

    /**
     * create
     * @return string
     */
    public function create(): string
    {
        return 'CREATE TABLE `__prefix__AcademicYearTerm` (
                    `id` int(5) UNSIGNED NOT NULL AUTO_INCREMENT,
                    `sequenceNumber` int(5) DEFAULT NULL,
                    `name` varchar(20) NOT NULL,
                    `nameShort` varchar(4) NOT NULL,
                    `firstDay` date DEFAULT NULL COMMENT \'(DC2Type:date_immutable)\',
                    `lastDay` date DEFAULT NULL COMMENT \'(DC2Type:date_immutable)\',
                    `academic_year` int(3) UNSIGNED DEFAULT NULL,
                    PRIMARY KEY (`id`),
                    UNIQUE KEY `name` (`academic_year`,`name`) USING BTREE,
                    UNIQUE KEY `abbr` (`academic_year`,`nameShort`),
                    UNIQUE KEY `sequence_nnumber` (`academic_year`,`sequenceNumber`) USING BTREE,
                    KEY `academic_year` (`academic_year`) USING BTREE
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=ut8mb4_unicode_ci;';
    }

    /**
     * foreignConstraints
     * @return string
     */
    public function foreignConstraints(): string
    {
        return 'ALTER TABLE `__prefix__AcademicYearTerm`
                    ADD CONSTRAINT FOREIGN KEY (`academic_year`) REFERENCES `__prefix__AcademicYear` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT;';
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