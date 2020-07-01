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
use App\Modules\People\Entity\FamilyMemberAdult;
use App\Modules\People\Entity\Locality;
use App\Modules\People\Entity\Phone;
use App\Modules\People\Util\UserHelper;
use App\Modules\Security\Util\SecurityHelper;
use App\Util\ImageHelper;
use App\Util\TranslationHelper;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NoResultException;
use App\Modules\RollGroup\Entity\RollGroup;
use App\Modules\School\Entity\House;
use App\Modules\Security\Entity\District;
use App\Modules\People\Entity\Person;
use App\Modules\School\Entity\AcademicYear;
use App\Modules\School\Util\AcademicYearHelper;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\User\UserInterface;

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
     * Loads the user for the given username.
     *
     * This method must return null if the user is not found.
     *
     * @param string $username The username
     *
     * @return Person|null
     */
    public function loadUserByUsernameOrEmail($username): ?Person
    {
        try {
            return $this->createQueryBuilder('p')
                ->select(['p'])
                ->where('p.email = :username OR p.username = :username')
                ->setParameter('username', $username)
                ->getQuery()
                ->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            return null;
        }
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
     * findStudentsByRollGroup
     * @param RollGroup $rollGroup
     * @param string $sortBy
     * @return mixed
     */
    public function findStudentsByRollGroup(RollGroup $rollGroup, string $sortBy = 'rollOrder')
    {
        $query = $this->createQueryBuilder('p')
            ->select(['p','se'])
            ->join('p.studentEnrolments', 'se')
            ->where('se.rollGroup = :rollGroup')
            ->andWhere('p.securityRoles LIKE :role')
            ->setParameter('rollGroup', $rollGroup)
            ->setParameter('role', '%ROLE_STUDENT%')
            ->andWhere('p.status = :full')
            ->setParameter('full', 'Full');

        switch (substr($sortBy, 0, 4)) {
            case 'roll':
                $query->orderBy('se.rollOrder', 'ASC')
                    ->addOrderBy('p.surname', 'ASC')
                    ->addOrderBy('p.preferredName', 'ASC');
                break;
            case 'surn':
                $query->orderBy('p.surname', 'ASC')
                    ->addOrderBy('p.preferredName', 'ASC');
                break;
            case 'pref':
                $query->orderBy('p.preferredName', 'ASC')
                    ->addOrderBy('p.surname', 'ASC');
                break;
        }

        return $query->getQuery()
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
            ->addParam('status', $status)
            ->addParam('today', $today);
        return $this->createQueryBuilder('p')
            ->where('p.status = :status')
            ->andWhere($this->getWhere())
            ->andWhere('(p.dateStart IS NULL OR p.dateStart <= :today)')
            ->andWhere('(p.dateEnd IS NULL OR p.dateEnd >= :today)')
            ->orderBy('p.surname', 'ASC')
            ->addOrderBy('p.firstName', "ASC")
            ->setParameters($this->getParams())
            ->getQuery()
            ->getResult();
    }

    /**
     * findCurrentStudents
     * @return array
     */
    public function findCurrentStudents(): array
    {
        return $this->findAllStudents('Full');
        $academicYear = AcademicYearHelper::getCurrentAcademicYear();
        $today = new \DateTimeImmutable(date('Y-m-d'));
        return $this->createQueryBuilder('p')
            ->leftJoin('p.studentEnrolments','se')
            ->where('se.academicYear = :academicYear')
            ->setParameter('academicYear', $academicYear)
            ->andWhere('p.status = :full')
            ->setParameter('full', 'Full')
            ->andWhere('(p.dateStart IS NULL OR p.dateStart <= :today)')
            ->andWhere('(p.dateEnd IS NULL OR p.dateEnd >= :today)')
            ->setParameter('today', $today)
            ->orderBy('p.surname', 'ASC')
            ->addOrderBy('p.preferredName', 'ASC')
            ->getQuery()
            ->getResult();
        ;
    }

    /**
     * findCurrentStaff
     * @return array
     * 22/06/2020 14:07
     */
    public function findCurrentStaff(): array
    {
        return $this->getStaffQueryBuilder()
            ->getQuery()
            ->getResult();;
    }

    /**
     * findCurrentStaff
     * @return array
     * @throws \Exception
     */
    public function findCurrentStaffAsArray(): array
    {
        $staffLabel = TranslationHelper::translate('Staff', [], 'People');
        return $this->getStaffQueryBuilder()
            ->select(['p.id as value', "CONCAT('".$staffLabel.": ',p.surname, ', ', p.firstName, ' (', p.preferredName, ')') AS label", "'".$staffLabel."' AS type", "COALESCE(p.image_240,'build/static/DefaultPerson.png') AS photo", "CONCAT(p.surname, p.firstName,p.preferredName) AS data"])
            ->getQuery()
            ->getResult()
        ;
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
     * findAllStudentsByRollGroup
     * @return mixed
     */
    public function findAllStudentsByRollGroup()
    {
        return $this->createQueryBuilder('p')
            ->select(['p.id', 'p.studentIdentifier', "CONCAT(p.surname, ', ', p.preferredName) AS fullName", 'rg.name AS rollGroup', 'rg.name AS type', 'p.image_240 AS photo'])
            ->where('p.status = :full')
            ->setParameter('full', 'Full')
            ->join('p.studentEnrolments', 'se')
            ->andWhere('se.academicYear = :currentYear')
            ->setParameter('currentYear', AcademicYearHelper::getCurrentAcademicYear())
            ->join('se.rollGroup', 'rg')
            ->orderBy('rg.name', 'ASC')
            ->addOrderBy('p.surname', 'ASC')
            ->addOrderBy('p.preferredName', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * findCurrentParents
     * @return array
     */
    public function findCurrentParents(): array
    {
        return $this->createQueryBuilder('p')
            ->select(['p','fa'])
            ->join('p.adults', 'fa')
            ->where('(fa.contactPriority <= 2 and fa.contactPriority > 0)')
            ->andWhere('p.status = :full')
            ->setParameter('full', 'Full')
            ->orderBy('p.surname', 'ASC')
            ->addOrderBy('p.preferredName', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * findAllStudentContactsQuery
     * @return QueryBuilder
     * 28/06/2020 12:22
     */
    public function findAllStudentContactsQuery(): QueryBuilder
    {
        return $this->createQueryBuilder('p')
            ->leftJoin('p.members', 'fm')
            ->where('fm.id IS NOT NULL')
            ->andWhere('fm.contactPriority IS NOT NULL')
            ->orderBy('p.surname', 'ASC')
            ->addOrderBy('p.preferredName', 'ASC');
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
        $where = trim($this->getWhere(), ')') . ' OR p.securityRoles LIKE :parent OR p.securityRoles LIKE :student)';
        $this->addParam('parent', '%ROLE_PARENT%')
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
        $students = $this->findAllStudentsQuery()
            ->select(['p.image_240 AS photo', "CONCAT(p.surname, ': ', p.preferredName) AS fullName",'p.id','p.status','f.name AS family','f.id As family_id','p.username', "'Student' AS role", 'p.canLogin'])
            ->leftJoin('p.members', 'fm')
            ->leftJoin('fm.family', 'f')
            ->getQuery()
            ->getResult();
        $parents = $this->findAllStudentContactsQuery()
            ->select(['p.image_240 AS photo', "CONCAT(p.surname, ': ', p.preferredName) AS fullName",'p.id','p.status','f.name AS family','f.id As family_id','p.username', "'Parent' AS role", 'p.canLogin'])
            ->leftJoin('fm.family', 'f')
            ->getQuery()
            ->getResult();
        $staff = $this->getStaffQueryBuilder()
            ->select(['p.image_240 AS photo', "CONCAT(p.surname, ': ', p.preferredName) AS fullName",'p.id','p.status', "'' AS family", "'' AS family_id", 'p.username', "'Staff' AS role", 'p.canLogin'])
            ->getQuery()
            ->getResult();

        $all = [];
        foreach($students as $person) {
            $all[] = $person['id'];
        }
        foreach($staff as $person) {
            $all[] = $person['id'];
        }
        foreach($parents as $person) {
            $all[] = $person['id'];
        }
        $others = $this->createQueryBuilder('p')
            ->where('p.id NOT IN (:list)')
            ->setParameter('list', $all, Connection::PARAM_STR_ARRAY)
            ->select(['p.image_240 AS photo', "CONCAT(p.surname, ': ', p.preferredName) AS fullName",'p.id','p.status', "'' AS family", "'' As family_id", 'p.username', "'Other' AS role", "'N' AS canLogin"])
            ->getQuery()
            ->getResult();

        $all = array_merge($students,$parents,$staff,$others);

        usort($all, function($a,$b) {
            return $a['fullName'] <= $b['fullName'] ? -1 : 1;
        });

        return $all;
    }

    /**
     * countPeopleInHouse
     * @param House $house
     * @return int
     */
    public function countPeopleInHouse(House $house): int
    {
        try {
            return $this->createQueryBuilder('p')
                ->select('COUNT(p.id)')
                ->where('p.house = :house')
                ->setParameter('house', $house)
                ->getQuery()
                ->getSingleScalarResult();
        } catch (NoResultException | NonUniqueResultException $e) {
            return 0;
        }
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
            return $this->createQueryBuilder('p')
                ->select('COUNT(p.id)')
                ->leftJoin('p.physicalAddress', 'a')
                ->leftJoin('p.postalAddress', 'pa')
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
     * findPhoneList
     * @return array
     */
    public function findPhoneList(): array
    {
        return $this->createQueryBuilder('p')
            ->where('p.personalPhone IS NOT NULL')
            ->select(['p.id as person','ph.id'])
            ->join('p.personalPhone', 'ph')
            ->getQuery()
            ->getResult();
    }

    /**
     * getStaffQueryBuilder
     * @param string $status
     * @return QueryBuilder
     * 17/06/2020 11:33
     */
    public function getStaffQueryBuilder(string $status = 'Full'): QueryBuilder
    {
        $today = new \DateTimeImmutable(date('Y-m-d'));
        $this->getStaffSearch()
            ->addParam('status', $status)
            ->addParam('today', $today);

        return $this->createQueryBuilder('p')
            ->where('p.status = :status')
            ->leftJoin('p.securityRoles', 'r')
            ->andWhere($this->getWhere())
            ->andWhere('(p.dateStart IS NULL OR p.dateStart <= :today)')
            ->andWhere('(p.dateEnd IS NULL OR p.dateEnd >= :today)')
            ->setParameter('today', $today)
            ->setParameters($this->getParams())
            ->orderBy('p.surname')
            ->addOrderBy('p.firstName')
        ;
    }

    /**
     * getStaffSearch
     * @return PersonRepository
     * 17/06/2020 10:42
     */
    public function getStaffSearch(): PersonRepository
    {
        $this->setWhere('r.category = :staff');
        $this->addParam('staff', 'Staff');
        return $this;
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
        dump($roles);
        foreach($roles as $q=>$role) {
            $this->where .= 'r.role LIKE :role' . $q . ' OR ';
            $this->addParam('role' . $q, '%' . $role . '%');
        }
        $this->where = rtrim($this->where, ' OR') . ')';

        return $this;
    }

    /**
     * findAllStudents
     * @param string $status
     * @return array
     * 28/06/2020 12:05
     */
    public function findAllStudents(string $status = 'Full'): array
    {
        return $this->findAllStudentsQuery($status)
            ->getQuery()
            ->getResult();
    }

    /**
     * findAllStudentsQuery
     * @param string $status
     * @return QueryBuilder
     * 28/06/2020 12:05
     */
    public function findAllStudentsQuery(string $status = 'Full'): QueryBuilder
    {
        return $this->createQueryBuilder('p')
            ->leftJoin('p.securityRoles', 'r')
            ->where('r.role = :role')
            ->andWhere('p.status LIKE :status')
            ->andWhere('(p.dateStart <= :today OR p.dateStart IS NULL)')
            ->andWhere('(p.dateEnd >= :today OR p.dateEnd IS NULL)')
            ->setParameters(['role' => 'ROLE_STUDENT', 'status' => '%'.$status.'%', 'today' => new \DateTimeImmutable('now')])
            ->orderBy('p.surname', 'ASC')
            ->addOrderBy('p.firstName', 'ASC');
    }
}
