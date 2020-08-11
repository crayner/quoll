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
 * Time: 16:56
 */
namespace App\Modules\Timetable\Entity;

use App\Manager\AbstractEntity;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class timetableDate
 * @package App\Modules\Timetable\Entity
 * @ORM\Entity(repositoryClass="App\Modules\Timetable\Repository\TimetableDateRepository")
 * @ORM\Table(name="TimetableDate",
 *     indexes={@ORM\Index(name="timetable_day", columns={"timetable_day"})})
 */
class TimetableDate extends AbstractEntity
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
     * @ORM\ManyToOne(targetEntity="App\Modules\Timetable\Entity\TimetableDay",inversedBy="timetableDates")
     * @ORM\JoinColumn(name="timetable_day",referencedColumnName="id")
     * @Assert\NotBlank()
     */
    private $timetableDay;

    /**
     * @var DateTimeImmutable|null
     * @ORM\Column(type="date_immutable")
     */
    private $date;

    /**
     * TimetableDayDate constructor.
     * @param TimetableDay|null $timetableDay
     * @param DateTimeImmutable|null $date
     */
    public function __construct(?TimetableDay $timetableDay = null, ?DateTimeImmutable $date = null)
    {
        $this->setTimetableDay($timetableDay)
            ->setDate($date);
    }

    /**
     * @return string|null
     */
    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * @param string|null $id
     * @return TimetableDate
     */
    public function setId(?string $id): TimetableDate
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
     * @return TimetableDate
     */
    public function setTimetableDay(?TimetableDay $timetableDay, bool $reflect = true): TimetableDate
    {
        if ($timetableDay instanceof TimetableDay && $reflect) $timetableDay->addTimetableDayDate($this, false);

        $this->timetableDay = $timetableDay;
        return $this;
    }

    /**
     * @return DateTimeImmutable|null
     */
    public function getDate(): ?DateTimeImmutable
    {
        return $this->date;
    }

    /**
     * @param DateTimeImmutable|null $date
     * @return TimetableDate
     */
    public function setDate(?DateTimeImmutable $date): TimetableDate
    {
        $this->date = $date;
        return $this;
    }

    /**
     * toArray
     * @param string|null $name
     * @return array
     */
    public function toArray(?string $name = null): array
    {
        if ($name === 'mapping') {
            return [
                'date' => $this->getDate()->format('Y-m-d'),
                'timetableDay' => $this->getTimetableDay()->toArray('mapping'),
            ];
        }
        return [];
    }
}
