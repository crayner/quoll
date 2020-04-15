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
namespace App\Modules\System\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Modules\System\Entity\Country;

/**
 * Class CountryRepository
 * @package App\Modules\System\Repository
 */
class CountryRepository extends ServiceEntityRepository
{
    /**
     * CountryRepository constructor.
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Country::class);
    }

    /**
     * getCountyCodeList
     */
    public function getCountryCodeList(): array
    {
        return $this->createQueryBuilder('c')
            ->orderBy('c.printable_name', 'ASC')
            ->addOrderBy('c.iddCountryCode', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
