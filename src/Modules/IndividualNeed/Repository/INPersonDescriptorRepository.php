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
namespace App\Modules\IndividualNeed\Repository;

use App\Modules\IndividualNeed\Entity\INDescriptor;
use App\Modules\IndividualNeed\Entity\INPersonDescriptor;
use App\Modules\People\Entity\Person;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Class INPersonDescriptorRepository
 * @package App\Modules\IndividualNeed\Repository
 * @author Craig Rayner <craig@craigrayner.com>
 */
class INPersonDescriptorRepository extends ServiceEntityRepository
{
    /**
     * ApplicationFormRepository constructor.
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, INPersonDescriptor::class);
    }

    /**
     * findAlertsByPerson
     * @param Person $person
     * @return array|null
     */
    public function findAlertsByPerson(Person $person): ?array
    {
        return $this->createQueryBuilder('i')
            ->join('i.alertLevel', 'al')
            ->where('i.person = :person')
            ->setParameter('person', $person)
            ->orderBy('al.sequenceNumber', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * countDescriptor
     * @param INDescriptor $descriptor
     * @return int
     */
    public function countDescriptor(INDescriptor $descriptor): int
    {
        try {
            return intval($this->createQueryBuilder('p')
                ->where('p.inDescriptor = :descriptor')
                ->setParameter('descriptor', $descriptor)
                ->select(['COUNT(p.id)'])
                ->getQuery()
                ->getSingleScalarResult());
        } catch (NoResultException | NonUniqueResultException $e) {
            return 0;
        }
    }
}
