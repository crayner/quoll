<?php
/**
 * Created by PhpStorm.
 *
 * Kookaburra
 *
 * (c) 2018 Craig Rayner <craig@craigrayner.com>
 *
 * User: craig
 * Date: 23/11/2018
 * Time: 15:27
 */
namespace App\Modules\People\Repository;

use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use App\Modules\People\Entity\District;
use App\Modules\People\Entity\Family;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Modules\People\Form\Entity\ManageSearch;

/**
 * Class FamilyRepository
 * @package App\Modules\People\Repository
 */
class FamilyRepository extends ServiceEntityRepository
{
    /**
     * FamilyRepository constructor.
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Family::class);
    }

    /**
     * findBySearch
     * @return array
     */
    public function findBySearch(): array
    {
        return $this->createQueryBuilder('f')
            ->select(['f.id','f.name','f.status'])
            ->orderBy('f.name')
            ->getQuery()
            ->getResult();
    }

    /**
     * countDistrictUsage
     * @param District $district
     * @return int
     */
    public function countDistrictUsage(District $district): int
    {
        try {
            return $this->createQueryBuilder('f')
                ->select('COUNT(f.id)')
                ->where('f.homeAddressDistrict = :district')
                ->setParameter('district', $district)
                ->getQuery()
                ->getSingleScalarResult();
        } catch (NoResultException | NonUniqueResultException $e) {
            return 0;
        }
    }
}
