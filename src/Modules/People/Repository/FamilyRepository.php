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
namespace App\Modules\People\Repository;

use App\Modules\People\Entity\Address;
use App\Modules\People\Entity\Locality;
use App\Modules\People\Entity\Person;
use App\Modules\People\Entity\Phone;
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
        return $this->createQueryBuilder('f','f.id')
            ->select(['f.id','f.name','f.status'])
            ->orderBy('f.name')
            ->getQuery()
            ->getResult();
    }

    /**
     * countAddressUsa
     * @param Address $address
     * @return int
     */
    public function countAddressUse(Address $address): int
    {
        try {
            return $this->createQueryBuilder('f')
                ->select('COUNT(f.id)')
                ->where('f.physicalAddress = :address')
                ->orWhere('f.postalAddress = :address')
                ->setParameter('address', $address)
                ->getQuery()
                ->getSingleScalarResult();
        } catch (NoResultException | NonUniqueResultException $e) {
            return 0;
        }
    }

    /**
     * countLocalityUse
     * @param Locality $locality
     * @return int
     */
    public function countLocalityUse(Locality $locality): int
    {
        try {
            return $this->createQueryBuilder('f')
                ->select('COUNT(f.id)')
                ->leftJoin('f.physicalAddress', 'a')
                ->leftJoin('f.postalAddress', 'pa')
                ->where('a.locality = :locality')
                ->orWhere('pa.locality = :locality')
                ->setParameter('locality', $locality)
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

    /**
     * findPhoneList
     * @return array
     */
    public function findPhoneList(): array
    {
        return $this->createQueryBuilder('f', )
            ->select(['f.id as family','p.id'])
            ->where('p.id IS NOT NULL')
            ->leftJoin('f.familyPhones', 'p')
            ->getQuery()
            ->getResult();
    }
}
