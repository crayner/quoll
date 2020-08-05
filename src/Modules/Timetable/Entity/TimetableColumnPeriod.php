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
 * Date: 5/12/2018
 * Time: 16:45
 */
namespace App\Modules\Timetable\Entity;

use App\Manager\AbstractEntity;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\PersistentCollection;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class TimetableColumnPeriod
 * @package App\Modules\Timetable\Entity
 * @ORM\Entity(repositoryClass="App\Modules\Timetable\Repository\TimetableColumnPeriodRepository")
 * @ORM\Table(name="TimetableColumnPeriod",
 *     indexes={@ORM\Index(name="timetable_column", columns={"timetable_column"})},
 *     uniqueConstraints={@ORM\UniqueConstraint(name="name_timetable_column",columns={"name","timetable_column"}),
 *     @ORM\UniqueConstraint(name="abbreviation_timetable_column",columns={"abbreviation","timetable_column"})}
 * )
 * @UniqueEntity({"name","timetableColumn"})
 * @UniqueEntity({"abbreviation","timetableColumn"})
 * @\App\Modules\Timetable\Validator\TimetableColumnPeriod()
 */
class TimetableColumnPeriod extends AbstractEntity
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
     * @var TimetableColumn|null
     * @ORM\ManyToOne(targetEntity="TimetableColumn",inversedBy="timetableColumnPeriods")
     * @ORM\JoinColumn(name="timetable_column",referencedColumnName="id")
     */
    private $timetableColumn;

    /**
     * @var string|null
     * @ORM\Column(length=12)
     * @Assert\NotBlank()
     * @Assert\Length(max=12)
     */
    private $name;

    /**
     * @var string|null
     * @ORM\Column(length=4, name="abbreviation")
     * @Assert\NotBlank()
     * @Assert\Length(max=4)
     */
    private $abbreviation;

    /**
     * @var DateTimeImmutable|null
     * @ORM\Column(type="time_immutable")
     * @Assert\NotBlank()
     */
    private $timeStart;

    /**
     * @var DateTimeImmutable|null
     * @ORM\Column(type="time_immutable")
     * @Assert\NotBlank()
     */
    private $timeEnd;

    /**
     * @var string
     * @ORM\Column(length=8)
     * @Assert\Choice(callback="getTypeList")
     */
    private $type;

    /**
     * @var array
     */
    private static $typeList = ['Lesson','Pastoral','Sport','Break','Service','Other'];

    /**
     * @var Collection|TimetableDayRowClass[]|null
     * @ORM\OneToMany(targetEntity="TimetableDayRowClass", mappedBy="timetableColumnPeriod")
     */
    private $timetableDayRowClasses;

    /**
     * TimetableColumnPeriod constructor.
     * @param TimetableColumn|null $column
     */
    public function __construct(?TimetableColumn $column = null)
    {
        $this->setTimetableColumn($column)
            ->setTimetableDayRowClasses(new ArrayCollection());
    }
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
     * @return TimetableColumnPeriod
     */
    public function setId(?string $id): TimetableColumnPeriod
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return TimetableColumn|null
     */
    public function getTimetableColumn(): ?TimetableColumn
    {
        return $this->timetableColumn;
    }

    /**
     * @param TimetableColumn|null $timetableColumn
     * @param bool $reflect
     * @return TimetableColumnPeriod
     */
    public function setTimetableColumn(?TimetableColumn $timetableColumn, bool $reflect = true): TimetableColumnPeriod
    {
        if ($timetableColumn instanceof TimetableColumn && $reflect) {
            $timetableColumn->addTimetableColumnPeriod($this, false);
        }
        $this->timetableColumn = $timetableColumn;
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
     * @return TimetableColumnPeriod
     */
    public function setName(?string $name): TimetableColumnPeriod
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
     * @return TimetableColumnPeriod
     */
    public function setAbbreviation(?string $abbreviation): TimetableColumnPeriod
    {
        $this->abbreviation = $abbreviation;
        return $this;
    }

    /**
     * @return DateTimeImmutable|null
     */
    public function getTimeStart(): ?DateTimeImmutable
    {
        return $this->timeStart;
    }

    /**
     * @param DateTimeImmutable|null $timeStart
     * @return TimetableColumnPeriod
     */
    public function setTimeStart(?DateTimeImmutable $timeStart): TimetableColumnPeriod
    {
        $this->timeStart = $timeStart;
        return $this;
    }

    /**
     * @return DateTimeImmutable|null
     */
    public function getTimeEnd(): ?DateTimeImmutable
    {
        return $this->timeEnd;
    }

    /**
     * @param DateTimeImmutable|null $timeEnd
     * @return TimetableColumnPeriod
     */
    public function setTimeEnd(?DateTimeImmutable $timeEnd): TimetableColumnPeriod
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
     * @return TimetableColumnPeriod
     */
    public function setType(string $type): TimetableColumnPeriod
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
     * gettimetableDayRowClasses
     * @return Collection|null
     */
    public function getTimetableDayRowClasses(): ?Collection
    {
        if (empty($this->timetableDayRowClasses))
            $this->timetableDayRowClasses = new ArrayCollection();

        if ($this->timetableDayRowClasses instanceof PersistentCollection)
            $this->timetableDayRowClasses->initialize();

        return $this->timetableDayRowClasses;
    }

    /**
     * @param Collection|null $timetableDayRowClasses
     * @return TimetableColumnPeriod
     */
    public function setTimetableDayRowClasses(?Collection $timetableDayRowClasses): TimetableColumnPeriod
    {
        $this->timetableDayRowClasses = $timetableDayRowClasses;
        return $this;
    }

    /**
     * toArray
     * @param string|null $name
     * @return array
     */
    public function toArray(?string $name = null): array
    {
        return [
            'id' => $this->getId(),
            'column' => $this->getTimetableColumn()->getId(),
            'name' => $this->getName(),
            'abbreviation' => $this->getAbbreviation(),
            'time' => $this->getTimeStartName() . ' - ' . $this->getTimeEndName(),
            'type' => $this->getType(),
            'canDelete' => true,
        ];
    }

    /**
     * getTimeStartName
     * @return string
     * 4/08/2020 11:51
     */
    public function getTimeStartName(): string
    {
        return $this->getTimeStart() ? $this->getTimeStart()->format('H:i') : '00:00';
    }

    /**
     * getTimeEndName
     * @return string
     * 4/08/2020 11:52
     */
    public function getTimeEndName(): string
    {
        return $this->getTimeEnd() ? $this->getTimeEnd()->format('H:i') : '23:59';
    }
}
