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

use App\Util\ErrorMessageHelper;
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
     * findOneByFamilyCareGiverStudent
     * @param array $item
     * @return mixed
     */
    public function findOneByFamilyCareGiverStudent(array $item): ?FamilyRelationship
    {
        try {
            return $this->createQueryBuilder('fr')
                ->join('fr.family', 'f')
                ->join('fr.careGiver', 'a')
                ->join('fr.student', 'c')
                ->where('f.id = :family')
                ->andWhere('a.id = :careGiver')
                ->andWhere('c.id = :student')
                ->setParameters(['family' => $item['family'], 'careGiver' => $item['careGiver'], 'student' => $item['student']])
                ->getQuery()
                ->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            return null;
        }
    }

    /**
     * findByFamily
     * @param Family $family
     * @return array|FamilyRelationship[]
     */
    public function findByFamily(Family $family): array
    {
        return $this->createQueryBuilder('r')
            ->where('r.family = :family')
            ->setParameter('family', $family)
            ->select(['r','fmcg','cg','fms','s','p','p1'])
            ->leftJoin('r.careGiver', 'fmcg')
            ->leftJoin('r.student', 'fms')
            ->leftJoin('fms.student', 's')
            ->leftJoin('fmcg.careGiver', 'cg')
            ->leftJoin('s.person', 'p')
            ->leftJoin('cg.person', 'p1')
            ->orderBy('fmcg.contactPriority', 'ASC')
            ->addOrderBy('p.surname', 'ASC')
            ->addOrderBy('p.firstName', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
