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

use App\Modules\People\Entity\Locality;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Class LocalityRepository
 * @package App\Modules\People\Repository
 */
class LocalityRepository extends ServiceEntityRepository
{
    /**
     * LocalityRepository constructor.
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Locality::class);
    }

    /**
     * buildChoiceList
     * @return array
     */
    public function buildChoiceList(): array
    {
        return $this->createQueryBuilder('l')
            ->select(['l.id', "CONCAT(l.name, ' ', l.territory, ' ', l.postCode, ' ', l.country) AS name"])
            ->orderBy('name','ASC')
            ->getQuery()
            ->getResult();
    }

}
