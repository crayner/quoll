<?php
/**
 * Created by PhpStorm.
 *
 * Kookaburra
 *
 * (c) 2018 Craig Rayner <craig@craigrayner.com>
 *
 * User: craig
 * Date: 5/12/2018
 * Time: 16:35
 */
namespace App\Modules\Timetable\Entity;

use App\Manager\EntityInterface;
use App\Modules\School\Entity\AcademicYear;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\PersistentCollection;

/**
 * Class TT
 * @package App\Modules\Timetable\Entity
 * @ORM\Entity(repositoryClass="App\Modules\Timetable\Repository\TTRepository")
 * @ORM\Table(options={"auto_increment": 1}, name="TT")
 */
class TT implements EntityInterface
{
    /**
     * @var integer|null
     * @ORM\Id
     * @ORM\Column(type="integer",columnDefinition="INT(8) UNSIGNED AUTO_INCREMENT")
     * @ORM\GeneratedValue
     */
    private $id;

    /**
     * @var AcademicYear|null
     * @ORM\ManyToOne(targetEntity="App\Modules\School\Entity\AcademicYear")
     * @ORM\JoinColumn(name="academic_year", referencedColumnName="id", nullable=false)
     */
    private $AcademicYear;

    /**
     * @var string|null
     * @ORM\Column(length=30,unique=true)
     */
    private $name;

    /**
     * @var string|null
     * @ORM\Column(length=12, name="nameShort",unique=true)
     */
    private $nameShort;

    /**
     * @var string|null
     * @ORM\Column(length=24, name="nameShortDisplay", options={"default": "Day Of The Week"})
     */
    private $nameShortDisplay;

    /**
     * @var array
     */
    private static $nameShortDisplayList = ['Day Of The Week','Dashboard Day Short Name',''];

    /**
     * @var array|null
     * @ORM\Column(name="year_group_list",type="simple_array")
     */
    private $yearGroupList;

    /**
     * @var string|null
     * @ORM\Column(length=1)
     */
    private $active;

    /**
     * @var Collection
     * @ORM\OneToMany(targetEntity="TTDay", mappedBy="TT")
     */
    private $TTDays;

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param int|null $id
     * @return TT
     */
    public function setId(?int $id): TT
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return AcademicYear|null
     */
    public function getAcademicYear(): ?AcademicYear
    {
        return $this->AcademicYear;
    }

    /**
     * @param AcademicYear|null $AcademicYear
     * @return TT
     */
    public function setAcademicYear(?AcademicYear $AcademicYear): TT
    {
        $this->AcademicYear = $AcademicYear;
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
     * @return TT
     */
    public function setName(?string $name): TT
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
     * @return TT
     */
    public function setNameShort(?string $nameShort): TT
    {
        $this->nameShort = $nameShort;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getNameShortDisplay(): ?string
    {
        return $this->nameShortDisplay;
    }

    /**
     * @param string|null $nameShortDisplay
     * @return TT
     */
    public function setNameShortDisplay(?string $nameShortDisplay): TT
    {
        $this->nameShortDisplay = in_array($nameShortDisplay, self::getNameShortDisplayList()) ? $nameShortDisplay : '';
        return $this;
    }

    /**
     * @return array|null
     */
    public function getYearGroupList(): ?array
    {
        return $this->yearGroupList;
    }

    /**
     * @param array|null $yearGroupList
     */
    public function setYearGroupList(?array $yearGroupList): void
    {
        $this->yearGroupList = $yearGroupList;
    }

    /**
     * @return string|null
     */
    public function getActive(): ?string
    {
        return $this->active;
    }

    /**
     * @param string|null $active
     * @return TT
     */
    public function setActive(?string $active): TT
    {
        $this->active = $active;
        return $this;
    }

    /**
     * @return array
     */
    public static function getNameShortDisplayList(): array
    {
        return self::$nameShortDisplayList;
    }

    /**
     * getTTDays
     * @return Collection
     */
    public function getTTDays(): Collection
    {
        if (empty($this->tTDays))
            $this->tTDays = new ArrayCollection();

        if ($this->tTDays instanceof PersistentCollection)
            $this->tTDays->initialize();

        return $this->tTDays;
    }

    /**
     * @param Collection $tTDays
     * @return TT
     */
    public function setTTDays(Collection $tTDays): TT
    {
        $this->tTDays = $tTDays;
        return $this;
    }

    /**
     * __toString
     * @return string
     */
    public function __toString(): string
    {
        return $this->getName() . ' ('.$this->getNameShort().')';
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

    public function create(): string
    {
        return 'CREATE TABLE `__prefix__TT` (
                    `id` int(8) UNSIGNED NOT NULL AUTO_INCREMENT,
                    `name` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
                    `nameShort` varchar(12) COLLATE utf8_unicode_ci NOT NULL,
                    `nameShortDisplay` varchar(24) COLLATE utf8_unicode_ci NOT NULL DEFAULT \'Day Of The Week\',
                    `year_group_list` varchar(191) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL COMMENT \'(DC2Type:simple_array)\',
                    `active` varchar(1) COLLATE utf8_unicode_ci NOT NULL,
                    `academic_year` int(3) UNSIGNED DEFAULT NULL,
                    PRIMARY KEY (`id`),
                    KEY `IDX_9431F94371FA7520` (`academic_year`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;';
    }

    public function foreignConstraints(): string
    {
        return 'ALTER TABLE `__prefix__TT`
  ADD CONSTRAINT FOREIGN KEY (`academic_year`) REFERENCES `__prefix__AcademicYear` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT;';
    }

    public function coreData(): string
    {
        return '';
    }
}