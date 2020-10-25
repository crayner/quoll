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
 * Date: 19/10/2020
 * Time: 12:26
 */
namespace App\Modules\Attendance\Provider;

use App\Modules\Attendance\Entity\AttendanceLogStudent;
use App\Modules\System\Manager\SettingFactory;
use App\Modules\Timetable\Entity\TimetableDate;
use App\Provider\AbstractProvider;
use App\Provider\ProviderFactory;

/**
 * Class AttendanceLogStudentProvider
 *
 * 19/10/2020 12:26
 * @package App\Modules\Attendance\Provider
 * @author Craig Rayner <craig@craigrayner.com>
 */
class AttendanceLogStudentProvider extends AbstractProvider
{
    /**
     * @var string
     */
    protected string $entityName = AttendanceLogStudent::class;

    /**
     * getPreviousDaysStatus
     *
     * 25/10/2020 11:52
     * @param AttendanceLogStudent $als
     * @return array
     */
    public function getPreviousDaysStatus(AttendanceLogStudent $als): array
    {
        $count = 5 * count(SettingFactory::getSettingManager()->get('Attendance', 'dailyAttendanceTimes', ['all_day']));
        $result = [];
        $days = $this->getRepository()->findPreviousDays($als, $count);
        foreach (ProviderFactory::getRepository(TimetableDate::class)->findPreviousTimetableDates($als->getDate()) as $td) {
            foreach (SettingFactory::getSettingManager()->get('Attendance', 'dailyAttendanceTimes', ['all_day']) as $dailyTime) {
                $found = false;
                foreach ($days as $q=>$w) {
                    if ($w->getDate()->format('Y-m-d') === $td->getDate()->format('Y-m-d') && $dailyTime === $w->getDailyTime()) {
                        $result[$w->getDailyTime()][$w->getDate()->format('d M')] =  $w->getCode()->getDirection();
                        unset($days[$q]);
                        $found = true;
                    }
                }
                if (!$found) {
                    $result[$dailyTime][$td->getDate()->format('d M')] = '';
                }
            }
        }

        foreach ($result as $q=>$w) {
            ksort($w);
            $result[$q] = $w;
        }

        return $result;
    }
}
