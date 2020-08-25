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
 * Time: 07:30
 */
namespace App\Modules\Timetable\Manager;

use App\Manager\StatusManager;
use App\Modules\School\Entity\AcademicYear;
use App\Modules\School\Entity\AcademicYearTerm;
use App\Modules\School\Entity\DaysOfWeek;
use App\Modules\School\Util\AcademicYearHelper;
use App\Modules\System\Manager\SettingFactory;
use App\Modules\Timetable\Entity\Timetable;
use App\Modules\Timetable\Entity\TimetableDay;
use App\Modules\Timetable\Entity\TimetableDate;
use App\Modules\Timetable\Manager\Hidden\Day;
use App\Modules\Timetable\Manager\Hidden\Term;
use App\Modules\Timetable\Manager\Hidden\Week;
use App\Provider\ProviderFactory;
use App\Util\ErrorMessageHelper;
use App\Util\TranslationHelper;
use DateInterval;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use InvalidArgumentException;

/**
 * Class MappingManager
 * @package App\Modules\Timetable\Manager
 * @author Craig Rayner <craig@craigrayner.com>
 */
class MappingManager
{
    /**
     * @var AcademicYear
     */
    private $academicYear;

    /**
     * @var Timetable|null
     */
    private $timetable;

    /**
     * @var ArrayCollection|DaysOfWeek[]|null
     */
    private $daysOfWeek;

    /**
     * @var ArrayCollection|Term[]|null
     */
    private $terms;

    /**
     * @var DateTimeImmutable|null
     */
    private $academicYearStartDay;

    /**
     * @var integer
     */
    private $weekOffset = 0;

    /**
     * @var ArrayCollection
     */
    private $dayDates;

    /**
     * @var array
     */
    private $timetableDays;

    /**
     * @var StatusManager
     */
    private StatusManager $messages;

    /**
     * MappingManager constructor.
     * @param StatusManager $messages
     */
    public function __construct(StatusManager $messages)
    {
        $this->messages = $messages;
    }

    /**
     * execute
     * @param Timetable|null $timetable
     * 6/08/2020 07:46
     * @return MappingManager
     */
    public function execute(?Timetable $timetable = null): MappingManager
    {
        $this->setTimetable($timetable);

        foreach($this->getAcademicYear()->getTerms() as $academicTerm) {
            $date = clone $academicTerm->getFirstDay();
            while ($date <= $academicTerm->getLastDay()) {
                $this->addDay(clone $date, $academicTerm);
                $date = $date->add(new DateInterval('P1D'));
            }
        }

        if ($this->getDayDates()->count() === 0) {
            $this->createDayDates();
        }

        foreach ($this->getTerms() as $term) {
            $term->setTimetable($this->getTimetable());
            foreach ($term->getWeeks() as $week) {
                foreach ($week->getDays() as $day) {
                    if ($day->isSchoolDay()) {
                        $day->setTimetableDate($this->getDayDates()[$day->getDate()->format('Ymd')], false);
                    }
                }
            }
        }


        return $this;
    }

    /**
     * @return Timetable|null
     */
    public function getTimetable(): ?Timetable
    {
        return $this->timetable;
    }

    /**
     * @param Timetable|null $timetable
     * @return MappingManager
     */
    public function setTimetable(?Timetable $timetable): MappingManager
    {
        $this->timetable = $timetable;

        if ($this->getTimetable() === null) {
            $tt = ProviderFactory::getRepository(Timetable::class)->findByAcademicYear($this->getAcademicYear());
            if (count($tt) === 1) {
                $this->setTimetable($tt[0]);
            }
        }
        return $this;
    }

    /**
     * @return AcademicYear
     */
    public function getAcademicYear(): AcademicYear
    {
        return $this->academicYear = $this->academicYear ?: AcademicYearHelper::getCurrentAcademicYear(true);
    }

    /**
     * addDay
     * @param DateTimeImmutable $date
     * @param AcademicYearTerm $term
     * 6/08/2020 08:25
     */
    private function addDay(DateTimeImmutable $date, AcademicYearTerm $term): MappingManager
    {
        $week = $this->getWeekByDate($date, $this->getTerm($term));
        $day = new Day($date);
        if ($this->getSpecialDays()->containsKey($date->format('Ymd'))) {
            $day->setSpecialDay($this->getSpecialDays()->get($date->format('Ymd')));
        }
        $day->setDayOfWeek($this->getDaysOfWeek()->get($date->format('N')));

        $week->setTerm($term)
            ->addDay($day);
        return $this;
    }

    /**
     * getWeekByDate
     * @param DateTimeImmutable $date
     * @return Week
     * 6/08/2020 08:25
     */
    private function getWeekByDate(DateTimeImmutable $date, Term $term): Week
    {
        $w = $this->getAcademicYearStartDay()->diff($date);
        $wc = $this->weekOffset + intval($w->days / 7) + 1;

        return $this->getWeek($wc, $term);
    }

    /**
     * getAcademicYearStartDay
     * @return DateTimeImmutable|null
     * @throws \Exception
     * 6/08/2020 09:10
     */
    public function getAcademicYearStartDay(): ?DateTimeImmutable
    {
        if ($this->academicYearStartDay === null) {
            $firstDayOfWeek = SettingFactory::getSettingManager()->get('System', 'firstDayOfTheWeek', 1);
            $w = clone $this->getAcademicYear()->getFirstDay();
            while (intval($w->format('N')) !== $firstDayOfWeek) {
                $w = $w->sub(new DateInterval('P1D'));
                $this->weekOffset = -1;
            }
            $this->academicYearStartDay = $w;
        }
        return $this->academicYearStartDay;
    }



    /**
     * @return Week[]|ArrayCollection|null
     */
    public function getTerms()
    {
        return $this->terms = $this->terms ?: new ArrayCollection();
    }

    /**
     * @param Week[]|ArrayCollection|null $Terms
     * @return MappingManager
     */
    public function setTerms($terms)
    {
        $this->terms = $terms;
        return $this;
    }

    /**
     * getWeek
     * @param int $wc
     * @return Week
     * 6/08/2020 08:48
     */
    public function getWeek(int $wc, Term $term): Week
    {
        if ($term->getWeeks()->containsKey($wc)) {
            return $term->getWeeks()->get($wc);
        }
        $week = new Week($wc);
        $this->addWeek($week, $term);
        return $week;
    }

    /**
     * addWeek
     * @param Week $week
     * @param Term $term
     * @return $this
     * 6/08/2020 08:50
     */
    public function addWeek(Week $week, Term $term): MappingManager
    {
        $term->addWeek($week);
        return $this;
    }

    /**
     * getTerm
     * @param AcademicYearTerm $term
     * @return Term
     * 6/08/2020 10:12
     */
    public function getTerm(AcademicYearTerm $term): Term
    {
        if ($this->getTerms()->containsKey($term->getName())) {
            return $this->getTerms()->get($term->getName());
        }
        $term = new Term($term->getName());
        $this->getTerms()->set($term->getName(),$term);
        return $term;
    }

    /**
     * @var ArrayCollection
     */
    private $specialDays;

    /**
     * getSpecialDays
     * @return ArrayCollection
     * 6/08/2020 12:43
     */
    public function getSpecialDays(): ArrayCollection
    {
        if ($this->specialDays === null) {
            $this->specialDays = new ArrayCollection();
            foreach($this->getAcademicYear()->getSpecialDays() as $specialDay)
            {
                $this->specialDays->set($specialDay->getDate()->format('Ymd'),$specialDay);
            }
        }
        return $this->specialDays;
    }

    /**
     * getDayDates
     * @return ArrayCollection
     * 6/08/2020 13:48
     */
    public function getDayDates(): ArrayCollection
    {
        if ($this->dayDates === null) {
            $this->dayDates = new ArrayCollection();
            foreach(ProviderFactory::getRepository(TimetableDate::class)->findByTimetable($this->getTimetable()) as $dayDate) {
                $this->dayDates->set($dayDate->getDate()->format('Ymd'),$dayDate);
            }
        }
        return $this->dayDates;
    }

    /**
     * createDayDates
     * @return ArrayCollection
     * 6/08/2020 13:50
     */
    public function createDayDates(): ArrayCollection
    {
        $this->setTimetableDays();
        $this->dayDates = new ArrayCollection();

        // allocate Timetable days to dates
        foreach ($this->getTerms() as $term) {
            $rotate = new ArrayCollection($this->getTimetableDays('rotate'));
            foreach ($term->getWeeks() as $week) {
                foreach ($week->getDays() as $day) {
                    if ($day->isSchoolDay()) {
                        if (false !== $td = $this->getFixedTimetableDay($day->getDate())) {
                            $tDate = ProviderFactory::getRepository(TimetableDate::class)->createTimetableDate($td, $day->getDate());
                        } else {
                            $td = $this->getRotateTimetableDay($rotate, $day->getDate());
                            $tDate = ProviderFactory::getRepository(TimetableDate::class)->createTimetableDate($td, $day->getDate());
                        }
                        $day->setTimetableDate($tDate);
                        $this->dayDates->set($tDate->getDate()->format('Ymd'),$tDate);
                        ProviderFactory::create(TimetableDate::class)->persistFlush($tDate, [], false);
                    }
                }
            }
        }

        ProviderFactory::create(TimetableDate::class)->flush();

        return $this->dayDates;
    }

    /**
     * getTimetableDays
     * @param string $key
     * @return array
     * 9/08/2020 15:16
     */
    public function getTimetableDays(string $key = ''): array
    {
        if (is_array($this->timetableDays) && key_exists($key, $this->timetableDays)) {
            return $this->timetableDays[$key];
        }
        return $this->timetableDays ?: [];
    }

    /**
     * setTimetableDays
     * @return $this
     * 9/08/2020 15:16
     */
    public function setTimetableDays(): MappingManager
    {
        $this->timetableDays = [];
        $this->timetableDays['fixed'] = [];
        $this->timetableDays['rotate'] = [];

        foreach(ProviderFactory::getRepository(TimetableDay::class)->findBy([],['rotateOrder' => 'ASC']) as $td) {
            if ($td->isFixed()) {
                $this->timetableDays['fixed'][] = $td;
            } else {
                $this->timetableDays['rotate'][] = $td;
            }
        }
        return $this;
    }

    /**
     * @return DaysOfWeek[]|ArrayCollection|null
     */
    public function getDaysOfWeek()
    {
        if ($this->daysOfWeek === null) {
            $this->daysOfWeek = new ArrayCollection();
            foreach(ProviderFactory::getRepository(DaysOfWeek::class)->findBy([],['sortOrder' => 'ASC']) as $dayOfWeek) {
                $this->daysOfWeek->set($dayOfWeek->getSortOrder(), $dayOfWeek);
            }
        }
        return $this->daysOfWeek;
    }

    /**
     * getFixedTimetableDay
     * @param DateTimeImmutable $date
     * @return false|TimetableDay
     * 8/08/2020 08:13
     */
    public function getFixedTimetableDay(DateTimeImmutable $date)
    {
        foreach ($this->getTimetableDays('fixed') as $day) {
            $dow = $day->getDaysofWeek();
            if (intval($date->format('N')) === $dow->first()->getSortOrder()) {
                return $day;
            }
        }

        return false;
    }

    /**
     * getRotateTimetableDay
     * @param ArrayCollection $rotate
     * @param DateTimeImmutable $date
     * @return TimetableDay
     * 8/08/2020 08:43
     */
    public function getRotateTimetableDay(ArrayCollection $rotate, DateTimeImmutable $date): TimetableDay
    {
        for ($i=0; $i<=$rotate->count(); $i++) {
            $td = $rotate->first();
            foreach ($td->getDaysOfWeek() as $dow) {
                if ($dow->getSortOrder() === intval($date->format('N'))) {
                    $rotate->removeElement($td);
                    $rotate->add($td);
                    return $td;
                }
            }
            $rotate->removeElement($td);
            $rotate->add($td);
        }

        throw new InvalidArgumentException(TranslationHelper::translate('Settings are not available to match the day of the week {name}.', ['{name}' => $date->format('l')], 'Timetable'));
    }

    /**
     * getTermByDate
     * @param DateTimeImmutable $date
     * @return Term|null
     * 9/08/2020 12:49
     */
    public function getTermByDate(DateTimeImmutable $date): ?Term
    {
        foreach ($this->getTerms() as $term) {
            if ($date >= $term->getFirstDate() && $date <= $term->getLastDate()) {
                return $term;
            }
        }
        return null;
    }

    /**
     * rippleTermColumns
     *
     * 25/08/2020 09:10
     * @param Term $term
     * @param DateTimeImmutable $date
     */
    public function rippleTermColumns(Term $term, DateTimeImmutable $date)
    {;
        $ripple = false;
        $rotate = new ArrayCollection();
        foreach ($term->getWeeks() as $week) {
            foreach($week->getDays() as $day) {
                if ($day->getTimetableDate() instanceof TimetableDate) {
                    if ($ripple) {
                        if ($day->getTimetableDate()->getTimetableDay()->isFixed()) continue;

                        $this->nextTimetableDay($rotate, $day);
                        if (!$this->getMessages()->isStatusSuccess()) return;
                    }
                    if ($day->getDate()->format('Ymd') === $date->format('Ymd')) {
                        if ($day->getTimetableDate()->getTimetableDay()->isFixed()) {
                            $this->getMessages()->warning('No work was done as you asked to ripple a fixed day.',[],'Timetable');
                            return;
                        }
                        $ripple = true;
                        $rotate = new ArrayCollection($this->setTimetableDays()->getTimetableDays('rotate'));
                        while ($day->getTimetableDate()->getTimetableDay() !== ($td = $rotate->first())) {
                            $rotate->removeElement($td);  // first shall be last
                            $rotate->add($td);
                        }
                        $rotate->removeElement($td);  // first shall be last
                        $rotate->add($td);
                        $data = $this->nextTimetableDay($rotate, $day);
                        if (!$this->getMessages()->isStatusSuccess()) return;
                    }
                }
            }
        }

        if ($ripple) {
            ProviderFactory::create(TimetableDate::class)->flush();
        }
    }

    /**
     * nextTimetableDay
     *
     * 25/08/2020 09:12
     * @param ArrayCollection $rotate
     * @param Day $day
     */
    protected function nextTimetableDay(ArrayCollection $rotate, Day $day)
    {
        $found = false;
        $tDate = $day->getTimetableDate();
        do {
            $td = $rotate->first();
            foreach ($td->getDaysOfWeek() as $dow) {
                if (intval($tDate->getDate()->format('N')) === $dow->getSortOrder()) {
                    $tDate->setTimetableDay($td);
                    $found = true;
                    $rotate->removeElement($td);
                    $rotate->add($td);
                    break;
                }
            }
            if (!$found) {
                $rotate->removeElement($td);
                $rotate->add($td);
            }
        } while (!$found);

        ProviderFactory::create(TimetableDate::class)->persistFlush($tDate, false);
    }

    /**
     * getMessages
     *
     * 25/08/2020 09:04
     * @return StatusManager
     */
    public function getMessages(): StatusManager
    {
        return $this->messages;
    }

}
