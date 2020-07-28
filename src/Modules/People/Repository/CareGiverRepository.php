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
 * Date: 2/07/2020
 * Time: 08:46
 */
namespace App\Modules\People\Repository;

use App\Modules\People\Entity\CareGiver;
use App\Util\TranslationHelper;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Class ParentContactRepository
 * @package App\Modules\People\Repository
 * @author Craig Rayner <craig@craigrayner.com>
 */
class CareGiverRepository extends ServiceEntityRepository
{
    /**
     * ParentContactRepository constructor.
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CareGiver::class);
    }

    /**
     * findCurrentCareGiversAsArray
     * @param string $status
     * @return array
     * 27/07/2020 09:01
     */
    public function findCurrentCareGiversAsArray(string $status = 'Full'): array
    {
        $cgLabel = TranslationHelper::translate('Care Giver', [], 'People');
        return $this->getAllCareGiversQuery()
            ->select(['p.id as value', "CONCAT('".$cgLabel.": ', p.surname, ', ', p.firstName, ' (', p.preferredName, ')') AS label", "CONCAT(p.surname, p.firstName,p.preferredName) AS data", "'".$cgLabel."' AS type", "COALESCE(d.personalImage,'build/static/DefaultPerson.png') AS photo"])
            ->where('p.status = :full')
            ->leftJoin('p.personalDocumentation','d')
            ->leftJoin('cg.memberOfFamilies', 'm')
            ->andWhere('m.contactPriority in (:priority)')
            ->setParameter('full', $status)
            ->setParameter('priority', [1,2], Connection::PARAM_INT_ARRAY)
            ->getQuery()
            ->getResult();
    }

    /**
     * getAllCareGiversQuery
     * @return QueryBuilder
     * 27/07/2020 08:57
     */
    public function getAllCareGiversQuery(): QueryBuilder
    {
        return $this->createQueryBuilder('cg')
            ->leftJoin('cg.person', 'p')
            ->orderBy('p.surname', 'ASC')
            ->addOrderBy('p.firstName', 'ASC')
        ;
    }
}
