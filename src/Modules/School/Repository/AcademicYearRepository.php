<?php
/**
 * Created by PhpStorm.
 *
 * Kookaburra
 *
 * (c) 2018 Craig Rayner <craig@craigrayner.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * UserProvider: craig
 * Date: 23/11/2018
 * Time: 11:14
 */
namespace App\Modules\School\Repository;

use App\Modules\School\Entity\AcademicYear;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Class AcademicYearRepository
 * @package App\Modules\School\Repository
 */
class AcademicYearRepository extends ServiceEntityRepository
{
    /**
     * AcademicYearRepository constructor.
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AcademicYear::class);
    }

    /**
     * findAllByOverlap
     * @param AcademicYear $year
     * @return array
     */
    public function findAllByOverlap(AcademicYear $year): array
    {
        return $this->createQueryBuilder('s')
            ->where('s.id <> :year')
            ->setParameter('year', $year->getId())
            ->andwhere('(s.firstDay <= :firstDay AND s.lastDay >= :firstDay) OR (s.firstDay <= :lastDay AND s.lastDay >= :lastDay)')
            ->setParameter('firstDay', $year->getFirstDay())
            ->setParameter('lastDay', $year->getLastDay())
            ->getQuery()
            ->getResult();
    }

    /**
     * selectAcademicYears
     * @param string $status
     * @param string $direction
     * @return array
     */
    public function findByStatus(string $status = 'All', $direction = 'ASC'): array
    {
        $direction = $direction === 'DESC' ? "DESC" : 'ASC';
        $query = $this->createQueryBuilder('s')
            ->select(['s.id', 's.name'])
            ->orderBy('s.firstDay', $direction);
        switch($status) {
            case 'Active':
                return $query
                    ->where('s.status = :current OR s.status = :future')
                    ->setParameters(['current' => 'Current', 'future' => 'Upcoming'])
                    ->getQuery()
                    ->getResult();
                break;
            case 'Upcoming':
                return $query
                    ->where('s.status = :future')
                    ->setParameters(['future' => 'Upcoming'])
                    ->getQuery()
                    ->getResult();
                break;
            case 'Past':
                return $query
                    ->where('s.status = :future')
                    ->setParameters(['future' => 'Past'])
                    ->getQuery()
                    ->getResult();
                break;
        }
        return $query->getQuery()->getResult();
    }

    /**
     * findOneByNext
     * @param AcademicYear $year
     * @return AcademicYear|null
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findOneByNext(AcademicYear $year): ?AcademicYear
    {
        return $this->createQueryBuilder('y')
            ->orderBy('y.firstDay', 'ASC')
            ->setMaxResults(1)
            ->where('y.firstDay > :firstDay')
            ->setParameter('firstDay', $year->getFirstDay())
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * findOneByPrev
     * @param AcademicYear $year
     * @return AcademicYear|null
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findOneByPrev(AcademicYear $year): ?AcademicYear
    {
        return $this->createQueryBuilder('y')
            ->orderBy('y.firstDay', 'DESC')
            ->setMaxResults(1)
            ->where('y.firstDay < :firstDay')
            ->setParameter('firstDay', $year->getFirstDay())
            ->getQuery()
            ->getOneOrNullResult();
    }
}
