<?php
/**
 * Created by PhpStorm.
 *
 * Project: Kookaburra
 * Build: Quoll
 *
 * (c) 2020 Craig Rayner <craig@craigrayner.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * User: craig
 * Date: 1/07/2020
 * Time: 15:20
 */
namespace App\Modules\Student\Repository;

use App\Modules\People\Entity\Person;
use App\Modules\People\Manager\PersonNameManager;
use App\Modules\RollGroup\Entity\RollGroup;
use App\Modules\School\Entity\House;
use App\Modules\School\Util\AcademicYearHelper;
use App\Modules\Student\Entity\Student;
use App\Provider\ProviderFactory;
use App\Util\TranslationHelper;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Class StudentRepository
 * @package App\Modules\Student\Repository
 * @author Craig Rayner <craig@craigrayner.com>
 */
class StudentRepository extends ServiceEntityRepository
{
    /**
     * StudentRepository constructor.
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Student::class);
    }

    /**
     * findByRollGroup
     * @param RollGroup $rollGroup
     * @param string $sortBy
     * @return int|mixed|string
     * 16/07/2020 10:00
     */
    public function findByRollGroup(RollGroup $rollGroup, string $sortBy = 'rollOrder')
    {
        $query = $this->createQueryBuilder('s')
            ->select(["s.id","CONCAT(".PersonNameManager::formatNameQuery('p', 'Student', 'Reversed').") AS reversed_name","CONCAT(".PersonNameManager::formatNameQuery('p', 'Student', 'Preferred').") AS full_name",'se.rollOrder', "COALESCE(d.personalImage, 'build/static/DefaultPerson.png') AS photo",'p.id as person_id'])
            ->leftJoin('s.person', 'p')
            ->leftJoin('p.personalDocumentation', 'd')
            ->join('s.studentEnrolments', 'se')
            ->where('se.rollGroup = :rollGroup')
            ->andWhere('p.student IS NOT NULL')
            ->setParameter('rollGroup', $rollGroup)
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
     * findStudentsByRollGroup
     * @param RollGroup $rollGroup
     * @param string $sortBy
     * @return mixed
     * @deprecated Use Student findByRollGroup
     */
    public function findStudentsByRollGroup(RollGroup $rollGroup, string $sortBy = 'rollOrder')
    {
        return ProviderFactory::getRepository(Student::class)->findByRollGroup($rollGroup, $sortBy);
    }

    /**
     * findAllStudentsByRollGroup
     * @return mixed
     */
    public function findAllStudentsByRollGroup(string $status = 'Full')
    {
        $unassigned = TranslationHelper::translate('Unassigned', [], 'messages');
        return $this->getAllStudentsQuery()
            ->from(Person::class, 'p')
            ->select(['p.id', 'p.studentIdentifier', "CONCAT(p.surname, ', ', p.preferredName) AS fullName", "COALESCE(rg.name AS rollGroup,'".$unassigned."')", "COALESCE(d.personalPhoto, '/build/static/DefaultPerson.png') AS photo"])
            ->leftJoin('p.personalDocumentation', 'd')
            ->where('p.status = :full')
            ->setParameter('full', $status)
            ->leftJoin('s.studentEnrolments', 'se')
            ->andWhere('(se.academicYear = :currentYear OR se.academicYear IS NULL)')
            ->setParameter('currentYear', AcademicYearHelper::getCurrentAcademicYear())
            ->leftJoin('se.rollGroup', 'rg')
            ->orderBy('rg.name', 'ASC')
            ->addOrderBy('p.surname', 'ASC')
            ->addOrderBy('p.preferredName', 'ASC')
            ->getQuery()
            ->getResult();
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

    /**
     * findCurrentStudentsAsArray
     * @param string $status
     * @return array
     */
    public function findCurrentStudentsAsArray(string $status = 'Full'): array
    {
        $studentLabel = TranslationHelper::translate('Student', [], 'Student');
        return $this->getAllStudentsQuery()
            ->select(['p.id as value', "CONCAT(COALESCE(rg.abbreviation,'".$studentLabel."'),': ',p.surname,': ',p.firstName,' (',p.preferredName,')') AS label", "'".$studentLabel."' AS type", "CONCAT(p.surname,p.firstName,p.preferredName) AS data", "COALESCE(d.personalImage,'build/static/DefaultPerson.png') AS photo"])
            ->leftJoin('s.studentEnrolments', 'se')
            ->leftJoin('se.rollGroup','rg')
            ->leftJoin('p.personalDocumentation','d')
            ->where('p.status = :full')
            ->setParameter('full', $status)
            ->orderBy('rg.abbreviation', 'ASC')
            ->addOrderBy('p.surname', 'ASC')
            ->addOrderBy('p.preferredName', 'ASC')
            ->andWhere('(se.academicYear = :academicYear OR se.academicYear IS NULL)')
            ->setParameter('academicYear', AcademicYearHelper::getCurrentAcademicYear())
            ->getQuery()
            ->getResult();
    }

    /**
     * findAllStudents
     * @param string $status
     * @return array
     * 28/06/2020 12:05
     */
    public function findAllStudents(string $status = '%'): array
    {
        return $this->getAllStudentsQuery()
            ->andWhere('p.status LIKE :status')
            ->setParameter('status', $status)
            ->getQuery()
            ->getResult();
    }

    /**
     * getAllStudentsQuery
     *
     * 24/08/2020 12:31
     * @return QueryBuilder
     */
    public function getAllStudentsQuery(): QueryBuilder
    {
        return $this->createQueryBuilder('s')
            ->select(['s','p','pd','c'])
            ->leftJoin('s.person', 'p')
            ->leftJoin('p.personalDocumentation', 'pd')
            ->leftJoin('p.contact', 'c')
            ->orderBy('p.surname', 'ASC')
            ->addOrderBy('p.firstName', 'ASC');
    }

    /**
     * getDemonstrationStudents
     *
     * 27/08/2020 10:29
     * @return array
     */
    public function getDemonstrationStudents(): array
    {
        $result = $this->createQueryBuilder('s')
            ->select(['s','p','su','c'])
            ->leftJoin('s.person', 'p')
            ->leftJoin('p.securityUser', 'su')
            ->leftJoin('p.contact', 'c')
            ->where('su.username IS NOT NULL')
            ->getQuery()
            ->getResult();
        $items = [];
        foreach ($result as $w) $items[$w->getPerson()->getSecurityUser()->getUsername()] = $w;
        return $items;
    }

    /**
     * findOneByUsername
     *
     * 9/09/2020 10:34
     * @param string $username
     * @return Student|null
     */
    public function findOneByUsername(string $username): ?Student
    {
        try {
            return $this->createQueryBuilder('s')
                ->leftJoin('s.person', 'p')
                ->leftJoin('p.securityUser', 'su')
                ->where('su.username = :username')
                ->setParameter('username', $username)
                ->getQuery()
                ->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            return null;
        }
    }

    /**
     * getStudentEnrolmentPaginationContent
     *
     * 8/09/2020 09:52
     * @return int|mixed|string
     */
    public function getStudentEnrolmentPaginationContent()
    {
        $result = $this->createQueryBuilder('s', 's.id')
            ->select(["CONCAT(".PersonNameManager::formatNameQuery('p', 'Student', 'Reversed').") AS student", "'' as rollOrder","'' AS rollGroup", "'' AS yearGroup", 's.id', 'p.id AS person_id',"'null' As enrolment"])
            ->leftJoin('s.person', 'p')
            ->orderBy('p.surname', 'ASC')
            ->addOrderBy('p.preferredName', 'ASC')
            ->where('p.status in (:status)')
            ->setParameter('status', ['Full', 'Expected'], Connection::PARAM_STR_ARRAY)
            ->getQuery()
            ->getResult();

        $result = array_values(array_merge($result, $this->createQueryBuilder('s', 's.id')
            ->leftJoin('s.studentEnrolments', 'se')
            ->leftJoin('s.person', 'p')
            ->leftJoin('se.yearGroup', 'yg')
            ->leftJoin('se.rollGroup', 'rg')
            ->where('se.academicYear = :currentYear')
            ->andWhere('p.status in (:status)')
            ->setParameter('currentYear', AcademicYearHelper::getCurrentAcademicYear())
            ->setParameter('status', ['Full', 'Expected'], Connection::PARAM_STR_ARRAY)
            ->orderBy('p.surname', 'ASC')
            ->addOrderBy('p.preferredName', 'ASC')
            ->select(["CONCAT(".PersonNameManager::formatNameQuery('p', 'Student', 'Reversed').") AS student",'se.rollOrder','rg.name AS rollGroup', 'yg.name AS yearGroup', 's.id', 'p.id AS person_id', 'se.id AS enrolment'])
            ->getQuery()
            ->getResult()));
        foreach ($result AS $q=>$w)
            $result[$q]['canDelete'] = $w['enrolment'] !== 'null';

        return $result;
    }

    /**
     * mergeStudentIndividualEnrolmentPagination
     *
     * 10/09/2020 14:00
     * @return int|mixed|string
     */
    public function mergeStudentIndividualEnrolmentPagination(): array
    {
        return $this->getEntityManager()->createQueryBuilder()
            ->from(Person::class, 'p', 'p.id')
            ->select(['p.id',"COALESCE(rg.name, '') AS rollGroup", "COALESCE(yg.name,'') AS yearGroup", "'Student' AS role"])
            ->leftJoin('p.student', 's')
            ->leftJoin('s.studentEnrolments', 'se')
            ->where('se.academicYear = :current')
            ->andWhere('p.student IS NOT NULL')
            ->setParameter('current', AcademicYearHelper::getCurrentAcademicYear())
            ->leftJoin('se.rollGroup', 'rg')
            ->leftJoin('se.yearGroup', 'yg')
            ->getQuery()
            ->getResult();
    }

    /**
     * findClassEnrolmentByRollGroup
     *
     * 15/09/2020 09:30
     * @return array
     */
    public function findClassEnrolmentByRollGroup(): array
    {
        return $this->createQueryBuilder('s', 's.id')
            ->leftJoin('s.studentEnrolments', 'se')
            ->leftJoin('se.rollGroup', 'r')
            ->leftJoin('s.person', 'p')
            ->select(
                [
                    'r.name AS rollGroupName',
                    "CONCAT(".PersonNameManager::formatNameQuery('p','Student','Reversed').") AS studentName",
                    '0 AS classCount',
                    's.id'
                ]
            )
            ->orderBy('r.name','ASC')
            ->addOrderBy('p.surname', 'ASC')
            ->addOrderBy('p.firstName', 'ASC')
            ->where('se.rollGroup IS NOT NULL')
            ->andWhere('se.academicYear = :current')
            ->setParameter('current', AcademicYearHelper::getCurrentAcademicYear())
            ->getQuery()
            ->getResult();
    }
}
