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
 * Date: 12/05/2020
 * Time: 08:54
 */
namespace App\Modules\People\Repository;

use App\Modules\People\Entity\Family;
use App\Modules\People\Entity\FamilyMemberCareGiver;
use App\Modules\People\Entity\Person;
use App\Util\TranslationHelper;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Class FamilyMemberCareGiverRepository
 * @package App\Modules\People\Repository
 * @author Craig Rayner <craig@craigrayner.com>
 */
class FamilyMemberCareGiverRepository extends ServiceEntityRepository
{
    /**
     * FamilyChildRepository constructor.
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FamilyMemberCareGiver::class);
    }

    /**
     * findByFamilyList
     * @param array $familyList
     * @return array
     * 25/07/2020 07:57
     */
    public function findByFamilyList(array $familyList): array
    {
        return $this->createQueryBuilder('fmcg')
            ->join('fmcg.family', 'f')
            ->where('f.id in (:family)')
            ->setParameter('family', $familyList, Connection::PARAM_STR_ARRAY)
            ->leftJoin('fmcg.careGiver', 'cg')
            ->leftJoin('cg.person', 'p')
            ->orderBy('f.id', 'ASC')
            ->addOrderBy('fmcg.contactPriority', 'ASC')
            ->select(['p.title','p.firstName AS first', 'p.preferredName AS preferred', 'p.surname', "CONCAT(p.title,' ',p.firstName,' ',p.surname) AS fullName", 'f.id AS id'])
            ->getQuery()
            ->getResult();
    }

    /**
     * findByFamily
     * @param Family|integer $family
     * @param bool $asArray
     * @return array
     */
    public function findByFamily(Family $family, bool $asArray = false): array
    {
        $query = $this->createQueryBuilder('fmcg')
            ->join('fmcg.family', 'f')
            ->where('fmcg.family = :family')
            ->setParameter('family', $family)
            ->leftJoin('fmcg.careGiver', 'cg')
            ->leftJoin('cg.person', 'p')
            ->orderBy('fmcg.contactPriority', 'ASC');

        if ($asArray)
            return $query->select(['fmcg.comment','fmcg.contactPriority','fmcg.contactSMS AS sms','fmcg.contactMail AS mail','fmcg.contactEmail AS email','fmcg.contactCall AS phone','fmcg.childDataAccess', 'p.status', 'p.title', 'f.id AS family_id', 'p.id AS person_id', 'fmcg.id AS care_giver_id',"CONCAT(p.surname,': ',p.firstName) AS fullName"])
                ->getQuery()
                ->getResult();
        return $query->select(['fmcg','f','cg','p'])
            ->getQuery()
            ->getResult();
    }

    /**
     * getNextContactPriority
     * @param Family $family
     * @return int
     */
    public function getNextContactPriority(Family $family): int
    {
        try {
            return $this->createQueryBuilder('a')
                    ->select('a.contactPriority')
                    ->orderBy('a.contactPriority', 'DESC')
                    ->where('a.family = :family')
                    ->setParameter('family', $family)
                    ->setMaxResults(1)
                    ->getQuery()
                    ->getSingleScalarResult() + 1;
        } catch (NoResultException | NonUniqueResultException $e) {
            return 1;
        }
    }

    /**
     * findByFamilyWithoutCareGiver
     * @param string $person
     * @param string $family
     * @return array
     */
    public function findByFamilyWithoutCareGiver(string $person, string $family): array
    {
        return $this->createQueryBuilder('fmcg')
            ->leftJoin('fmcg.family', 'f')
            ->leftJoin('fmcg.careGiver','cg')
            ->leftJoin('cg.person', 'p')
            ->where('f.id = :family')
            ->andWhere('p.id <> :person')
            ->setParameters(['person' => $person, 'family' => $family])
            ->orderBy('fmcg.contactPriority', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * findFamiliesOfParent
     * @param Person $parent
     * @return int|mixed|string
     * 19/06/2020 09:31
     */
    public function findFamiliesOfParent(Person $parent)
    {
        return $this->createQueryBuilder('fa')
            ->select(['f','fa'])
            ->leftJoin('fa.family', 'f')
            ->where('fa.person = :parent')
            ->setParameter('parent', $parent)
            ->getQuery()
            ->getResult();
    }
}
