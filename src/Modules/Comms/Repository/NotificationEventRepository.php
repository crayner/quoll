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

namespace App\Modules\Comms\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use App\Modules\Comms\Entity\Module;
use App\Modules\Comms\Entity\NotificationEvent;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Class NotificationEventRepository
 * @package App\Modules\Comms\Repository
 */
class NotificationEventRepository extends ServiceEntityRepository
{
    /**
     * ApplicationFormRepository constructor.
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, NotificationEvent::class);
    }

    /**
     * findAllNotificationEvents
     * @return array
     */
    public function findAllNotificationEvents(): array
    {
        return $this->createQueryBuilder('ne')
            ->join('ne.module', 'm')
            ->leftJoin('ne.listeners', 'nl')
            ->where('m.active = :yes')
            ->setParameter('yes', 'Y')
            ->groupBy('ne.id')
            ->orderBy('m.name')
            ->addOrderBy('ne.event')
            ->getQuery()
            ->getResult();
    }

    /**
     * deleteModuleRecords
     * @param Module $module
     * @return mixed
     */
    public function deleteModuleRecords(Module $module)
    {
        return $this->createQueryBuilder('ne')
            ->delete()
            ->where('ne.module = :module')
            ->setParameter('module', $module)
            ->getQuery()
            ->execute();
    }
}
