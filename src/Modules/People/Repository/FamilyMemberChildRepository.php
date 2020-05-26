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

use App\Modules\People\Entity\FamilyMemberChild;
use App\Modules\School\Util\AcademicYearHelper;
use App\Util\TranslationHelper;
use Doctrine\DBAL\Connection;
use App\Modules\People\Entity\Family;
use App\Modules\People\Entity\Person;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Class FamilyMemberChildRepository
 * @package App\Modules\People\Repository
 */
class FamilyMemberChildRepository extends ServiceEntityRepository
{
    /**
     * FamilyChildRepository constructor.
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FamilyMemberChild::class);
    }

    /**
     * getChildrenFromParent
     * @param Person $person
     * @return array
     */
    public function findByParent(Person $person): array
    {
        $result = $this->createQueryBuilder('fc')
            ->leftJoin('fc.family', 'f')
            ->leftJoin('f.adults', 'fa')
            ->where('fa.person = :person')
            ->setParameter('person', $person)
            ->getQuery()
            ->getResult();

        $children = [];
        foreach($result as $child)
            $children[] = $child->getPerson()->getId();

        return $children;
    }

    /**
     * findByFamily
     * @param Family|integer $family
     * @return array
     */
    public function findByFamily($family, bool $asArray = false): array
    {
        $query = $this->createQueryBuilder('c')
            ->join('c.family', 'f')
            ->where('f.id = :family')
            ->setParameter('family', $family instanceof Family ? $family->getId() : $family)
            ->leftJoin('c.person', 'p')
            ->orderBy('p.surname', 'ASC')
            ->addOrderBy('p.firstName', 'ASC');

        if ($asArray)
            return $query->select(['p.title','p.surname','p.firstName AS first','p.preferredName AS preferred','p.image_240 AS photo','p.status','c.id AS child_id','c.comment','f.id AS family_id','p.id AS person','c.id'])
                ->getQuery()
                ->getResult();
        return $query->select(['p', 'c'])
            ->getQuery()
            ->getResult();
    }

    /**
     * findByFamilyList
     * @param array $familyList
     * @return array
     */
    public function findByFamilyList(array $familyList): array
    {
        return $this->createQueryBuilder('c')
            ->join('c.family', 'f')
            ->where('f.id in (:family)')
            ->setParameter('family', $familyList, Connection::PARAM_INT_ARRAY)
            ->leftJoin('c.person', 'p')
            ->orderBy('f.id', 'ASC')
            ->addOrderBy('p.surname', 'ASC')
            ->addOrderBy('p.firstName', 'ASC')
            ->select(['p.title','p.firstName AS first', 'p.preferredName AS preferred', 'p.surname', 'f.id AS id'])
            ->getQuery()
            ->getResult();
    }

    /**
     * findAllStudentsByRollGroup
     * @return mixed
     */
    public function findCurrentStudentsAsArray()
    {
        return $this->createQueryBuilder('m')
            ->select(['p.id', "CONCAT(p.surname, ', ', p.preferredName) AS fullName", "'".TranslationHelper::translate('Student', [], 'People')."' AS type", 'p.image_240 AS photo'])
            ->join('m.person', 'p')
            ->where('p.status = :full')
            ->setParameter('full', 'Full')
            ->addOrderBy('p.surname', 'ASC')
            ->addOrderBy('p.preferredName', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
