<?php
/**
 * Created by PhpStorm.
 *
* Quoll
 *
 * (c) 2018 Craig Rayner <craig@craigrayner.com>
 *
 * User: craig
 * Date: 5/12/2018
 * Time: 16:45
 */
namespace App\Modules\Timetable\Entity;

use App\Manager\EntityInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\PersistentCollection;

/**
 * Class TTColumnRow
 * @package App\Modules\Timetable\Entity
 * @ORM\Entity(repositoryClass="App\Modules\Timetable\Repository\TTColumnRowRepository")
 * @ORM\Table(name="TTColumnRow", indexes={@ORM\Index(name="timetable_column", columns={"timetable_column"})})
 */
class TTColumnRow implements EntityInterface
{
    CONST VERSION = '20200401';

    /**
     * @var string|null
     * @ORM\Id()
     * @ORM\Column(type="guid")
     * @ORM\GeneratedValue(strategy="UUID")
     */
    private $id;

    /**
     * @var TTColumn|null
     * @ORM\ManyToOne(targetEntity="TTColumn", inversedBy="timetableColumnRows")
     * @ORM\JoinColumn(name="timetable_column", referencedColumnName="id", nullable=false)
     */
    private $TTColumn;

    /**
     * @var string|null
     * @ORM\Column(length=12)
     */
    private $name;

    /**
     * @var string|null
     * @ORM\Column(length=4, name="abbreviation")
     */
    private $abbreviation;

    /**
     * @var \DateTimeImmutable|null
     * @ORM\Column(type="time_immutable")
     */
    private $timeStart;

    /**
     * @var \DateTimeImmutable|null
     * @ORM\Column(type="time_immutable")
     */
    private $timeEnd;

    /**
     * @var string
     * @ORM\Column(length=8)
     */
    private $type;

    /**
     * @var array
     */
    private static $typeList = ['Lesson','Pastoral','Sport','Break','Service','Other'];

    /**
     * @var Collection|null
     * @ORM\OneToMany(targetEntity="TTDayRowClass", mappedBy="TTColumnRow")
     */
    private $TTDayRowClasses;

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
     * @return TTColumnRow
     */
    public function setId(?string $id): TTColumnRow
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return TTColumn|null
     */
    public function getTTColumn(): ?TTColumn
    {
        return $this->TTColumn;
    }

    /**
     * @param TTColumn|null $TTColumn
     * @return TTColumnRow
     */
    public function setTTColumn(?TTColumn $TTColumn): TTColumnRow
    {
        $this->TTColumn = $TTColumn;
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
     * @return TTColumnRow
     */
    public function setName(?string $name): TTColumnRow
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
     * @return TTColumnRow
     */
    public function setAbbreviation(?string $abbreviation): TTColumnRow
    {
        $this->abbreviation = $abbreviation;
        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getTimeStart(): ?\DateTime
    {
        return $this->timeStart;
    }

    /**
     * @param \DateTime|null $timeStart
     * @return TTColumnRow
     */
    public function setTimeStart(?\DateTime $timeStart): TTColumnRow
    {
        $this->timeStart = $timeStart;
        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getTimeEnd(): ?\DateTime
    {
        return $this->timeEnd;
    }

    /**
     * @param \DateTime|null $timeEnd
     * @return TTColumnRow
     */
    public function setTimeEnd(?\DateTime $timeEnd): TTColumnRow
    {
        $this->timeEnd = $timeEnd;
        return $this;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     * @return TTColumnRow
     */
    public function setType(string $type): TTColumnRow
    {
        $this->type = in_array($type, self::getTypeList()) ? $type : null ;
        return $this;
    }

    /**
     * @return array
     */
    public static function getTypeList(): array
    {
        return self::$typeList;
    }

    /**
     * getTTDayRowClasses
     * @return Collection|null
     */
    public function getTTDayRowClasses(): ?Collection
    {
        if (empty($this->TTDayRowClasses))
            $this->TTDayRowClasses = new ArrayCollection();

        if ($this->TTDayRowClasses instanceof PersistentCollection)
            $this->TTDayRowClasses->initialize();

        return $this->TTDayRowClasses;
    }

    /**
     * @param Collection|null $TTDayRowClasses
     * @return TTColumnRow
     */
    public function setTTDayRowClasses(?Collection $TTDayRowClasses): TTColumnRow
    {
        $this->TTDayRowClasses = $TTDayRowClasses;
        return $this;
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
        return "CREATE TABLE `__prefix__TTColumnRow` (
                    `id` CHAR(36) NOT NULL COMMENT '(DC2Type:guid)',
                    `name` CHAR(12) COLLATE ut8mb4_unicode_ci NOT NULL,
                    `abbreviation` CHAR(4) COLLATE ut8mb4_unicode_ci NOT NULL,
                    `time_start` time NOT NULL COMMENT '(DC2Type:time_immutable)',
                    `time_end` time NOT NULL COMMENT '(DC2Type:time_immutable)',
                    `type` CHAR(8) COLLATE ut8mb4_unicode_ci NOT NULL,
                    `timetable_column` CHAR(36) DEFAULT NULL,
                    PRIMARY KEY (`id`),
                    KEY `timetable_column` (`timetable_column`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=ut8mb4_unicode_ci;";
    }

    public function foreignConstraints(): string
    {
        return "ALTER TABLE `__prefix__TTColumnRow`
                    ADD CONSTRAINT FOREIGN KEY (`timetable_column`) REFERENCES `__prefix__TTColumn` (`id`);";
    }

    public function coreData(): string
    {
        return '';
    }

    public static function getVersion(): string
    {
        return self::VERSION;
    }
}
