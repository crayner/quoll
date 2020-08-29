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
namespace App\Modules\People\Repository;

use App\Modules\People\Entity\FamilyMember;
use App\Provider\ProviderFactory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Class FamilyChildRepository
 * @package App\Modules\People\Repository
 */
class FamilyMemberRepository extends ServiceEntityRepository
{
    /**
     * FamilyChildRepository constructor.
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FamilyMember::class);
    }

    /**
     * findByDemonstrationData
     *
     * 27/08/2020 14:58
     * @param array $criteria
     * @param array|null $orderBy
     * @param null $limit
     * @param null $offset
     * @return array
     */
    public function findByDemonstrationData(array $criteria, array $orderBy = null, $limit = null, $offset = null): array
    {
        dump(ProviderFactory::getRequest()->attributes);
        if (ProviderFactory::getRequest()->attributes->get('_route') === 'demonstration_load') return [];

        return parent::findBy($criteria, $orderBy, $limit, $offset);
    }
}
