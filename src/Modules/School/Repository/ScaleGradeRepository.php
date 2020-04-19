<?php
/**
 * Created by PhpStorm.
 *
* Quoll
 *
 * (c) 2018 Craig Rayner <craig@craigrayner.com>
 *
 * User: craig
 * Date: 23/11/2018
 * Time: 15:27
 */
namespace App\Modules\School\Repository;

use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use App\Modules\School\Entity\Scale;
use App\Modules\School\Entity\ScaleGrade;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Class ScaleGradeRepository
 * @package App\Modules\School\Repository
 */
class ScaleGradeRepository extends ServiceEntityRepository
{
    /**
     * ApplicationFormRepository constructor.
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ScaleGrade::class);
    }

    /**
     * countScaleUse
     * @param Scale $scale
     * @return int
     */
    public function countScaleUse(Scale $scale): int
    {
        try {
            return intval($this->createQueryBuilder('g')
                ->select(['COUNT(g.id)'])
                ->where('g.scale = :scale')
                ->setParameter('scale', $scale)
                ->getQuery()
                ->getSingleScalarResult());
        } catch (NoResultException | NonUniqueResultException $e) {
            return 0;
        }
    }
}
