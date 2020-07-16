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
 * Date: 1/07/2020
 * Time: 15:20
 */
namespace App\Modules\Student\Repository;

use App\Modules\RollGroup\Entity\RollGroup;
use App\Modules\School\Entity\House;
use App\Modules\Student\Entity\Student;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Class StudentRepository
 * @package App\Modules\Student\Repository
 * @author Craig Rayner <craig@craigrayner.com>
 */
class StudentRepository extends ServiceEntityRepository
{
    /**
     * StudentRepository constructor.
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Student::class);
    }

    /**
     * findByRollGroup
     * @param RollGroup $rollGroup
     * @param string $sortBy
     * @return int|mixed|string
     * 16/07/2020 10:00
     */
    public function findByRollGroup(RollGroup $rollGroup, string $sortBy = 'rollOrder')
    {
        $query = $this->createQueryBuilder('s')
            ->select(['s', 'p','se'])
            ->leftJoin('s.person', 'p')
            ->join('s.studentEnrolments', 'se')
            ->where('se.rollGroup = :rollGroup')
            ->andWhere('p.student IS NOT NULL')
            ->setParameter('rollGroup', $rollGroup)
            ->andWhere('p.status = :full')
            ->setParameter('full', 'Full');

        switch (substr($sortBy, 0, 4)) {
            case 'roll':
                $query->orderBy('se.rollOrder', 'ASC')
                    ->addOrderBy('p.surname', 'ASC')
                    ->addOrderBy('p.preferredName', 'ASC');
                break;
            case 'surn':
                $query->orderBy('p.surname', 'ASC')
                    ->addOrderBy('p.preferredName', 'ASC');
                break;
            case 'pref':
                $query->orderBy('p.preferredName', 'ASC')
                    ->addOrderBy('p.surname', 'ASC');
                break;
        }

        return $query->getQuery()
            ->getResult();
    }

    /**
     * countInHouse
     * @param House $house
     * @return int
     * 16/07/2020 10:25
     */
    public function countInHouse(House $house): int
    {
        try {
            return $this->createQueryBuilder('s')
                ->select('COUNT(s.id)')
                ->where('s.house = :house')
                ->setParameter('house', $house)
                ->getQuery()
                ->getSingleScalarResult();
        } catch (NoResultException | NonUniqueResultException $e) {
            return 0;
        }
    }

}
