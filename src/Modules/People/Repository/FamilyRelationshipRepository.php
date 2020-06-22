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

use Doctrine\DBAL\Driver\PDOException;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use App\Modules\People\Entity\Family;
use App\Modules\People\Entity\FamilyRelationship;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Modules\People\Entity\Person;

/**
 * Class FamilyRelationshipRepository
 * @package App\Modules\People\Repository
 */
class FamilyRelationshipRepository extends ServiceEntityRepository
{
    /**
     * FamilyRelationshipRepository constructor.
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FamilyRelationship::class);
    }

    /**
     * findOneByFamilyAdultChild
     * @param array $item
     * @return mixed
     */
    public function findOneByFamilyAdultChild(array $item): ?FamilyRelationship
    {
        try {
            return $this->createQueryBuilder('fr')
                ->join('fr.family', 'f')
                ->join('fr.adult', 'a')
                ->join('fr.child', 'c')
                ->where('f.id = :family')
                ->andWhere('a.id = :adult')
                ->andWhere('c.id = :child')
                ->setParameters(['family' => $item['family'], 'adult' => $item['adult'], 'child' => $item['child']])
                ->getQuery()
                ->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            return null;
        }
    }

    /**
     * removeFamilyChild
     * @param Family $family
     * @param Person $child
     * @param array $data
     * @return array
     */
    public function removeFamilyChild(Family $family, Person $child, array $data = []): array
    {
        try {
            foreach ($this->findBy(['family' => $family, 'child' => $child]) as $fc)
                $this->getEntityManager()->remove($fc);
            $this->getEntityManager()->flush();
        } catch (\PDOException | PDOException | ORMException | OptimisticLockException $e) {
            $data['status'] = 'error';
            $data['errors'][] = ['class' => 'error', 'message' => ['return.error.1', [], 'messages']];
        }
        return $data;
    }

    /**
     * removeFamilyAdult
     * @param Family $family
     * @param Person $adult
     * @param array $data
     * @param bool $flush
     * @return array
     */
    public function removeFamilyAdult(Family $family, Person $adult, array $data = [], bool $flush = true): array
    {
        try {
            foreach ($this->findBy(['family' => $family, 'adult' => $adult]) as $fa)
                $this->getEntityManager()->remove($fa);
            if ($flush) $this->getEntityManager()->flush();
        } catch (\PDOException | PDOException | ORMException | OptimisticLockException $e) {
            $data['status'] = 'error';
            $data['errors'][] = ['class' => 'error', 'message' => ['return.error.1', [], 'messages']];
        }
        return $data;
    }

    /**
     * findByFamily
     * @param Family $family
     * @return array|FamilyRelationship[]
     */
    public function findByFamily(Family $family): array
    {
        if (intval($family->getId()) === 0)
            return [];
        return $this->createQueryBuilder('r')
            ->where('r.family = :family')
            ->setParameter('family', $family)
            ->select(['r','a','c','p','p1'])
            ->leftJoin('r.adult', 'a')
            ->leftJoin('r.child', 'c')
            ->leftJoin('c.person', 'p')
            ->leftJoin('a.person', 'p1')
            ->orderBy('a.contactPriority', 'ASC')
            ->addOrderBy('p.surname', 'ASC')
            ->addOrderBy('p.firstName', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
