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

use App\Modules\People\Entity\FamilyMemberStudent;
use App\Modules\School\Util\AcademicYearHelper;
use Doctrine\DBAL\Connection;
use App\Modules\People\Entity\Family;
use App\Modules\People\Entity\Person;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Class FamilyMemberStudentRepository
 * @package App\Modules\People\Repository
 */
class FamilyMemberStudentRepository extends ServiceEntityRepository
{
    /**
     * FamilyChildRepository constructor.
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FamilyMemberStudent::class);
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
     * @param Family|null $family
     * @param bool $asArray
     * @return array
     */
    public function findByFamily(?Family $family, bool $asArray = false): array
    {
        $query = $this->createQueryBuilder('m')
            ->join('m.family', 'f')
            ->where('m.family = :family')
            ->setParameter('family', $family)
            ->join('m.student', 's')
            ->leftJoin('s.person', 'p')
            ->leftJoin('p.personalDocumentation', 'd')
            ->join('s.studentEnrolments', 'se')
            ->leftJoin('se.rollGroup','rg')
            ->andWhere('rg.academicYear = :academicYear')
            ->setParameter('academicYear', AcademicYearHelper::getCurrentAcademicYear())
            ->orderBy('p.surname', 'ASC')
            ->addOrderBy('p.firstName', 'ASC');

        if ($asArray)
            return $query->select(["CONCAT(p.firstName,' ',p.surname) AS fullName","COALESCE(d.personalImage, '/build/static/DefaultPerson.png') AS photo",'p.status','m.id AS student_id','m.comment','f.id AS family_id','p.id AS person_id','p.status', 'rg.name AS roll'])
                ->getQuery()
                ->getResult();
        return $query->select(['p','m','s','d'])
            ->getQuery()
            ->getResult();
    }

    /**
     * findByFamilyList
     * @param array $familyList
     * @return array
     * 25/07/2020 07:57
     */
    public function findByFamilyList(array $familyList): array
    {
        return $this->createQueryBuilder('fms')
            ->join('fms.family', 'f')
            ->where('f.id in (:family)')
            ->setParameter('family', $familyList, Connection::PARAM_STR_ARRAY)
            ->leftJoin('fms.student', 's')
            ->leftJoin('s.person','p')
            ->orderBy('f.id', 'ASC')
            ->addOrderBy('p.surname', 'ASC')
            ->addOrderBy('p.firstName', 'ASC')
            ->select(['p.title','p.firstName AS first', 'p.preferredName AS preferred', 'p.surname', "CONCAT(p.firstName,' ',p.surname) AS fullName", 'f.id AS id'])
            ->getQuery()
            ->getResult();
    }
}
