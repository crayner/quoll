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
use App\Modules\RollGroup\Entity\RollGroup;
use App\Modules\School\Entity\House;
use App\Modules\School\Util\AcademicYearHelper;
use App\Modules\Student\Entity\Student;
use App\Provider\ProviderFactory;
use App\Util\TranslationHelper;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
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
        $query = $this->createQueryBuilder()
            ->from(Person::class, 'p')
            ->select(['s', 'p','se'])
            ->leftJoin('p.student', 's')
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
     * @return QueryBuilder
     * 18/07/2020 11:01
     */
    public function getAllStudentsQuery(): QueryBuilder
    {
        return $this->createQueryBuilder('s')
            ->leftJoin('s.person', 'p')
            ->orderBy('p.surname', 'ASC')
            ->addOrderBy('p.firstName', 'ASC');
    }
}
