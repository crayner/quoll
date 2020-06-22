<?php
/**
 * Created by PhpStorm.
 *
  * Project: Kookaburra
 * Build: Quoll
 *
 * (c) 2018 Craig Rayner <craig@craigrayner.com>
 *
 * User: craig
 * Date: 23/11/2018
 * Time: 15:27
 */
namespace App\Modules\IndividualNeed\Repository;

use App\Modules\IndividualNeed\Entity\INArchive;
use App\Modules\IndividualNeed\Entity\INDescriptor;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Class INArchiveRepository
 * @package App\Modules\IndividualNeed\Repository
 * @author Craig Rayner <craig@craigrayner.com>
 */
class INArchiveRepository extends ServiceEntityRepository
{
    /**
     * ApplicationFormRepository constructor.
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, INArchive::class);
    }

    /**
     * countDescriptor
     * @param INDescriptor $descriptor
     * @return int
     */
    public function countDescriptor(INDescriptor $descriptor): int
    {
        try {
            return intval($this->createQueryBuilder('a')
                ->join('a.descriptors', 'd')
                ->where('d.id = :descriptor')
                ->setParameter('descriptor', $descriptor->getId())
                ->select(['COUNT(a.id)'])
                ->getQuery()
                ->getSingleScalarResult());
        } catch (NoResultException | NonUniqueResultException $e) {
            return 0;
        }
    }
}
