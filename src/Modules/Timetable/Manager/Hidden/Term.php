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
 * Time: 10:01
 */
namespace App\Modules\Timetable\Manager\Hidden;

use App\Modules\System\Manager\SettingFactory;
use App\Modules\System\Util\LocaleHelper;
use App\Modules\Timetable\Entity\Timetable;
use App\Util\TranslationHelper;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Class Term
 * @package App\Modules\Timetable\Manager\Hidden
 * @author Craig Rayner <craig@craigrayner.com>
 */
class Term
{
    /**
     * @var ArrayCollection|Week[]|null
     */
    private $weeks;

    /**
     * @var string
     */
    private $name;

    /**
     * @var Timetable
     */
    private $timetable;

    /**
     * Term constructor.
     * @param string $name
     */
    public function __construct(string $name)
    {
        $this->name = $name;
    }

    /**
     * @return Week[]|ArrayCollection|null
     */
    public function getWeeks()
    {
        return $this->weeks = $this->weeks ?: new ArrayCollection();
    }

    /**
     * @param Week[]|ArrayCollection|null $weeks
     * @return Term
     */
    public function setWeeks($weeks): Term
    {
        $this->weeks = $weeks;
        return $this;
    }

    public function addWeek(Week $week): Term
    {
        $this->getWeeks()->set($week->getNumber(), $week);
        return $this;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return Term
     */
    public function setName(string $name): Term
    {
        $this->name = $name;
        return $this;
    }

    public function toArray(): array
    {
        TranslationHelper::addTranslation('Monday', [], 'System');
        TranslationHelper::addTranslation('Tuesday', [], 'System');
        TranslationHelper::addTranslation('Wednesday', [], 'System');
        TranslationHelper::addTranslation('Thursday', [], 'System');
        TranslationHelper::addTranslation('Friday', [], 'System');
        TranslationHelper::addTranslation('Saturday', [], 'System');
        TranslationHelper::addTranslation('Sunday', [], 'System');
        TranslationHelper::addTranslation('Week Number', [], 'Timetable');
        TranslationHelper::addTranslation('School Day', [], 'Timetable');
        TranslationHelper::addTranslation('Next Column', [], 'Timetable');
        TranslationHelper::addTranslation('Ripple Columns in Term', [], 'Timetable');
        TranslationHelper::addTranslation('Close Message', [], 'messages');
        TranslationHelper::addTranslation('Let me ponder your request', [], 'messages');
        return [
            'name' => 'timetable_calendar_map',
            'weeks' => $this->getWeeksArray(),
            'firstDayOfTheWeek' => SettingFactory::getSettingManager()->get('System', 'firstDayOfTheWeek'),
            'days' => [
                1 => 'Monday',
                2 => 'Tuesday',
                3 => 'Wednesday',
                4 => 'Thursday',
                5 => 'Friday',
                6 => 'Saturday',
                7 => 'Sunday',
                0 => 'Sunday',
            ],
            'locale' => LocaleHelper::getLocale(),
            'timetable' => $this->getTimetable()->getId(),
        ];
    }

    public function getWeeksArray(): array
    {
        $weeks = [];
        foreach ($this->getWeeks() as $week) {
            $weeks[] = $week->toArray();
        }
        return $weeks;
    }

    /**
     * @return Timetable
     */
    public function getTimetable(): Timetable
    {
        return $this->timetable;
    }

    /**
     * @param Timetable $timetable
     * @return Term
     */
    public function setTimetable(Timetable $timetable): Term
    {
        $this->timetable = $timetable;
        return $this;
    }

    /**
     * getFirstDate
     * @return DateTimeImmutable
     * 9/08/2020 12:48
     */
    public function getFirstDate(): DateTimeImmutable
    {
        return $this->getWeeks()->first()->getDays()->first()->getDate();
    }

    /**
     * getFirstDate
     * @return DateTimeImmutable
     * 9/08/2020 12:48
     */
    public function getLastDate(): DateTimeImmutable
    {
        return $this->getWeeks()->last()->getDays()->last()->getDate();
    }
}
