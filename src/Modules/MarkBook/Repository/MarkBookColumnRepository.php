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

namespace App\Modules\MarkBook\Repository;

use App\Modules\MarkBook\Entity\MarkBookColumn;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Class MarkBookColumnRepository
 * @package App\Modules\MarkBook\Repository
 * @author Craig Rayner <craig@craigrayner.com>
 */
class MarkBookColumnRepository extends ServiceEntityRepository
{
    /**
     * ApplicationFormRepository constructor.
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MarkBookColumn::class);
    }
}
