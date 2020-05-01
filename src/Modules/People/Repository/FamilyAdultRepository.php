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

use App\Modules\School\Entity\AcademicYear;
use Doctrine\DBAL\Connection;
use App\Modules\People\Entity\Family;
use App\Modules\People\Entity\FamilyAdult;
use App\Modules\People\Entity\Person;
use App\Provider\ProviderFactory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Class FamilyAdultRepository
 * @package App\Modules\People\Repository
 */
class FamilyAdultRepository extends ServiceEntityRepository
{
    /**
     * FamilyAdultRepository constructor.
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FamilyAdult::class);
    }

    /**
     * @param Person $parent
     * @return array
     */
    public function findChildrenByParent(Person $parent): array
    {
        $x = $this->createQueryBuilder('fa')
            ->leftJoin('fa.family', 'f')
            ->leftJoin('f.children', 'fc')
            ->leftJoin('fc.person', 'p')
            ->select('fa,f,fc,p')
            ->where('fa.person = :person')
            ->setParameter('person', $parent)
            ->getQuery()
            ->getResult();
        $results = [];
        foreach(($x ?: []) as $item) {
            foreach($item->getFamily()->getChildren() as $child)
                if ($child->getPerson())
                    $results[$child->getPerson()->getId()] = $child->getPerson();
        }
        return $results;
    }

    /**
     * findStudentsOfParentFastFinder
     * @param Person $person
     * @param string $studentTitle
     * @param AcademicYear $AcademicYear
     * @return array
     * @throws \Exception
     */
    public function findStudentsOfParentFastFinder(Person $person, string $studentTitle, AcademicYear $AcademicYear): ?array
    {
        $person = ProviderFactory::getRepository(Person::class)->find(2762);
        return $this->createQueryBuilder('fa')
            ->select([
                "CONCAT('".$studentTitle."', p.surname, ', ', p.preferredName, ' (', rg.name, ', ', p.studentID, ')') AS text",
                "CONCAT(p.username, ' ', p.firstName, ' ', p.email) AS search",
                "CONCAT('Stu-', p.id) AS id",
            ])
            ->leftJoin('fa.family', 'f')
            ->join('f.children', 'fc')
            ->join('fc.person', 'p')
            ->join('p.studentEnrolments', 'se')
            ->join('se.rollGroup', 'rg')
            ->where('fa.person = :person')
            ->andWhere('se.academicYear = :AcademicYear')
            ->andWhere('(p.dateStart IS NULL OR p.dateStart >= :today)')
            ->andWhere('(p.dateEnd IS NULL OR p.dateEnd <= :today)')
            ->setParameters(['person' => $person, 'AcademicYear' => $AcademicYear, 'today' => new \DateTime(date('Y-m-d'))])
            ->orderBy('text')
            ->getQuery()
            ->getResult();
    }

    /**
     * findByFamilyWithoutAdult
     * @param int $person
     * @param int $family
     * @return array
     */
    public function findByFamilyWithoutAdult(int $person, int $family): array
    {
        return $this->createQueryBuilder('a')
            ->leftJoin('a.family', 'f')
            ->leftJoin('a.person', 'p')
            ->where('f.id = :family')
            ->andWhere('p.id <> :person')
            ->setParameters(['person' => $person, 'family' => $family])
            ->orderBy('a.contactPriority')
            ->getQuery()
            ->getResult();
    }

    /**
     * findByFamily
     * @param Family|integer $family
     * @return array
     */
    public function findByFamily(Family $family, bool $asArray = false): array
    {
        $query = $this->createQueryBuilder('a')
            ->join('a.family', 'f')
            ->where('f.id = :family')
            ->setParameter('family', $family instanceof Family ? $family->getId() : $family)
            ->leftJoin('a.person', 'p')
            ->orderBy('a.contactPriority', 'ASC');

        if ($asArray)
            return $query->select(['a.comment','a.contactPriority','a.contactSMS AS sms','a.contactMail AS mail','a.contactEmail AS email','a.contactCall AS phone','a.id AS adult_id','a.childDataAccess', 'p.status', 'p.title','p.firstName AS first', 'p.preferredName AS preferred', 'p.surname', 'f.id AS family_id', 'p.id AS person', 'a.id'])
                ->getQuery()
                ->getResult();
        return $query->select(['a','p','s'])
            ->leftJoin('p.staff', 's')
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
        return $this->createQueryBuilder('a')
            ->join('a.family', 'f')
            ->where('f.id in (:family)')
            ->setParameter('family', $familyList, Connection::PARAM_INT_ARRAY)
            ->leftJoin('a.person', 'p')
            ->orderBy('f.id', 'ASC')
            ->addOrderBy('a.contactPriority', 'ASC')
            ->select(['p.title','p.firstName AS first', 'p.preferredName AS preferred', 'p.surname', 'f.id AS id'])
            ->getQuery()
            ->getResult();
    }
}
