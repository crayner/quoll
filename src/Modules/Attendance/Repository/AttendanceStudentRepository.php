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
 * Time: 12:25
 */
namespace App\Modules\Attendance\Repository;

use App\Modules\Attendance\Entity\AttendanceRollGroup;
use App\Modules\Attendance\Entity\AttendanceStudent;
use App\Modules\RollGroup\Entity\RollGroup;
use DateTimeImmutable;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\ORMInvalidArgumentException;
use Doctrine\Persistence\ManagerRegistry;
use Exception;

/**
 * Class AttendanceStudentRepository
 *
 * 19/10/2020 12:25
 * @package App\Modules\Attendance\Repository
 * @author Craig Rayner <craig@craigrayner.com>
 */
class AttendanceStudentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AttendanceStudent::class);
    }

    /**
     * hasDuplicates
     *
     * 7/11/2020 08:54
     * @param AttendanceStudent|null $student
     * @return bool
     */
    public function hasDuplicates(?AttendanceStudent $student): bool
    {
        $query = $this->createQueryBuilder('a')
                ->select('COUNT(a.id)')
                ->where('a.id <> :id')
                ->setParameter('id', $student->getId())
                ->andWhere('a.student = :student')
                ->setParameter('student', $student->getStudent())
                ->andWhere('a.date = :date')
                ->setParameter('date', $student->getDate())
                ->andWhere('a.dailyTime = :time')
                ->setParameter('time', $student->getDailyTime());
        if ($student->getContext() === 'Class') {
            $query
                ->andWhere('a.attendanceClass = :courseClass')
                ->setParameter('courseClass', $student->getAttendanceClass());
        } else {
            $query
                ->andWhere('a.attendanceRollGroup = :rollGroup')
                ->setParameter('rollGroup', $student->getAttendanceRollGroup());
        }
        try {
                return intval($query
                    ->getQuery()
                    ->getSingleScalarResult()) > 0;
        } catch (NoResultException | NonUniqueResultException | ORMInvalidArgumentException $e) {
            return false;
        }
    }

    /**
     * findByRollGroupDateDailyTimeStudent
     *
     * 23/10/2020 12:22
     * @param RollGroup $rollGroup
     * @param DateTimeImmutable $date
     * @param array $studentList
     * @param string $dailyTime
     * @return array
     */
    public function findByRollGroupDateDailyTimeStudent(RollGroup $rollGroup, DateTimeImmutable $date, array $studentList, string $dailyTime = 'all_day'): array
    {
        return $this->createQueryBuilder('a')
            ->select(['s.id AS student','ac.name AS code','a.reason','a.comment','a.id'])
            ->leftJoin('a.student', 's')
            ->leftJoin('a.attendanceRollGroup', 'arg')
            ->leftJoin('a.code', 'ac')
            ->where('s.id in (:studentList)')
            ->setParameter('studentList', $studentList, Connection::PARAM_STR_ARRAY)
            ->andWhere('arg.rollGroup = :rollGroup')
            ->setParameter('rollGroup', $rollGroup)
            ->andWhere('a.date = :date')
            ->setParameter('date', $date)
            ->andWhere('a.dailyTime = :time')
            ->setParameter('time', $dailyTime)
            ->getQuery()
            ->getResult();
    }

    /**
     * countOutByRollGroupDateDailyTimeStudent
     *
     * 23/10/2020 12:21
     * @param RollGroup $rollGroup
     * @param DateTimeImmutable $date
     * @param array $studentList
     * @param string $dailyTime
     * @return array
     */
    public function countOutByRollGroupDateDailyTimeStudent(RollGroup $rollGroup, DateTimeImmutable $date, array $studentList, string $dailyTime = 'all_day'): array
    {
        return $this->createQueryBuilder('a')
            ->select(['s.id AS student',"COALESCE(COUNT(a.id), '0') AS absentCount"])
            ->leftJoin('a.student', 's')
            ->leftJoin('a.attendanceRollGroup', 'arg')
            ->leftJoin('a.code', 'c')
            ->andWhere('s.id IN (:studentList)')
            ->setParameter('studentList', $studentList, Connection::PARAM_STR_ARRAY)
            ->andWhere('arg.rollGroup = :rollGroup')
            ->setParameter('rollGroup', $rollGroup)
            ->andWhere('a.date = :date')
            ->setParameter('date', $date)
            ->andWhere('a.dailyTime = :time')
            ->setParameter('time', $dailyTime)
            ->andWhere('c.direction = :out')
            ->setParameter('out', 'Out')
            ->groupBy('s.id')
            ->getQuery()
            ->getResult();
    }

    /**
     * findOneByStudentDateRollGroupDailyTime
     *
     * 23/10/2020 12:20
     * @param string $id
     * @param string $date
     * @param RollGroup $rollGroup
     * @param string $dailyTime
     * @return AttendanceStudent|null
     */
    public function findOneByStudentDateRollGroupDailyTime(string $id, string $date, RollGroup $rollGroup, string $dailyTime = 'all_day'): ?AttendanceStudent
    {
        try {
            return $this->createQueryBuilder('a')
                ->leftJoin('a.student', 's')
                ->leftJoin('a.attendanceRollGroup', 'arg')
                ->andWhere('s.id = :student')
                ->setParameter('student', $id)
                ->andWhere('arg.rollGroup = :rollGroup')
                ->setParameter('rollGroup', $rollGroup)
                ->andWhere('a.date = :date')
                ->setParameter('date', new DateTimeImmutable($date))
                ->andWhere('a.dailyTime = :dailyTime')
                ->setParameter('dailyTime', $dailyTime)
                ->getQuery()
                ->getOneOrNullResult();
        } catch (NonUniqueResultException | Exception $e) {
            return null;
        }
    }

    /**
     * countStudentAbsences
     *
     * 25/10/2020 09:52
     * @param AttendanceStudent $als
     * @return int
     */
    public function countStudentAbsences(AttendanceStudent $als): int
    {
        try {
            if ($als->getAttendanceRollGroup() instanceof AttendanceRollGroup) {
                return intval($this->createQueryBuilder('a')
                    ->select(['COUNT(a.id)'])
                    ->leftJoin('a.attendanceRollGroup', 'arg')
                    ->leftJoin('a.code', 'c')
                    ->andWhere('a.student = :student')
                    ->setParameter('student', $als->getStudent())
                    ->andWhere('arg.rollGroup = :rollGroup')
                    ->setParameter('rollGroup', $als->getAttendanceRollGroup()->getRollGroup())
                    ->andWhere('c.direction = :out')
                    ->setParameter('out', 'Out')
                    ->getQuery()
                    ->getSingleScalarResult());
            } else {
                return intval($this->createQueryBuilder('a')
                    ->select(['COUNT(a.id)'])
                    ->leftJoin('a.attendanceCourseClass', 'acc')
                    ->leftJoin('a.code', 'c')
                    ->andWhere('a.student = :student')
                    ->setParameter('student', $als->getStudent())
                    ->andWhere('acc.courseClass = :courseClass')
                    ->setParameter('courseClass', $als->getAttendanceCourseClass()->getCourseClass())
                    ->andWhere('c.direction = :out')
                    ->setParameter('out', 'Out')
                    ->getQuery()
                    ->getSingleScalarResult());
            }
        } catch (NoResultException | NonUniqueResultException $e) {
            return 0;
        }
    }

    /**
     * findAttendanceDays
     *
     * 25/10/2020 11:49
     * @param AttendanceStudent $als
     * @param array $dates
     * @return array
     */
    public function findAttendanceDays(AttendanceStudent $als, array $dates): array
    {
        if (empty($dates)) return [];
        $first = reset($dates);
        $last = end($dates);
        $parameters = [
            'student' => $als->getStudent(),
            'rollGroup' => $als->getAttendanceRollGroup()->getRollGroup(),
            'start' => $first->getDate(),
            'last' => $last->getDate(),
        ];

        return $this->createQueryBuilder('a')
            ->leftJoin('a.attendanceRollGroup', 'arg')
            ->leftJoin('a.code', 'c')
            ->andWhere('a.student = :student')
            ->andWhere('arg.rollGroup = :rollGroup')
            ->andWhere('a.date <= :start')
            ->andWhere('a.date >= :last')
            ->setParameters($parameters)
            ->orderBy('a.date', 'DESC')
            ->addOrderBy('a.dailyTime', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
