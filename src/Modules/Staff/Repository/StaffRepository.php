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
namespace App\Modules\Staff\Repository;

use App\Modules\People\Entity\Person;
use App\Modules\School\Entity\House;
use App\Modules\Staff\Entity\Staff;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Class StaffRepository
 * @package App\Modules\Staff\Repository
 */
class StaffRepository extends ServiceEntityRepository
{
    /**
     * ApplicationFormRepository constructor.
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Staff::class);
    }

    /**
     * findOneByPersonOrCreate
     * @param Person $person
     * @return Staff
     * 23/06/2020 10:04
     */
    public function findOneByPersonOrCreate(Person $person): Staff
    {
        return $this->findOneByPerson($person) ?: new Staff($person);
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
