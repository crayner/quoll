<?php
/**
 * Created by PhpStorm.
 *
* Quoll
 *
 * (c) 2018 Craig Rayner <craig@craigrayner.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * UserProvider: craig
 * Date: 23/11/2018
 * Time: 11:12
 */
namespace App\Modules\School\Entity;

use App\Manager\EntityInterface;
use App\Provider\ProviderFactory;
use App\Util\TranslationHelper;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\PersistentCollection;
use App\Modules\School\Validator as Check;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class AcademicYear
 * @package App\Modules\School\Entity
 * @ORM\Entity(repositoryClass="App\Modules\School\Repository\AcademicYearRepository")
 * @ORM\Table(options={"auto_increment": 1}, name="AcademicYear", uniqueConstraints={@ORM\UniqueConstraint(name="name", columns={"name"}), @ORM\UniqueConstraint(name="sequence", columns={"sequenceNumber"})})
 * @Check\AcademicYear()
 * @UniqueEntity("sequenceNumber")
 * @UniqueEntity("name")
 */
class AcademicYear implements EntityInterface
{
    /**
     * @var integer|null
     * @ORM\Id
     * @ORM\Column(type="integer", name="id", columnDefinition="INT(3) UNSIGNED")
     * @ORM\GeneratedValue
     */
    private $id;

    /**
     * @var string|null
     * @ORM\Column(length=9, unique=true)
     * @Assert\NotBlank()
     * @Assert\Length(max=9)
     */
    private $name;

    /**
     * @var string|null
     * @ORM\Column(length=8, options={"default": "Upcoming"})
     * @Assert\Choice(callback="getStatusList")
     */
    private $status = 'Upcoming';

    /**
     * @var array
     */
    private static $statusList = ['Past', 'Current', 'Upcoming'];

    /**
     * @var \DateTimeImmutable|null
     * @ORM\Column(type="date_immutable", name="firstDay", nullable=true)
     * @Assert\NotBlank()
     */
    private $firstDay;

    /**
     * @var \DateTimeImmutable|null
     * @ORM\Column(type="date_immutable", name="lastDay", nullable=true)
     * @Assert\NotBlank()
     */
    private $lastDay;

    /**
     * @var integer
     * @ORM\Column(type="smallint",columnDefinition="INT(3)",name="sequenceNumber",unique=true)
     * @Assert\Range(min=1,max=999)
     */
    private $sequenceNumber;

    /**
     * @var Collection|AcademicYearTerm[]
     * @ORM\OneToMany(targetEntity="App\Modules\School\Entity\AcademicYearTerm", mappedBy="academicYear")
     */
    private $terms;

    /**
     * @var Collection|AcademicYearSpecialDay[]
     * @ORM\OneToMany(targetEntity="App\Modules\School\Entity\AcademicYearSpecialDay", mappedBy="academicYear")
     */
    private $specialDays;

    /**
     * AcademicYear constructor.
     */
    public function __construct()
    {
        $this->terms = new ArrayCollection();
        $this->specialDays = new ArrayCollection();
    }

    /**
     * @return array
     */
    public static function getStatusList(): array
    {
        return self::$statusList;
    }

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param int|null $id
     * @return AcademicYear
     */
    public function setId(?int $id): AcademicYear
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
     * @return AcademicYear
     */
    public function setName(?string $name): AcademicYear
    {
        $this->name = mb_substr($name, 0,9);
        return $this;
    }

    /**
     * @return string|null
     */
    public function getStatus(): ?string
    {
        return $this->status;
    }

    /**
     * setStatus
     * @param string|null $status
     * @return AcademicYear
     */
    public function setStatus(?string $status): AcademicYear
    {
        $this->status = in_array($status, self::getStatusList()) ? $status : 'Unknown' ;
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
     * @return AcademicYear
     */
    public function setFirstDay(?\DateTimeImmutable $firstDay): AcademicYear
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
     * @return AcademicYear
     */
    public function setLastDay(?\DateTimeImmutable $lastDay): AcademicYear
    {
        $this->lastDay = $lastDay;
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
     * @return AcademicYear
     */
    public function setSequenceNumber(int $sequenceNumber): AcademicYear
    {
        $this->sequenceNumber = $sequenceNumber;
        return $this;
    }

    /**
     * isEqualTo
     * @param $entity
     * @return bool
     */
    public function isEqualTo($entity): bool
    {
        if ($this->getId() !== $entity->getId())
            return false;

        if ($this->getName() !== $entity->getName())
            return false;

        if ($this->getFirstDay() !== $entity->getFirstDay())
            return false;

        if ($this->getLastDay() !== $entity->getLastDay())
            return false;

        return true;
    }

    /**
     * __toString
     * @return string
     */
    public function __toString(): string
    {
       return $this->getName();
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
            'status' => TranslationHelper::translate('academicyear.status.'.strtolower($this->getStatus()), [], 'School'),
            'dates' => $dates,
            'canDelete' => $this->canDelete(),
            'sequence' => $this->getSequenceNumber(),
            'id' => $this->getId(),
        ];
    }

    /**
     * getNameDates
     * @return string
     */
    public function getNameDates()
    {
        return $this->getName() . ': ' . $this->getFirstDay()->format('Y-m-d') . ' - ' . $this->getLastDay()->format('Y-m-d');
    }

    /**
     * getTerms
     * @return ArrayCollection|Collection|PersistentCollection|AcademicYearTerm[]
     */
    public function getTerms()
    {
        if (null === $this->terms)
            $this->terms = new ArrayCollection();
        if ($this->terms instanceof PersistentCollection)
            $this->terms->initialize();

        return $this->terms;
    }

    /**
     * Terms.
     *
     * @param Collection|AcademicYearTerm[] $terms
     * @return AcademicYear
     */
    public function setTerms($terms)
    {
        $this->terms = $terms;
        return $this;
    }

    /**
     * @return Collection|AcademicYearSpecialDay[]
     */
    public function getSpecialDays()
    {
        if (null === $this->specialDays)
            $this->specialDays = new ArrayCollection();
        if ($this->specialDays instanceof PersistentCollection)
            $this->specialDays->initialize();

        return $this->specialDays;
    }

    /**
     * SpecialDays.
     *
     * @param Collection|AcademicYearSpecialDay[] $specialDays
     * @return AcademicYear
     */
    public function setSpecialDays($specialDays)
    {
        $this->specialDays = $specialDays;
        return $this;
    }

    /**
     * hasSpecialDay
     * @param \DateTimeImmutable $date
     * @return bool
     */
    public function hasSpecialDay(\DateTimeImmutable $date): bool
    {
        $found = $this->getSpecialDays()->filter(function(AcademicYearSpecialDay $day) use ($date) {
            if ($date->format('Ymd') === $day->getDate()->format('Ymd'))
                return $day;
            }
        );

        if ($found->count() === 1) {
            $this->specialDays->removeElement($found->first());
            $this->specialDays->set($found->first()->getDate()->format('Ymd'), $found->first());
            return true;
        }
        return false;
    }

    /**
     * canDelete
     * @return bool
     */
    public function canDelete(): bool
    {
        return ProviderFactory::create(AcademicYear::class)->canDelete($this);
    }

    /**
     * create
     * @return string
     */
    public function create(): string
    {
        return "CREATE TABLE `__prefix__AcademicYear` (
                    `id` int(3) UNSIGNED NOT NULL AUTO_INCREMENT,
                    `name` varchar(9) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
                    `status` varchar(8) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Upcoming',
                    `firstDay` date DEFAULT NULL COMMENT '(DC2Type:date_immutable)',
                    `lastDay` date DEFAULT NULL COMMENT '(DC2Type:date_immutable)',
                    `sequenceNumber` int(3) DEFAULT NULL,
                    PRIMARY KEY (`id`),
                    UNIQUE KEY `name` (`name`) USING BTREE,
                    UNIQUE KEY `sequence` (`sequenceNumber`) USING BTREE
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
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

}