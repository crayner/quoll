<?php
/**
 * Created by PhpStorm.
 *
 * kookaburra
 * (c) 2019 Craig Rayner <craig@craigrayner.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * User: craig
 * Date: 31/12/2019
 * Time: 08:00
 */
namespace App\Modules\School\Manager\Hidden;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * Class Month
 * @package App\Modules\School\Manager\Hidden
 * @author Craig Rayner <craig@craigrayner.com>
 */
class Month
{
    /**
     * @var ArrayCollection|Day[]
     */
    private $days;

    /**
     * @var string
     */
    private $name;

    /**
     * @var int
     */
    private $daysInMonth;

    /**
     * @var CalendarDisplayManager
     */
    private $manager;

    /**
     * Month constructor.
     * @param Day $day
     * @param CalendarDisplayManager $manager
     */
    public function __construct(Day $day, CalendarDisplayManager $manager)
    {
        $this->setName($day->getDate())->addDay($day);
        $this->manager = $manager;
    }

    /**
     * getDays
     * @return ArrayCollection|Day[]
     */
    public function getDays()
    {
        if (null === $this->days)
            $this->days = new ArrayCollection();

        return $this->days;
    }

    /**
     * addDay
     * @param Day $day
     * @return Month
     */
    public function addDay(Day $day): Month
    {
        if ($this->getDays()->containsKey($day->getName()))
            return $this;

        $this->days->set($day->getName(), $day);

        return $this;
    }

    /**
     * isMonthValid
     * @return bool
     */
    public function isMonthValid(): bool
    {
        if ($this->getDays()->count() !== 35)
            return false;
        return true;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * setName
     * @param \DateTimeImmutable $name
     * @return Month
     */
    public function setName(\DateTimeImmutable $name): Month
    {
        $this->setDaysInMonth($name);
        $this->name = $name->format('F');
        return $this;
    }

    /**
     * @return int
     */
    public function getDaysInMonth(): int
    {
        return $this->daysInMonth;
    }

    /**
     * DaysInMonth.
     *
     * @param int $daysInMonth
     * @return Month
     */
    public function setDaysInMonth(\DateTimeImmutable $daysInMonth): Month
    {
        $this->daysInMonth = cal_days_in_month(CAL_GREGORIAN, $daysInMonth->format('m'), $daysInMonth->format('Y'));
        return $this;
    }

    /**
     * validateMonth
     * @return Month
     * @throws \Exception
     */
    public function validateMonth(): Month
    {
        if ($this->isMonthValid())
            return $this;

        $firstDay = intval($this->getDays()->first()->getDate()->format('N'));

        $days = new ArrayCollection();

        if ($firstDay === 7 && $this->getManager()->getFirstDayofWeek() === 7)
            $firstDay = 0;

        for($i = $this->getManager()->getFirstDayofWeek() === 7 ? 0 : 1; $i<$firstDay; $i++)
        {
            $day = new Day(null, null, $this->getManager());
            $days->add($day);
        }
        $offset = 0;
        foreach($this->getDays() as $day)
        {
            if ($days->count() === 35)
            {
                $days->set($offset++, $day);
            } else {
                $days->add($day);
            }
        }

        while ($days->count() < 35)
            $days->add(new Day(null, null, $this->getManager()));

        $this->days = $days;

        return $this;
    }

    /**
     * @return CalendarDisplayManager
     */
    public function getManager(): CalendarDisplayManager
    {
        return $this->manager;
    }
}