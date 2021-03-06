<?php
/**
 * Created by PhpStorm.
 *
 * Project: Kookaburra
 * Build: Quoll
 *
 * (c) 2019 Craig Rayner <craig@craigrayner.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * User: craig
 * Date: 30/12/2019
 * Time: 10:04
 */

namespace App\Modules\School\Manager\Hidden;

use App\Modules\School\Entity\AcademicYear;
use App\Modules\School\Entity\DaysOfWeek;
use App\Modules\System\Entity\Locale;
use App\Modules\System\Manager\SettingFactory;
use App\Provider\ProviderFactory;
use App\Util\TranslationHelper;
use DateInterval;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * Class CalendarDisplayManager
 * @package App\Modules\School\Manager\Hidden
 * @author Craig Rayner <craig@craigrayner.com>
 */
class CalendarDisplayManager
{
    /**
     * @var Locale
     */
    private $locale;

    /**
     * @var AcademicYear|null
     */
    private $academicYear;

    /**
     * @var int
     */
    private $firstDayofWeek;

    /**
     * @var int
     */
    private $lastDayofWeek;

    /**
     * CalendarDisplayManager constructor.
     * @param string $locale
     */
    public function __construct(string $locale)
    {
        $this->getDaysOfWeek();
        $this->locale = ProviderFactory::getRepository(Locale::class)->findOneByCode($locale) ?: ProviderFactory::getRepository(Locale::class)->findOneByCode('en_GB');
        $this->firstDayofWeek = SettingFactory::getSettingManager()->get('System', 'firstDayOfTheWeek', 1) === 7 ? 7 : 1;
        if ($this->firstDayofWeek === 7)
        {
            $this->lastDayofWeek  = 6;
            $daysOfWeek = $this->getDaysOfWeek()->toArray();
            $sunday = array_pop($daysOfWeek);
            array_unshift($daysOfWeek, $sunday);
            $this->daysOfWeek = new ArrayCollection($daysOfWeek);
        } else {
            $this->lastDayofWeek  = 7;
        }
    }

    /**
     * @return AcademicYear|null
     */
    public function getAcademicYear(): ?AcademicYear
    {
        return $this->academicYear;
    }

    /**
     * AcademicYear.
     *
     * @param AcademicYear|null $academicYear
     * @return CalendarDisplayManager
     */
    public function setAcademicYear(?AcademicYear $academicYear): CalendarDisplayManager
    {
        $this->academicYear = $academicYear;
        return $this;
    }

    /**
     * @return Locale
     */
    public function getLocale(): Locale
    {
        return $this->locale;
    }

    /**
     * @var ArrayCollection
     */
    private ArrayCollection $daysOfWeek;

    /**
     * @var Collection
     */
    private $months;

    /**
     * getDaysOfWeek
     *
     * 30/08/2020 13:48
     * @return ArrayCollection
     */
    public function getDaysOfWeek(): ArrayCollection
    {
        if (!isset($this->daysOfWeek) || $this->daysOfWeek->count() === 0)
            $this->daysOfWeek = new ArrayCollection(ProviderFactory::getRepository(DaysOfWeek::class)->findBy([],['sortOrder' => 'ASC']));
        return $this->daysOfWeek;
    }

    /**
     * createYear
     * @param AcademicYear $year
     * @return CalendarDisplayManager
     */
    public function createYear(AcademicYear $year): CalendarDisplayManager
    {
        $this->setAcademicYear($year);
        $start = clone $this->getAcademicYear()->getFirstDay();
        if ($start->add(new DateInterval('P1Y'))->sub(new DateInterval('P1D'))->format('Y-m-d') !== $this->getAcademicYear()->getLastDay()->format('Y-m-d'))
            trigger_error(sprintf('The School Year MUST be a full calendar year, not "%s" to "%s"',$this->getAcademicYear()->getFirstDay()->format('Y-m-d'),$this->getAcademicYear()->getLastDay()->format('Y-m-d')), E_USER_ERROR);

        $start = clone $this->getAcademicYear()->getFirstDay();
        $week = 1;
        do {
            $day = new Day($start, $week, $this);
            $day->setTermBreak($this->isTermBreak($start));
            $day->setClosed($day->isTermBreak(), $this->addTranslation('Term Break'));
            if ($this->isSpecialDay($day)) {
                $day->setClosed($this->isClosed($day), $this->getClosedPrompt($day));
                $day->setSpecial(true, $this->getSpecialDayPrompt($day));
            }
            $this->addDayToMonth($day);
            $start = clone $start->add(new DateInterval('P1D'));
            if (intval($start->format('N')) === $day->getFirstDayofWeek())
                $week++;
        } while ($start <= $this->getAcademicYear()->getLastDay());
        $this->validateMonths();

        return $this;
    }

    /**
     * isTermBreak
     * @param DateTimeImmutable $date
     * @return bool
     */
    private function isTermBreak(DateTimeImmutable $date): bool
    {
        foreach($this->getAcademicYear()->getTerms() as $term)
            if ($date->format('Y-m-d') >= $term->getFirstDay()->format('Y-m-d') && $date->format('Y-m-d') <= $term->getLastDay()->format('Y-m-d') )
                return false;
        return true;
    }

    /**
     * addTranslation
     * @param string|null $id
     * @param array $parameters
     * @param string $domain
     * @return string
     */
    private function addTranslation(?string $id, array $parameters = [], string $domain = 'School'): string
    {
        if (empty($id))
            return '';
        return TranslationHelper::translate($id, $parameters, $domain);
    }

    /**
     * isSpecialDay
     *
     * @param \DateTime $date
     * @return bool
     */
    private function isSpecialDay(Day $day): bool
    {
        $date = $day->getDate();
        if ($this->getAcademicYear()->hasSpecialDay($date))
            return true;
        return false;
    }

    /**
     * addDay
     *
     * @param Day $day
     * @return CalendarDisplayManager
     */
    public function addDayToMonth(Day $day): CalendarDisplayManager
    {
        $month = $this->getMonth($day->getDate()->format('F')) ?: new Month($day, $this);

        $month->addDay($day);

        return $this->addMonth($month);
    }

    /**
     * @return int
     */
    public function getFirstDayofWeek(): int
    {
        return $this->firstDayofWeek;
    }

    /**
     * @return int
     */
    public function getLastDayofWeek(): int
    {
        return $this->lastDayofWeek;
    }

    /**
     * @return int
     */
    public function getFirstMonth(): int
    {
        return $this->getMonths()->first()->getDays()->first()->getDate()->format('n');
    }

    /**
     * @return Collection
     */
    public function getMonths(): Collection
    {
        if (null === $this->months)
            $this->months = new ArrayCollection();
        return $this->months;
    }

    /**
     * getMonth
     * @return Month|null
     */
    public function getMonth(string $name): ?Month
    {
        if ($this->getMonths()->containsKey($name))
            return $this->months->get($name);
        return null;
    }

    /**
     * addMonth
     * @param Month $month
     * @return CalendarDisplayManager
     */
    public function addMonth(Month $month): CalendarDisplayManager
    {
        if ($this->getMonths()->containsKey($month->getName()))
            return $this;

        $this->months->set($month->getName(), $month);

        return $this;
    }

    /**
     * validateMonths
     */
    private function validateMonths(): CalendarDisplayManager
    {
        foreach($this->getMonths() as $month)
        {
            $month->validateMonth();
        }
        return $this;
    }

    private function isClosed(Day $day): bool
    {
        $date = $day->getDate()->format('Ymd');
        $specialDay = $this->getAcademicYear()->getSpecialDays()->get($date);
        return $specialDay->getType() === 'School Closure';
    }

    /**
     * getClosedPrompt
     * @param Day $day
     * @return string
     */
    private function getClosedPrompt(Day $day): string
    {
        if (!$this->isClosed($day))
            return '';
        $date = $day->getDate();
        $day = $this->getAcademicYear()->getSpecialDays()->get($date->format('Ymd'));
        return $day->getName() ?: '';
    }

    /**
     * getSpecialDayPrompt
     * @param Day $day
     * @return string
     */
    private function getSpecialDayPrompt(Day $day): string
    {
        $date = $day->getDate();
        $day = $this->getAcademicYear()->getSpecialDays()->get($date->format('Ymd'));
        if ($day)
            return $day->getName() ?: '';
        return '';
    }
}