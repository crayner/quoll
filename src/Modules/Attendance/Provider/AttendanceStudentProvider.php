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

use App\Modules\Attendance\Entity\AttendanceStudent;
use App\Modules\System\Manager\SettingFactory;
use App\Modules\Timetable\Entity\TimetableDate;
use App\Provider\AbstractProvider;
use App\Provider\ProviderFactory;
use App\Util\UrlGeneratorHelper;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class AttendanceStudentProvider
 *
 * 19/10/2020 12:26
 * @package App\Modules\Attendance\Provider
 * @author Craig Rayner <craig@craigrayner.com>
 */
class AttendanceStudentProvider extends AbstractProvider
{
    /**
     * @var string
     */
    protected string $entityName = AttendanceStudent::class;

    /**
     * @var array
     */
    private array $timetableDates;

    /**
     * getPreviousDaysStatus
     *
     * 28/10/2020 12:04
     * @param AttendanceStudent $als
     * @param array $days
     * @return array
     */
    public function getPreviousDaysStatus(AttendanceStudent $als, array $days = []): array
    {
        $resolver = new OptionsResolver();
        $dailyTimes = SettingFactory::getSettingManager()->get('Attendance', 'dailyAttendanceTimes', ['all_day']);
        $resolver->setDefaults(
            [
                'previous' => 5,
                'future' => count($dailyTimes) === 1 ? 0 : 1,
            ]
        );
        // Future includes today, so 1 = today, 2 = today and next school day.
        $resolver->setAllowedTypes('previous', 'integer')
            ->setAllowedTypes('future', 'integer');
        $days = $resolver->resolve($days);

        $result = [];
        $dates = $this->getTimetableDates($als->getDate(), $days);

        $days = $this->getRepository()->findAttendanceDays($als, $dates);
        foreach ($dates as $td) {
            foreach ($dailyTimes as $dailyTime) {
                $found = false;
                foreach ($days as $q=>$w) {
                    if ($w->getDate()->format('Y-m-d') === $td->getDate()->format('Y-m-d') && $dailyTime === $w->getDailyTime()) {
                        $result[$w->getDailyTime()][$w->getDate()->format('Y-m-d')] = ['direction' => $w->getCode()->getDirection()];
                        unset($days[$q]);
                        $found = true;
                    }
                }
                if (!$found) {
                    $result[$dailyTime][$td->getDate()->format('Y-m-d')] = ['direction' => ''];
                }
                $result[$dailyTime][$td->getDate()->format('Y-m-d')]['href'] = UrlGeneratorHelper::getUrl('attendance_by_student', ['dailyTime' => $als->getAttendanceRollGroup()->getDailyTime(), 'date' => $als->getAttendanceRollGroup()->getDate()->format('Y-m-d'), 'student' => $als->getStudent()->getId()]);
            }
        }

        foreach ($result as $q=>$w) {
            ksort($w);
            $result[$q] = $w;
        }


        return $result;
    }

    /**
     * getTimetableDates
     *
     * 28/10/2020 12:08
     * @param DateTimeImmutable $date
     * @param array $days
     * @return array
     */
    public function getTimetableDates(DateTimeImmutable $date, array $days): array
    {
        if (!isset($this->timetableDates)) {
            $this->timetableDates = ProviderFactory::getRepository(TimetableDate::class)->findPreviousTimetableDates($date, $days);
        }
        return $this->timetableDates;
    }

}
