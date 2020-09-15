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
namespace App\Modules\RollGroup\Repository;

use App\Modules\People\Entity\Person;
use App\Modules\People\Manager\PersonNameManager;
use App\Modules\People\Util\UserHelper;
use App\Modules\RollGroup\Entity\RollGroup;
use App\Modules\School\Entity\AcademicYear;
use App\Modules\School\Entity\Facility;
use App\Modules\School\Util\AcademicYearHelper;
use App\Util\TranslationHelper;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Class RollGroupRepository
 * @package App\Modules\RollGroup\Repository
 * @author Craig Rayner <craig@craigrayner.com>
 */
class RollGroupRepository extends ServiceEntityRepository
{
    /**
     * ApplicationFormRepository constructor.
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RollGroup::class);
    }

    /**
     * findByTutor
     * @param Person $tutor
     * @return array
     */
    public function findByTutor(Person $tutor, ?AcademicYear $schoolYear): array
    {
        $schoolYear = $schoolYear ?: AcademicYearHelper::getCurrentAcademicYear();
        return $this->createQueryBuilder('rg')
            ->select('rg')
            ->where('rg.tutor = :person OR rg.tutor2 = :person OR rg.tutor3 = :person OR rg.assistant = :person OR rg.assistant2 = :person OR rg.assistant3 = :person')
            ->setParameter('person', $tutor)
            ->andWhere('rg.academicYear = :academicYear')
            ->setParameter('academicYear', $schoolYear)
            ->getQuery()
            ->getResult();
    }

    /**
     * findOneByPersonSchoolYear
     * @param Person $person
     * @param AcademicYear $schoolYear
     * @return RollGroup|null
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findOneByPersonSchoolYear(Person $person, AcademicYear $schoolYear): ?RollGroup
    {
        if (UserHelper::isStaff())
            return $this->findOneBy(['tutor' => $person, 'academicYear' => $schoolYear]);
        return $this->findOneByStudent($person, $schoolYear);
    }

    /**
     * findOneByStudent
     * @param Person $person
     * @param AcademicYear|null $schoolYear
     * @return RollGroup|null
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findOneByStudent(Person $person, ?AcademicYear $schoolYear): ?RollGroup
    {
        $schoolYear = $schoolYear ?: AcademicYearHelper::getCurrentAcademicYear();
        return $this->createQueryBuilder('rg')
            ->select('rg')
            ->leftJoin('rg.studentEnrolments', 'se')
            ->where('se.person = :person')
            ->setParameter('person', $person)
            ->andWhere('rg.academicYear = :academicYear')
            ->andWhere('se.academicYear = :academicYear')
            ->setParameter('academicYear', $schoolYear)
            ->orderBy('se.rollOrder', 'ASC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * findByAcademicYear
     * @param AcademicYear $year
     */
    public function findByAcademicYear(AcademicYear $year): array
    {
        return $this->createQueryBuilder('r')
            ->select(['r','s','t'])
            ->leftJoin('r.space', 's')
            ->leftJoin('r.tutor', 't')
            ->leftJoin('r.studentEnrolments', 'se')
            ->where('r.academicYear = :year')
            ->setParameter('year', $year)
            ->orderBy('r.name')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * countFacility
     * @param Facility $facility
     * @return int
     */
    public function countFacility(Facility $facility): int
    {
        try {
            return intval($this->createQueryBuilder('r')
                ->select('COUNT(r.id)')
                ->where('r.facility = :facility')
                ->setParameter('facility', $facility)
                ->getQuery()
                ->getSingleScalarResult());
        } catch ( NoResultException | NonUniqueResultException $e) {
            return 0;
        }
    }
}
