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
namespace App\Modules\School\Repository;

use App\Modules\School\Entity\AcademicYear;
use App\Modules\School\Entity\AcademicYearTerm;
use App\Modules\School\Util\AcademicYearHelper;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Class AcademicYearTermRepository
 * @package App\Modules\School\Repository
 */
class AcademicYearTermRepository extends ServiceEntityRepository
{
    /**
     * ApplicationFormRepository constructor.
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AcademicYearTerm::class);
    }

    /**
     * isDayInTerm
     * @param \DateTime $date
     * @return bool
     */
    public function isDayInTerm(\DateTime $date): bool
    {
        if ($this->createQueryBuilder('syt')
                ->select('COUNT(syt)')
                ->where('syt.firstDay <= :date and syt.lastDay >= :date')
                ->andWhere('syt.academicYear = :AcademicYear')
                ->setParameters(['AcademicYear' => AcademicYearHelper::getCurrentAcademicYear(), 'date' => $date])
                ->getQuery()
                ->getSingleScalarResult() > 0)
            return true;
        return false;
    }

    /**
     * findOneByDay
     * @param \DateTimeImmutable $date
     * @param AcademicYear|null $year
     * @return AcademicYearTerm|null
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findOneByDay(\DateTimeImmutable $date, ?AcademicYear $year = null): ?AcademicYearTerm
    {
        if (null === $year) $year = AcademicYearHelper::getCurrentAcademicYear();

        return $this->createQueryBuilder('syt')
                ->where('syt.firstDay <= :date and syt.lastDay >= :date')
                ->andWhere('syt.academicYear = :academicYear')
                ->setParameters(['academicYear' => $year, 'date' => $date])
                ->getQuery()
                ->getOneOrNullResult();
    }

    /**
     * findByPaginationList
     * @return mixed
     */
    public function findByPaginationList(): iterable
    {
        return $this->createQueryBuilder('t')
            ->leftJoin('t.academicYear', 'y')
            ->orderBy('y.firstDay')
            ->addOrderBy('t.firstDay')
            ->select(['t','y'])
            ->getQuery()
            ->getResult();

    }

    /**
     * findOtherTerms
     * @param AcademicYearTerm $term
     * @return mixed
     */
    public function findOtherTerms(AcademicYearTerm $term)
    {
        return $this->createQueryBuilder('t')
            ->where('t.academicYear = :year')
            ->setParameter('year', $term->getAcademicYear())
            ->andWhere('t.id <> :term')
            ->setParameter('term', $term->getId())
            ->orderBy('t.firstDay')
            ->getQuery()
            ->getResult();
    }
}
