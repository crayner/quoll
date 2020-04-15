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
namespace App\Modules\School\Entity;

use App\Manager\EntityInterface;
use App\Modules\School\Manager\SpecialDayManager;
use App\Modules\School\Util\AcademicYearHelper;
use App\Modules\School\Validator as Check;
use App\Util\TranslationHelper;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class AcademicYearSpecialDay
 * @package App\Modules\School\Entity
 * @ORM\Entity(repositoryClass="App\Modules\School\Repository\AcademicYearSpecialDayRepository")
 * @ORM\Table(options={"auto_increment": 1}, name="AcademicYearSpecialDay",
 *     uniqueConstraints={@ORM\UniqueConstraint(name="date", columns={"date"})},
 *     indexes={@ORM\Index(name="academic_year", columns={"academic_year"})})
 * @Check\SpecialDay()
 * @UniqueEntity("date")
 */
class AcademicYearSpecialDay implements EntityInterface
{
    /**
     * @var integer|null
     * @ORM\Id()
     * @ORM\Column(type="integer", name="id", columnDefinition="INT(10) UNSIGNED AUTO_INCREMENT")
     * @ORM\GeneratedValue
     */
    private $id;

    /**
     * @var AcademicYear|null
     * @ORM\ManyToOne(targetEntity="App\Modules\School\Entity\AcademicYear", inversedBy="specialDays")
     * @ORM\JoinColumn(name="academic_year", referencedColumnName="id", nullable=false)
     */
    private $academicYear;

    /**
     * @var string|null
     * @ORM\Column(length=14, name="type")
     */
    private $type ;

    /**
     * @var array
     */
    private static $typeList = ['School Closure', 'Timing Change'];

    /**
     * @var string|null
     * @ORM\Column(length=20)
     * @Assert\NotBlank()
     */
    private $name;

    /**
     * @var string|null
     * @ORM\Column(nullable=true)
     */
    private $description;

    /**
     * @var \DateTimeImmutable|null
     * @ORM\Column(type="date_immutable")
     * @Assert\NotBlank()
     */
    private $date;

    /**
     * @var \DateTimeImmutable|null
     * @ORM\Column(type="time_immutable", name="schoolOpen", nullable=true)
     */
    private $schoolOpen;

    /**
     * @var \DateTimeImmutable|null
     * @ORM\Column(type="time_immutable", name="schoolStart", nullable=true)
     */
    private $schoolStart;

    /**
     * @var \DateTimeImmutable|null
     * @ORM\Column(type="time_immutable", name="schoolEnd", nullable=true)
     */
    private $schoolEnd;

    /**
     * @var \DateTimeImmutable|null
     * @ORM\Column(type="time_immutable", name="schoolClose", nullable=true)
     */
    private $schoolClose;

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param int|null $id
     * @return AcademicYearSpecialDay
     */
    public function setId(?int $id): AcademicYearSpecialDay
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return null|AcademicYear
     */
    public function getAcademicYear(): ?AcademicYear
    {
        return $this->academicYear;
    }

    /**
     * setAcademicYear
     * @param AcademicYear|null $academicYear
     * @return AcademicYearSpecialDay
     */
    public function setAcademicYear(?AcademicYear $academicYear): AcademicYearSpecialDay
    {
        $this->academicYear = $academicYear;
        return $this;
    }

    /**
     * @return null|string
     */
    public function getType(): ?string
    {
        return $this->type;
    }

    /**
     * @param string $type
     * @return AcademicYearSpecialDay
     */
    public function setType(string $type): AcademicYearSpecialDay
    {
        $this->type = $type;
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
     * @return AcademicYearSpecialDay
     */
    public function setName(?string $name): AcademicYearSpecialDay
    {
        $this->name = $name;
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
     * @return AcademicYearSpecialDay
     */
    public function setDescription(?string $description): AcademicYearSpecialDay
    {
        $this->description = $description;
        return $this;
    }

    /**
     * @return \DateTimeImmutable|null
     */
    public function getDate(): ?\DateTimeImmutable
    {
        return $this->date;
    }

    /**
     * Date.
     *
     * @param \DateTimeImmutable|null $date
     * @return AcademicYearSpecialDay
     */
    public function setDate(?\DateTimeImmutable $date): AcademicYearSpecialDay
    {
        $this->date = $date;
        return $this;
    }

    /**
     * @return \DateTimeImmutable|null
     */
    public function getSchoolOpen(): ?\DateTimeImmutable
    {
        return $this->schoolOpen;
    }

    /**
     * SchoolOpen.
     *
     * @param \DateTimeImmutable|null $schoolOpen
     * @return AcademicYearSpecialDay
     */
    public function setSchoolOpen(?\DateTimeImmutable $schoolOpen): AcademicYearSpecialDay
    {
        $this->schoolOpen = $schoolOpen;
        return $this;
    }

    /**
     * @return \DateTimeImmutable|null
     */
    public function getSchoolStart(): ?\DateTimeImmutable
    {
        return $this->schoolStart;
    }

    /**
     * SchoolStart.
     *
     * @param \DateTimeImmutable|null $schoolStart
     * @return AcademicYearSpecialDay
     */
    public function setSchoolStart(?\DateTimeImmutable $schoolStart): AcademicYearSpecialDay
    {
        $this->schoolStart = $schoolStart;
        return $this;
    }

    /**
     * @return \DateTimeImmutable|null
     */
    public function getSchoolEnd(): ?\DateTimeImmutable
    {
        return $this->schoolEnd;
    }

    /**
     * SchoolEnd.
     *
     * @param \DateTimeImmutable|null $schoolEnd
     * @return AcademicYearSpecialDay
     */
    public function setSchoolEnd(?\DateTimeImmutable $schoolEnd): AcademicYearSpecialDay
    {
        $this->schoolEnd = $schoolEnd;
        return $this;
    }

    /**
     * @return \DateTimeImmutable|null
     */
    public function getSchoolClose(): ?\DateTimeImmutable
    {
        return $this->schoolClose;
    }

    /**
     * SchoolClose.
     *
     * @param \DateTimeImmutable|null $schoolClose
     * @return AcademicYearSpecialDay
     */
    public function setSchoolClose(?\DateTimeImmutable $schoolClose): AcademicYearSpecialDay
    {
        $this->schoolClose = $schoolClose;
        return $this;
    }

    /**
     * @param \DateTime $date
     * @return AcademicYearSpecialDay
     */
    public static function createSpecialDay(\DateTime $date): AcademicYearSpecialDay
    {
        $self = new self();
        $self->setDate($date);
        $self->setType('School Closure');
        $self->setName('ERROR');
        $self->setDescription('Database Error: The date was not found in the term data.');
        $self->setAcademicYearTerm(AcademicYearHelper::findOneTermByDay($date));
        return $self;
    }

    /**
     * getTypeList
     * @return array
     */
    public static function getTypeList(): array
    {
        return self::$typeList;
    }

    /**
     * __toString
     * @return string
     */
    public function __toString(): string
    {
        return $this->getDate()->format('Y-m-d');
    }

    /**
     * toArray
     * @param string|null $name
     * @return array
     */
    public function toArray(?string $name = null): array
    {
        if ($name === 'new')
        {
            return [
                'type' => 'School Closure',
                'name' => 'New Special Day',
                'description' => '',
            ];
        }
        return [
            'year' => $this->getAcademicYear()->getName(),
            'name' => $this->getName(),
            'description' => $this->getDescription(),
            'date' => $this->getDate()->format('jS M/Y'),
            'type' => TranslationHelper::translate('academicyearspecialday.type.'.strtolower($this->getType()), [], 'SchoolAdmin'),
            'canDelete' => true,
            'canDuplicate' => SpecialDayManager::canDuplicate($this),
        ];
    }

    /**
     * create
     * @return string
     */
    public function create(): string
    {
        return 'CREATE TABLE `__prefix__AcademicYearSpecialDay` (
                    `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                    `type` varchar(14) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
                    `name` varchar(20) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
                    `description` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
                    `date` date NOT NULL COMMENT \'(DC2Type:date_immutable)\',
                    `schoolOpen` time DEFAULT NULL COMMENT \'(DC2Type:time_immutable)\',
                    `schoolStart` time DEFAULT NULL COMMENT \'(DC2Type:time_immutable)\',
                    `schoolEnd` time DEFAULT NULL COMMENT \'(DC2Type:time_immutable)\',
                    `schoolClose` time DEFAULT NULL COMMENT \'(DC2Type:time_immutable)\',
                    `academic_year` int(3) UNSIGNED DEFAULT NULL,
                    PRIMARY KEY (`id`),
                    UNIQUE KEY `date` (`date`),
                    KEY `academic_year` (`academic_year`) USING BTREE
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;';
    }

    /**
     * foreignConstraints
     * @return string
     */
    public function foreignConstraints(): string
    {
        return 'ALTER TABLE `__prefix__AcademicYearSpecialDay`
  ADD CONSTRAINT FOREIGN KEY (`academic_year`) REFERENCES `__prefix__AcademicYear` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT;
';
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