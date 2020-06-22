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
namespace App\Modules\Behaviour\Repository;

use App\Modules\Behaviour\Entity\Behaviour;
use App\Modules\People\Entity\Person;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Class BehaviourRepository
 * @package App\Modules\Behaviour\Repository
 * @author Craig Rayner <craig@craigrayner.com>
 */
class BehaviourRepository extends ServiceEntityRepository
{
    /**
     * BehaviourRepository constructor.
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Behaviour::class);
    }

    /**
     * findNegativeInLast60Days
     * @param Person $person
     * @return array|null
     */
    public function findNegativeInLast60Days(Person $person): array
    {
        try {
            return $this->createQueryBuilder('b')
                ->where('b.person = :person')
                ->andWhere('b.type = :negative')
                ->andWhere('b.date > :date')
                ->setParameter('date', new \DateTime('-60 days'))
                ->setParameter('negative', 'Negative')
                ->setParameter('person', $person)
                ->getQuery()
                ->getResult();
        } catch (\Exception $e) {
            throw $e;
            return [];
        }
    }
}
