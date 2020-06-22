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
use App\Provider\ProviderFactory;
use Doctrine\ORM\Mapping as ORM;
use App\Modules\School\Validator as Check;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class AcademicYearTerm
 * @package App\Modules\School\Entity
 * @ORM\Entity(repositoryClass="App\Modules\School\Repository\AcademicYearTermRepository")
 * @ORM\Table(name="AcademicYearTerm",
 *     indexes={@ORM\Index(name="academic_year",columns={"academic_year"})},
 *     uniqueConstraints={@ORM\UniqueConstraint(name="abbr", columns={"academic_year","abbreviation"}),
 *     @ORM\UniqueConstraint(name="name", columns={"academic_year","name"}),
 *     @ORM\UniqueConstraint(name="academic_year_first_day", columns={"academic_year","first_day"}),
 *     @ORM\UniqueConstraint(name="academic_year_last_day", columns={"academic_year","last_day"})})
 * @UniqueEntity(fields={"academicYear","name"},errorPath="name")
 * @UniqueEntity(fields={"academicYear","abbreviation"},errorPath="abbreviation")
 * @UniqueEntity(fields={"firstDay","academicYear"},errorPath="firstDay")
 * @UniqueEntity(fields={"lastDay","academicYear"},errorPath="lastDay")
 * @Check\Term()
 */
class AcademicYearTerm extends AbstractEntity
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
     * @var AcademicYear|null
     * @ORM\ManyToOne(targetEntity="App\Modules\School\Entity\AcademicYear", inversedBy="terms")
     * @ORM\JoinColumn(name="academic_year",referencedColumnName="id",nullable=false)
     * @Assert\NotBlank()
     * @ORM\OrderBy({"firstDay" = "ASC"})
     */
    private $academicYear;

    /**
     * @var string|null
     * @ORM\Column(length=20)
     * @Assert\Length(max=20)
     * @Assert\NotBlank()
     */
    private $name;

    /**
     * @var string|null
     * @ORM\Column(length=4)
     * @Assert\Length(max=4)
     * @Assert\NotBlank()
     */
    private $abbreviation;

    /**
     * @var \DateTimeImmutable|null
     * @ORM\Column(type="date_immutable",nullable=true)
     * @Assert\NotBlank()
     */
    private $firstDay;

    /**
     * @var \DateTimeImmutable|null
     * @ORM\Column(type="date_immutable",nullable=true)
     * @Assert\NotBlank()
     */
    private $lastDay;

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
     * @return AcademicYearTerm
     */
    public function setId(?string $id): AcademicYearTerm
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
    public function getAbbreviation(): ?string
    {
        return $this->abbreviation;
    }

    /**
     * @param string|null $abbreviation
     * @return AcademicYearTerm
     */
    public function setAbbreviation(?string $abbreviation): AcademicYearTerm
    {
        $this->abbreviation = $abbreviation;
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
            'abbr' => $this->getAbbreviation(),
            'year' => $this->getAcademicYear()->getName(),
            'dates' => $dates,
            'canDelete' => ProviderFactory::create(AcademicYearTerm::class)->canDelete($this),
        ];
    }

    /**
     * create
     * @return string
     */
    public function create(): array
    {
        return ["CREATE TABLE `__prefix__AcademicYearTerm` (
                    `id` CHAR(36) NOT NULL COMMENT '(DC2Type:guid)',
                    `name` CHAR(20) NOT NULL,
                    `abbreviation` CHAR(4) NOT NULL,
                    `first_day` date DEFAULT NULL COMMENT '(DC2Type:date_immutable)',
                    `last_day` date DEFAULT NULL COMMENT '(DC2Type:date_immutable)',
                    `academic_year` CHAR(36) DEFAULT NULL,
                    PRIMARY KEY (`id`),
                    UNIQUE KEY `name` (`academic_year`,`name`),
                    UNIQUE KEY `abbr` (`academic_year`,`abbreviation`),
                    KEY `academic_year` (`academic_year`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;"];
    }

    /**
     * foreignConstraints
     * @return string
     */
    public function foreignConstraints(): string
    {
        return 'ALTER TABLE `__prefix__AcademicYearTerm`
                    ADD CONSTRAINT FOREIGN KEY (`academic_year`) REFERENCES `__prefix__AcademicYear` (`id`);';
    }

    /**
     * coreData
     * @return string
     */public static function getVersion(): string
    {
        return self::VERSION;
    }
}
