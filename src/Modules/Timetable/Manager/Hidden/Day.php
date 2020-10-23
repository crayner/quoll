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
    private DateTimeImmutable $date;

    /**
     * @var AcademicYearSpecialDay|null
     */
    private ?AcademicYearSpecialDay $specialDay;

    /**
     * @var bool
     */
    private bool $schoolDay;

    /**
     * @var bool
     */
    private bool $schoolOpen;

    /**
     * @var DaysOfWeek|null
     */
    private ?DaysOfWeek $dayOfWeek;

    /**
     * @var TimetableDate|null
     */
    private ?TimetableDate $timetableDate;

    /**
     * Day constructor.
     * @param DateTimeImmutable $date
     */
    public function __construct(DateTimeImmutable $date)
    {
        $this->date = $date;
        $this->isSchoolOpen();
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
        return isset($this->specialDay) ? $this->specialDay : null;
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
        if (!isset($this->schoolDay)) {
            $this->setDayOfWeek(ProviderFactory::getRepository(DaysOfWeek::class)->findOneBySortOrder(intval($this->getDate()->format('N'))));
            $this->setSchoolDay($this->getDayOfWeek()->isSchoolDay());
        }
        return (bool)$this->schoolDay;
    }

    /**
     * @param bool $schoolDay
     * @return Day
     */
    public function setSchoolDay(bool $schoolDay): Day
    {
        $this->schoolDay = $schoolDay;
        return $this;
    }

    /**
     * @return bool
     */
    public function isSchoolOpen(): bool
    {
        if (!isset($this->schoolOpen)) {
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
        return $this->schoolOpen;
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
        if (!isset($this->dayOfWeek)) {
            $this->dayOfWeek = ProviderFactory::getRepository(DaysOfWeek::class)->findOneBySortOrder(intval($this->getDate()->format('N')));
        }
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
        if (!isset($this->timetableDate)) {
            $this->timetableDate = ProviderFactory::getRepository(TimetableDate::class)->findOneByAcademicYearDate($this->getDate());
        }
        return $this->timetableDate;
    }

    /**
     * setTimetableDate
     * @param TimetableDate $timetableDate
     * @return $this
     * 13/08/2020 08:53
     */
    public function setTimetableDate(TimetableDate $timetableDate): Day
    {
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
