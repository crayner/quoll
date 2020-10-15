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
use App\Modules\RollGroup\Entity\RollGroup;
use App\Provider\ProviderFactory;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\PersistentCollection;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class TimetableDayPeriod
 * @package App\Modules\Timetable\Entity
 * @ORM\Entity(repositoryClass="App\Modules\Timetable\Repository\TimetablePeriodRepository")
 * @ORM\Table(name="TimetablePeriod",
 *     indexes={@ORM\Index(name="timetable_day", columns={"timetable_day"})},
 *     uniqueConstraints={@ORM\UniqueConstraint(name="name_timetable_day",columns={"name","timetable_day"}),
 *     @ORM\UniqueConstraint(name="abbreviation_timetable_day",columns={"abbreviation","timetable_day"})}
 * )
 * @UniqueEntity({"name","timetableDay"})
 * @UniqueEntity({"abbreviation","timetableDay"})
 * @\App\Modules\Timetable\Validator\TimetableDayPeriod()
 */
class TimetablePeriod extends AbstractEntity
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
     * @var TimetableDay|null
     * @ORM\ManyToOne(targetEntity="TimetableDay",inversedBy="periods")
     * @ORM\JoinColumn(name="timetable_day",referencedColumnName="id")
     */
    private $timetableDay;

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
    private static array $typeList = ['Lesson','Pastoral','Sport','Break','Service','Other'];

    /**
     * @var Collection|TimetablePeriodClass[]|null
     * @ORM\OneToMany(targetEntity="TimetablePeriodClass", mappedBy="period")
     */
    private $periodClasses;

    /**
     * TimetableDayPeriod constructor.
     * @param TimetableDay|null $timetableDay
     */
    public function __construct(?TimetableDay $timetableDay = null)
    {
        $this->setTimetableDay($timetableDay)
            ->setPeriodClasses(new ArrayCollection());
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
     * @return TimetablePeriod
     */
    public function setId(?string $id): TimetablePeriod
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return TimetableDay|null
     */
    public function getTimetableDay(): ?TimetableDay
    {
        return $this->timetableDay;
    }

    /**
     * @param TimetableDay|null $timetableDay
     * @param bool $reflect
     * @return TimetablePeriod
     */
    public function setTimetableDay(?TimetableDay $timetableDay, bool $reflect = true): TimetablePeriod
    {
        if ($timetableDay instanceof TimetableDay && $reflect) {
            $timetableDay->addPeriod($this, false);
        }
        $this->timetableDay = $timetableDay;
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
     * @return TimetablePeriod
     */
    public function setName(?string $name): TimetablePeriod
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
     * @return TimetablePeriod
     */
    public function setAbbreviation(?string $abbreviation): TimetablePeriod
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
     * @return TimetablePeriod
     */
    public function setTimeStart(?DateTimeImmutable $timeStart): TimetablePeriod
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
     * @return TimetablePeriod
     */
    public function setTimeEnd(?DateTimeImmutable $timeEnd): TimetablePeriod
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
     * @return TimetablePeriod
     */
    public function setType(string $type): TimetablePeriod
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
     * getperiodClasses
     * @return Collection|null
     */
    public function getPeriodClasses(): ?Collection
    {
        if (empty($this->periodClasses))
            $this->periodClasses = new ArrayCollection();

        if ($this->periodClasses instanceof PersistentCollection)
            $this->periodClasses->initialize();

        return $this->periodClasses;
    }

    /**
     * @param Collection|null $periodClasses
     * @return TimetablePeriod
     */
    public function setPeriodClasses(?Collection $periodClasses): TimetablePeriod
    {
        $this->periodClasses = $periodClasses;
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
            'timetableDay' => $this->getTimetableDay()->getId(),
            'name' => $this->getName(),
            'abbreviation' => $this->getAbbreviation(),
            'time' => $this->getTimeStartName() . ' - ' . $this->getTimeEndName(),
            'type' => $this->getType(),
            'canDelete' => $this->canDelete(),
            'classes' => strval(intval($this->getPeriodClasses()->count()) + intval(ProviderFactory::create(RollGroup::class)->countForPeriod($this))),
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

    /**
     * canDelete
     *
     * 13/10/2020 15:48
     * @return bool
     */
    public function canDelete(): bool
    {
        return $this->getPeriodClasses()->count() === 0;
    }
}
