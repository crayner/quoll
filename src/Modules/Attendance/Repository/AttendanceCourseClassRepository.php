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
 * Time: 12:54
 */
namespace App\Modules\Attendance\Repository;

use App\Modules\Attendance\Entity\AttendanceCourseClass;
use App\Modules\Attendance\Entity\AttendanceRecorderLog;
use App\Modules\Enrolment\Entity\CourseClass;
use App\Modules\People\Manager\PersonNameManager;
use App\Util\TranslationHelper;
use DateTimeImmutable;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Class AttendanceRecorderLogClassRepository
 *
 * 19/10/2020 12:54
 * @package App\Modules\Attendance\Repository
 * @author Craig Rayner <craig@craigrayner.com>
 */
class AttendanceCourseClassRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AttendanceCourseClass::class);
    }

    /**
     * findByCourseClassDateHistory
     *
     * 19/11/2020 09:48
     * @param CourseClass $courseCLass
     * @param DateTimeImmutable $date
     * @return array
     */
    public function findByCourseClassDateHistory(CourseClass $courseCLass, DateTimeImmutable $date): array
    {
        return $this->createQueryBuilder('acc')
            ->select(
                [
                    "CONCAT(".PersonNameManager::formatNameQuery('p', 'Staff','Formal').") AS recorder",
                    'arl.recordedOn',
                    "COALESCE(tp.name, 'no_period') as period",
                ]
            )
            ->leftJoin('acc.periodClass', 'tpc')
            ->leftJoin(AttendanceRecorderLog::class, 'arl', 'WITH', 'acc.id = arl.logId AND arl.context = :class')
            ->leftJoin('arl.recorder', 's')
            ->leftJoin('s.person', 'p')
            ->leftJoin('tpc.period', 'tp')
            ->where('acc.courseClass = :course_class')
            ->andWhere('acc.date = :date')
            ->andWhere('arl.id IS NOT NULL')
            ->setParameter('class', 'Class')
            ->setParameter('course_class', $courseCLass)
            ->setParameter('date', $date)
            ->orderBy('arl.recordedOn', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
