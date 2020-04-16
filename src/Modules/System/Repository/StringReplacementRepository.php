<?php
/**
 * Created by PhpStorm.
 *
 * Kookaburra
 *
 * (c) 2018 Craig Rayner <craig@craigrayner.com>
 *
 * UserProvider: craig
 * Date: 24/11/2018
 * Time: 16:17
 */
namespace App\Modules\System\Repository;

use App\Modules\System\Entity\StringReplacement;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Class StringReplacementRepository
 * @package App\Modules\System\Repository
 */
class StringReplacementRepository extends ServiceEntityRepository
{
    /**
     * StringReplacementRepository constructor.
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, StringReplacement::class);
    }

    /**
     * getPaginationSearch
     * @param string $search
     * @return mixed
     */
    public function getPaginationSearch()
    {
        return $this->createQueryBuilder('s')
            ->orderBy('s.original')
            ->getQuery()
            ->getResult();
    }
}
