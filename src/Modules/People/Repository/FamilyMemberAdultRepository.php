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
use App\Modules\People\Entity\FamilyMemberAdult;
use App\Modules\People\Entity\Person;
use App\Modules\Security\Util\SecurityHelper;
use App\Util\TranslationHelper;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Class FamilyMemberAdultRepository
 * @package App\Modules\People\Repository
 */
class FamilyMemberAdultRepository extends ServiceEntityRepository
{
    /**
     * FamilyChildRepository constructor.
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FamilyMemberAdult::class);
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

    /**
     * findByFamily
     * @param Family|integer $family
     * @param bool $asArray
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
        return $query->select(['a','p'])
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
     * findByFamilyWithoutAdult
     * @param string $person
     * @param string $family
     * @return array
     */
    public function findByFamilyWithoutAdult(string $person, string $family): array
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
     * findCurrentParentsAsArray
     * @return array
     */
    public function findCurrentParentsAsArray(): array
    {
        $parentLabel = TranslationHelper::translate('Parent', [], 'People');
        return $this->createQueryBuilder('m')
            ->select(['p.id as value', "CONCAT('".$parentLabel.": ', p.surname, ', ', p.firstName, ' (', p.preferredName, ')') AS label", "CONCAT(p.surname, p.firstName,p.preferredName) AS data", "'".$parentLabel."' AS type", "COALESCE(p.image_240,'build/static/DefaultPerson.png') AS photo"])
            ->join('m.person', 'p')
            ->where('(m.contactPriority <= 2 and m.contactPriority > 0)')
            ->andWhere('p.securityRoles LIKE :role')
            ->andWhere('p.status = :full')
            ->setParameter('full', 'Full')
            ->setParameter('role', '%ROLE_PARENT%')
            ->orderBy('p.surname', 'ASC')
            ->addOrderBy('p.preferredName', 'ASC')
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
