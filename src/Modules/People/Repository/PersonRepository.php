<?php
/**
 * Created by PhpStorm.
 *
 * Project: Kookaburra
 * Build: Quoll
 *
 * (c) 2018 Craig Rayner <craig@craigrayner.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * UserProvider: craig
 * Date: 23/11/2018
 * Time: 09:01
 */
namespace App\Modules\People\Repository;

use App\Modules\People\Entity\Address;
use App\Modules\People\Entity\CareGiver;
use App\Modules\People\Entity\Person;
use App\Modules\RollGroup\Entity\RollGroup;
use App\Modules\School\Entity\AcademicYear;
use App\Modules\School\Util\AcademicYearHelper;
use App\Modules\Staff\Entity\Staff;
use App\Modules\Student\Entity\Student;
use App\Provider\ProviderFactory;
use App\Util\TranslationHelper;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Class PersonRepository
 * @package App\Modules\People\Repository
 */
class PersonRepository extends ServiceEntityRepository
{
    /**
     * @var string|null
     */
    private $where;

    /**
     * @var array|null
     */
    private $params;

    /**
     * PersonRepository constructor.
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Person::class);
    }

    /**
     * @return string|null
     */
    public function getWhere(): ?string
    {
        return $this->where;
    }

    /**
     * @param string|null $where
     * @return PersonRepository
     */
    public function setWhere(?string $where): PersonRepository
    {
        $this->where = $where;
        return $this;
    }

    /**
     * @return array
     */
    public function getParams(): array
    {
        if (null === $this->params) {
            $this->params = [];
        }

        return $this->params;
    }

    /**
     * @param array|null $params
     * @return PersonRepository
     */
    public function setParams(?array $params): PersonRepository
    {
        $this->params = $params;
        return $this;
    }

    /**
     * addParam
     * @param $name
     * @param $value
     * @return PersonRepository
     * 17/06/2020 10:37
     */
    public function addParam($name, $value): PersonRepository
    {
        $this->getParams();

        $this->params[$name] = $value;

        return $this;
    }

    /**
     * findStaffForFastFinder
     * @param string $staffTitle
     * @return array|null
     * @throws \Exception
     */
    public function findStaffForFastFinder(string $staffTitle): ?array
    {
        $this->getStaffSearch();
        $this->addParam('full', 'Full')
            ->addParam('today', new \DateTimeImmutable(date('Y-m-d')));
        return $this->createQueryBuilder('p')
            ->select(["CONCAT('".$staffTitle . "', p.surname, ', ', p.preferredName) as text", "CONCAT('Sta-', p.id) AS id", "CONCAT(p.username, ' ', p.email) AS search"])
            ->leftJoin('p.securityRoles', 'r')
            ->distinct()
            ->where('p.status = :full')
            ->andWhere('(p.dateStart IS NULL OR p.dateStart <= :today)')
            ->andWhere('(p.dateEnd IS NULL OR p.dateEnd >= :today)')
            ->orderBy('text')
            ->andWhere($this->getWhere())
            ->setParameters($this->getParams())
            ->getQuery()
            ->getResult();
    }

    /**
     * findStudentsForFastFinder
     * @param AcademicYear $academicYear
     * @param string $studentTitle
     * @return array|null
     * @throws \Exception
     */
    public function findStudentsForFastFinder(AcademicYear $academicYear, string $studentTitle): ?array
    {
        return $this->createQueryBuilder('p')
            ->select([
                "CONCAT('".$studentTitle."', p.surname, ', ', p.preferredName, ' (', rg.name, ', ', p.studentIdentifier, ')') AS text",
                "CONCAT(p.username, ' ', p.firstName, ' ', p.email) AS search",
                "CONCAT('Stu-', p.id) AS id",
            ])
            ->join('p.studentEnrolments', 'se')
            ->join('se.rollGroup', 'rg')
            ->where('rg.academicYear = :academicYear')
            ->andWhere('p.status = :full')
            ->andWhere('(p.dateStart IS NULL OR p.dateStart <= :today)')
            ->andWhere('(p.dateEnd IS NULL OR p.dateEnd >= :today)')
            ->setParameters(['today' => new \DateTime(date('Y-m-d')), 'academicYear' => $academicYear, 'full' => 'Full'])
            ->orderBy('text')
            ->getQuery()
            ->getResult();
    }

    /**
     * findByRoles
     * @param array $roles
     * @param string $status
     * @return mixed
     */
    public function findByRoles(array $roles = [], string $status = 'Full')
    {
        $today = new \DateTimeImmutable(date('Y-m-d'));
        $this->getRoleSearch($roles)
            ->addParam('status', $status);
        return $this->createQueryBuilder('p')
            ->join('p.securityUser', 'u')
            ->where('p.status = :status')
            ->andWhere($this->getWhere())
            ->orderBy('p.surname', 'ASC')
            ->addOrderBy('p.firstName', "ASC")
            ->setParameters($this->getParams())
            ->getQuery()
            ->getResult();
    }

    /**
     * findAllFullList
     * @return array
     */
    public function findAllFullList(): array
    {
        return $this->createQueryBuilder('p')
            ->select(['p.id', "CONCAT(p.surname, ': ', p.preferredName) AS fullName"])
            ->where('p.status = :full')
            ->setParameter('full', 'Full')
            ->orderBy('p.surname', 'ASC')
            ->addOrderBy('p.preferredName', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * findOneUsingQuickSearch
     * @param string $search
     * @return Person|null
     */
    public function findOneUsingQuickSearch(string $search): ?Person
    {
        $query = $this->createQueryBuilder('p')
            ->where('p.id = :searchInt')->setParameter('searchInt', intval($search));
        if ($search !== '')
            $query->orWhere('p.studentID = :search')->orWhere('p.username = :search')->setParameter('search', $search);

        try {
            return $query
                ->getQuery()
                ->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            return null;
        }
    }

    /**
     * findOthers
     * @return array
     */
    public function findOthers(): array
    {
        $this->getStaffSearch();
        $where = trim($this->getWhere(), ')') . ' OR p.securityRoles LIKE :careGiver OR p.securityRoles LIKE :student)';
        $this->addParam('careGiver', '%ROLE_CARE_GIVER%')
            ->addParam('student', '%ROLE_STUDENT%');
        return $this->createQueryBuilder('p')
            ->leftJoin('p.adults', 'fa')
            ->where('fa.id IS NULL')
            ->leftJoin('p.children', 'fc')
            ->andWhere('fc.id IS NULL')
            ->andWhere('!' . $where)
            ->setParameters($this->getParams())
            ->orderBy('p.surname', 'ASC')
            ->addOrderBy('p.preferredName', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * getPaginationContent
     * @return array
     */
    public function getPaginationContent(): array
    {
        $students = ProviderFactory::getRepository(Student::class)->getAllStudentsQuery()
            ->select(["COALESCE(d.personalImage, '/build/static/DefaultPerson.png') AS photo", "CONCAT(p.surname, ': ', p.preferredName) AS fullName",'p.id','p.status','f.name AS family','f.id As family_id',"COALESCE(u.username, '') AS username", "'Student' AS role", 'u.canLogin','p.id'])
            ->join('s.memberOfFamilies', 'fm')
            ->leftJoin('fm.family', 'f')
            ->leftJoin('p.personalDocumentation', 'd')
            ->leftJoin('p.securityUser', 'u')
            ->getQuery()
            ->getResult();
        $careGivers = ProviderFactory::getRepository(CareGiver::class)->getAllCareGiversQuery()
            ->select(["COALESCE(d.personalImage, '/build/static/DefaultPerson.png') AS photo", "CONCAT(p.surname, ': ', p.preferredName) AS fullName",'p.id','p.status','f.name AS family','f.id As family_id',"COALESCE(u.username, '') AS username", "'Care Giver' AS role", 'u.canLogin','p.id'])
            ->leftJoin('cg.memberOfFamilies', 'fm')
            ->leftJoin('fm.family', 'f')
            ->leftJoin('p.securityUser', 'u')
            ->leftJoin('p.personalDocumentation', 'd')
            ->getQuery()
            ->getResult();
        $staff = ProviderFactory::getRepository(Staff::class)->getAllStaffQuery()
            ->select(["COALESCE(d.personalImage, '/build/static/DefaultPerson.png') AS photo", "CONCAT(p.surname, ': ', p.preferredName) AS fullName",'p.id','p.status', "'' AS family", "'' AS family_id", "COALESCE(u.username, '') AS username", "'Staff' AS role", 'u.canLogin','p.id'])
            ->leftJoin('p.personalDocumentation', 'd')
            ->leftJoin('p.securityUser', 'u')
            ->getQuery()
            ->getResult();

        $others = $this->getAllContactsOnly()
            ->select(["COALESCE(d.personalImage, '/build/static/DefaultPerson.png') AS photo", "CONCAT(p.surname, ': ', p.preferredName) AS fullName",'p.id','p.status', "'' AS family", "'' As family_id", "'' AS username", "'Other' AS role", "0 AS canLogin",'p.id'])
            ->leftJoin('p.personalDocumentation', 'd')
            ->getQuery()
            ->getResult();

        $all = [];
        foreach($students as $student) {
            $all[$student['id']] = $student;
        }
        foreach($careGivers as $entity) {
            $id = $entity['id'];
            if (key_exists($id,$all)) {
                $all[$id]['role'] .= ', Care Giver';
            } else {
                $all[$id] = $entity;
            }
        }
        foreach($others as $entity) {
            $id = $entity['id'];
            if (key_exists($id,$all)) {
                $all[$id]['role'] .= ', Other';
            } else {
                $all[$id] = $entity;
            }
        }
        foreach($staff as $entity) {
            $id = $entity['id'];
            if (key_exists($id,$all)) {
                $all[$id]['role'] .= ', Other';
            } else {
                $all[$id] = $entity;
            }
        }

        usort($all, function($a,$b) {
            return $a['fullName'] <= $b['fullName'] ? -1 : 1;
        });

        return $all;
    }

    /**
     * getRoleSearch
     * @param array $roles
     * @return $this
     * 23/06/2020 11:14
     */
    public function getRoleSearch(array $roles): PersonRepository
    {
        $this->setWhere('(');
        $this->setParams([]);
        foreach($roles as $q=>$role) {
            $this->where .= 'u.securityRoles LIKE :role' . $q . ' OR ';
            $this->addParam('role' . $q, '%' . $role . '%');
        }
        $this->where = rtrim($this->where, ' OR') . ')';

        return $this;
    }

    /**
     * getAllContactsOnly
     * @return QueryBuilder
     * 18/07/2020 11:01
     */
    public function getAllContactsOnly(): QueryBuilder
    {
        return $this->createQueryBuilder('p', 'p.id')
            ->where('p.student IS NULL')
            ->andWhere('p.careGiver IS NULL')
            ->andWhere('p.staff IS NULL')
            ->orderBy('p.surname', 'ASC')
            ->addOrderBy('p.firstName', 'ASC');

    }
}
