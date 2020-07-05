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

use App\Modules\IndividualNeed\Entity\INDescriptor;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Class INDescriptorRepository
 * @package App\Modules\IndividualNeed\Repository
 * @author Craig Rayner <craig@craigrayner.com>
 */
class INDescriptorRepository extends ServiceEntityRepository
{
    /**
     * ApplicationFormRepository constructor.
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, INDescriptor::class);
    }

    /**
     * nextSortOrder
     * @return int
     * 10/06/2020 10:13
     */
    public function nextSortOrder(): int
    {
        try {
            return intval($this->createQueryBuilder('d')
                    ->select(['d.sortOrder'])
                    ->setMaxResults(1)
                    ->orderBy('d.sortOrder', 'DESC')
                    ->getQuery()
                    ->getSingleScalarResult()) + 1;
        } catch (NoResultException | NonUniqueResultException $e) {
            return 1;
        }

    }

}
