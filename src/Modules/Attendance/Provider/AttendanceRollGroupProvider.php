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
 * Date: 17/10/2020
 * Time: 10:31
 */
namespace App\Modules\Attendance\Provider;

use App\Modules\Attendance\Entity\AttendanceRollGroup;
use App\Modules\Attendance\Entity\AttendanceStudent;
use App\Provider\AbstractProvider;
use App\Provider\ProviderFactory;

/**
 * Class AttendanceRollGroupProvider
 *
 * 17/10/2020 10:32
 * @package App\Modules\Attendance\Provider
 * @author Craig Rayner <craig@craigrayner.com>
 */
class AttendanceRollGroupProvider extends AbstractProvider
{
    /**
     * @var string
     */
    protected string $entityName = AttendanceRollGroup::class;

    /**
     * generateAttendanceRollGroup
     *
     * 30/10/2020 10:43
     * @param AttendanceStudent $als
     * @return AttendanceRollGroup
     */
    public function generateAttendanceRollGroup(AttendanceStudent $als): AttendanceRollGroup
    {
        return $this->getRepository()->findOneBy(['rollGroup' => $als->getStudent()->getCurrentEnrolment()->getRollGroup(), 'dailyTime' => $als->getDailyTime(), 'date' => $als->getDate()]) ?: new AttendanceRollGroup($als->getStudent()->getCurrentEnrolment()->getRollGroup(), $als->getDate(), $als->getDailyTime());
    }
}
