<?php
/**
 * Created by PhpStorm.
 *
 * Project: Kookaburra
 * Build: Quoll
 *
 * (c) 2020 Craig Rayner <craig@craigrayner.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * User: craig
 * Date: 6/08/2020
 * Time: 07:41
 */
namespace App\Modules\Timetable\Manager\Hidden;

use App\Modules\School\Entity\AcademicYearSpecialDay;
use App\Modules\School\Entity\DaysOfWeek;
use App\Modules\Timetable\Entity\TimetableDate;
use App\Provider\ProviderFactory;
use DateTimeImmutable;

/**
 * Class Day
 * @package App\Modules\Timetable\Manager\Hidden
 * @author Craig Rayner <craig@craigrayner.com>
 */
class Day
{
    /**
     * @var DateTimeImmutable
     */
    private $date;

    /**
     * @var AcademicYearSpecialDay|null
     */
    private $specialDay;

    /**
     * @var bool
     */
    private $schoolDay;

    /**
     * @var bool
     */
    private $schoolOpen;

    /**
     * @var DaysOfWeek
     */
    private $dayOfWeek;

    /**
     * @var TimetableDate
     */
    private $timetableDate;

    /**
     * Day constructor.
     * @param DateTimeImmutable $date
     */
    public function __construct(DateTimeImmutable $date)
    {
        $this->date = $date;
    }

    /**
     * @return DateTimeImmutable|null
     */
    public function getDate(): ?DateTimeImmutable
    {
        return $this->date;
    }

    /**
     * @param DateTimeImmutable $date
     * @return Day
     */
    public function setDate(DateTimeImmutable $date): Day
    {
        $this->date = $date;
        return $this;
    }

    /**
     * @return AcademicYearSpecialDay|null
     */
    public function getSpecialDay(): ?AcademicYearSpecialDay
    {
        return $this->specialDay;
    }

    /**
     * @param AcademicYearSpecialDay|null $specialDay
     * @return Day
     */
    public function setSpecialDay(?AcademicYearSpecialDay $specialDay): Day
    {
        $this->specialDay = $specialDay;
        return $this;
    }

    /**
     * @return bool
     */
    public function isSchoolDay(): bool
    {
        if (null === $this->schoolDay) {
            $dow = ProviderFactory::getRepository(DaysOfWeek::class)->findOneBySortOrder(intval($this->getDate()->format('N')));
            $this->schoolDay = $dow->isSchoolDay();
        }
        return (bool)$this->schoolDay;
    }

    /**
     * @param bool $schoolDay
     * @return Day
     */
    public function setSchoolDay(bool $schoolDay): Day
    {
        $this->schoolDay = (bool)$schoolDay;
        return $this;
    }

    /**
     * @return bool
     */
    public function isSchoolOpen(): bool
    {
        if ($this->schoolOpen === null) {
            if ($this->isSchoolDay()) {
                if ($this->getSpecialDay() !== null) {
                    if ($this->getSpecialDay()->getType() === 'School Closure') {
                        $this->setSchoolOpen(false);
                    }
                } else {
                    $this->setSchoolOpen($this->getTimetableDate() instanceof TimetableDate);
                }

            } else {
                $this->setSchoolOpen(false);
            }
        }
        return (bool)$this->schoolOpen;
    }

    /**
     * @param bool $schoolOpen
     * @return Day
     */
    public function setSchoolOpen(bool $schoolOpen): Day
    {
        $this->schoolOpen = (bool)$schoolOpen;
        return $this;
    }

    /**
     * @return DaysOfWeek
     */
    public function getDayOfWeek(): DaysOfWeek
    {
        return $this->dayOfWeek;
    }

    /**
     * @param DaysOfWeek $dayOfWeek
     * @return Day
     */
    public function setDayOfWeek(DaysOfWeek $dayOfWeek): Day
    {
        $this->dayOfWeek = $dayOfWeek;
        return $this;
    }

    /**
     * @return TimetableDate|null
     */
    public function getTimetableDate(): ?TimetableDate
    {
        return $this->timetableDate;
    }

    /**
     * setTimetableDate
     * @param TimetableDate $timetableDate
     * @param bool $reflect
     * @return $this
     * 11/08/2020 15:59
     */
    public function setTimetableDate(TimetableDate $timetableDate, bool $reflect = true): Day
    {
        if ($reflect) $timetableDate->getTimetableDay()->addTimetableDate($timetableDate);
        $this->timetableDate = $timetableDate;
        return $this;
    }

    /**
     * toArray
     * @return array
     * 8/08/2020 10:39
     */
    public function toArray(): array
    {
        return [
            'date' => $this->getDate()->format('Y-m-d'),
            'schoolDay' => $this->isSchoolDay(),
            'schoolOpen' => $this->isSchoolOpen(),
            'dayOfWeek' => $this->getDayOfWeek()->getSortOrder(),
            'specialDay' => $this->getSpecialDay() ? $this->getSpecialDay()->toArray('mapping') : null,
            'dayDate' => $this->isSchoolOpen() ? $this->getTimetableDate()->toArray('mapping') : null,
            'rotate' => $this->isSchoolOpen() ? $this->getTimetableDate()->getTimetableDay()->getDaysOfWeek()->count() > 1 : false,
        ];
    }
}
