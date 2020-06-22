<?php
/**
 * Created by PhpStorm.
 *
 * Kookaburra
 *
 * (c) 2018 Craig Rayner <craig@craigrayner.com>
 *
 * User: craig
 * Date: 23/11/2018
 * Time: 15:27
 */
namespace App\Modules\Medical\Repository;

use App\Modules\Medical\Entity\PersonMedical;
use App\Modules\People\Entity\Person;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Class PersonMedicalRepository
 * @package App\Modules\Medical\Repository
 * @author Craig Rayner <craig@craigrayner.com>
 */
class PersonMedicalRepository extends ServiceEntityRepository
{
    /**
     * PersonMedicalRepository constructor.
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PersonMedical::class);
    }

    /**
     * findHighestMedicalRisk
     * @param Person $student
     * @return array|bool
     */
    function findHighestMedicalRisk(Person $student)
    {
        try {
            return $this->createQueryBuilder('pm')
                ->select(['al.id', 'al.name', 'al.colour', 'al.colourBG', 'al.abbreviation'])
                ->join('pm.personMedicalConditions', 'pmc')
                ->join('pmc.alertLevel', 'al')
                ->where('pm.student = :student')
                ->orderBy('al.priority', 'DESC')
                ->setParameter('student', $student)
                ->setMaxResults(1)
                ->getQuery()
                ->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            return null;
        }
    }
}
