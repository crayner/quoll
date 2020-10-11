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
 * Date: 16/09/2020
 * Time: 16:31
 */
namespace App\Modules\Enrolment\Repository;

use App\Modules\Enrolment\Entity\CourseClass;
use App\Modules\Enrolment\Entity\CourseClassTutor;
use App\Modules\School\Util\AcademicYearHelper;
use App\Modules\Staff\Entity\Staff;
use App\Util\StringHelper;
use App\Util\TranslationHelper;
use DateTimeImmutable;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\ORMInvalidArgumentException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Class CourseClassStaffRepository
 * @package App\Modules\Enrolment\Repository
 * @author Craig Rayner <craig@craigrayner.com>
 */
class CourseClassTutorRepository extends ServiceEntityRepository
{
    /**
     * CourseClassRepository constructor.
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CourseClassTutor::class);
    }

    /**
     * nextSortOrder
     *
     * 16/09/2020 16:36
     * @param CourseClass|null $class
     * @return int
     */
    public function nextSortOrder(?CourseClass $class): int
    {
        if (null === $class) return 1;
        try {
            return intval($this->createQueryBuilder('s')
                    ->orderBy('s.sortOrder', 'DESC')
                    ->where('s.courseClass = :courseClass')
                    ->setParameter('courseClass', $class)
                    ->setMaxResults(1)
                    ->select('s.sortOrder')
                    ->getQuery()
                    ->getSingleScalarResult()) + 1;
        } catch (NoResultException | NonUniqueResultException | ORMInvalidArgumentException $e) {
            return 1;
        }
    }

    /**
     * findIndividualClassEnrolmentContent
     *
     * 17/09/2020 11:20
     * @param Staff $staff
     * @return array
     */
    public function findIndividualClassEnrolmentContent(Staff $staff): array
    {
        return $this->createQueryBuilder('cct')
            ->select(
                [
                    "CONCAT(c.abbreviation,'.',cc.abbreviation) AS classCode",
                    'c.name AS course',
                    'cct.id',
                    'cc.id AS course_class_id',
                    'p.id AS person_id',
                    "CASE WHEN p.status = 'Left' OR (s.dateEnd < '" . date('Y-m-d') . " AND s.dateEnd IS NOT NULL') THEN CONCAT(COALESCE(cct.role, s.type, '-'),' - " . TranslationHelper::translate('person.status.left', [], 'People') . "') ELSE COALESCE(cct.role, s.type, '-') END AS role",
                ]
            )
            ->where('cct.staff = :staff')
            ->setParameter('staff', $staff)
            ->andWhere('c.academicYear = :current')
            ->setParameter('current', AcademicYearHelper::getCurrentAcademicYear())
            ->leftJoin('cct.staff', 's')
            ->leftJoin('s.person', 'p')
            ->leftJoin('cct.courseClass', 'cc')
            ->leftJoin('cc.course', 'c')
            ->orderBy('c.abbreviation', 'ASC')
            ->addOrderBy('cc.abbreviation','ASC')
            ->getQuery()
            ->getResult();
    }
}
