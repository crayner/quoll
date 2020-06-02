<?php
/**
 * Created by PhpStorm.
 *
 * Quoll
 * (c) 2020 Craig Rayner <craig@craigrayner.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * User: craig
 * Date: 1/06/2020
 * Time: 11:42
 */
namespace App\Modules\MarkBook\Repository;

use App\Modules\MarkBook\Entity\MarkBookTarget;
use App\Modules\School\Entity\ScaleGrade;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Class MarkBookTargetRepository
 * @package App\Modules\MarkBook\Repository
 * @author Craig Rayner <craig@craigrayner.com>
 */
class MarkBookTargetRepository extends ServiceEntityRepository
{
    /**
     * MarkBookTargetRepository constructor.
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MarkBookTarget::class);
    }

    /**
     * countGradeUse
     * @param ScaleGrade $grade
     * @return int
     */
    public function countGradeUse(ScaleGrade $grade): int
    {
        try {
            return intval($this->createQueryBuilder('t')
                ->where('t.scaleGrade = :grade')
                ->setParameter('grade', $grade)
                ->select(['COUNT(t.id)'])
                ->getQuery()
                ->getSingleScalarResult());
        } catch (NoResultException | NonUniqueResultException $e) {
            return 0;
        }
    }
}