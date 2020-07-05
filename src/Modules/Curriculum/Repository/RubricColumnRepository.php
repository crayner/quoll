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
 * Date: 1/06/2020
 * Time: 13:07
 */
namespace App\Modules\Curriculum\Repository;

use App\Modules\Curriculum\Entity\RubricColumn;
use App\Modules\School\Entity\ScaleGrade;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NoResultException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Class RubricColumnRepository
 * @package App\Modules\Curriculum\Repository
 * @author Craig Rayner <craig@craigrayner.com>
 */
class RubricColumnRepository extends ServiceEntityRepository
{
    /**
     * RubricColumnRepository constructor.
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RubricColumn::class);
    }

    /**
     * countGradeUse
     * @param ScaleGrade $grade
     * @return int
     */
    public function countGradeUse(ScaleGrade $grade): int
    {
        try {
            return intval($this->createQueryBuilder('c')
                ->where('c.scaleGrade = :grade')
                ->setParameter('grade', $grade)
                ->select(['COUNT(c.id)'])
                ->getQuery()
                ->getSingleScalarResult());
        } catch (NoResultException | NonUniqueResultException $e) {
            return 0;
        }
    }
}