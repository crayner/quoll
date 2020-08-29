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
use App\Modules\People\Manager\PersonNameManager;
use App\Util\TranslationHelper;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Form\ChoiceList\View\ChoiceView;

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
     *
     * 23/08/2020 08:48
     * @return QueryBuilder
     */
    public function getAllCareGiversQuery(): QueryBuilder
    {
        return $this->createQueryBuilder('cg')
            ->select(['p','cg','pd','c','cd'])
            ->leftJoin('cg.person', 'p')
            ->leftJoin('p.personalDocumentation','pd')
            ->leftJoin('p.contact', 'c')
            ->leftJoin('cg.customData', 'cd')
            ->orderBy('p.surname', 'ASC')
            ->addOrderBy('p.firstName', 'ASC')
        ;
    }

    /**
     * getDemonstrationCareGivers
     *
     * 27/08/2020 10:33
     * @return array
     */
    public function getDemonstrationCareGivers(): array
    {
        $result = $this->createQueryBuilder('cg')
            ->select(['cg','p','su','c','cd'])
            ->leftJoin('cg.person', 'p')
            ->leftJoin('p.personalDocumentation','pd')
            ->leftJoin('p.contact', 'c')
            ->leftJoin('cg.customData', 'cd')
            ->leftJoin('p.securityUser', 'su')
            ->getQuery()
            ->getResult();
        $items = [];
        foreach ($result as $w) {
            $items[$w->getPerson()->getSecurityUser()->getUsername()] = $w;
        }
        dump($items);
        return $items;
    }

}
