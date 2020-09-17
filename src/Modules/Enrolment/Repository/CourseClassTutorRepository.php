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
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
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
        } catch (NoResultException | NonUniqueResultException $e) {
            return 1;
        }
    }
}
