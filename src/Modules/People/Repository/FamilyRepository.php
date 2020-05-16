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
namespace App\Modules\People\Repository;

use App\Modules\People\Entity\Person;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use App\Modules\People\Entity\District;
use App\Modules\People\Entity\Family;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

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
    public function getPaginationContent(): array
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

    /**
     * getFamiliesOfPerson
     * @param Person $person
     * @return array
     */
    public function getFamiliesOfPerson(Person $person): array
    {
        return $this->createQueryBuilder('f')
            ->leftJoin('f.adults', 'a')
            ->leftJoin('f.children', 'c')
            ->where('(a.person = :person OR c.person = :person)')
            ->setParameter('person', $person)
            ->getQuery()
            ->getResult();
    }
}
