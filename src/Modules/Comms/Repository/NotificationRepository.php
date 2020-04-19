<?php
/**
 * Created by PhpStorm.
 *
* Quoll
 *
 * (c) 2018 Craig Rayner <craig@craigrayner.com>
 *
 * User: craig
 * Date: 23/11/2018
 * Time: 15:27
 */
namespace App\Modules\Comms\Repository;

use App\Modules\People\Entity\Person;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use App\Modules\Comms\Entity\Notification;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Class NotificationRepository
 * @package App\Modules\Comms\Repository
 */
class NotificationRepository extends ServiceEntityRepository
{
    /**
     * ApplicationFormRepository constructor.
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Notification::class);
    }

    /**
     * findByPersonStatus
     * @param Person $person
     * @param string $status
     * @return array
     */
    public function findByPersonStatus(Person $person, string $status = 'New')
    {
        $results = $this->createQueryBuilder('n')
            ->select(['n.id', 'n.text', 'n.timestamp', 'n.count', 'n.actionLink', 'm.name AS source'])
            ->join('n.module', 'm')
            ->where('n.status = :new')
            ->andWhere('n.person = :person')
            ->setParameters(['new' => $status, 'person' => $person])
            ->getQuery()
            ->getResult();
        $results = array_merge($results, $this->createQueryBuilder('n')
            ->select(['n.id', 'n.text', 'n.timestamp', 'n.count', 'n.actionLink', "'System' AS source"])
            ->where('n.status = :new')
            ->andWhere('n.person = :person')
            ->andWhere('n.module IS NULL')
            ->setParameters(['new' => $status, 'person' => $person])
            ->getQuery()
            ->getResult());
        $results = new ArrayCollection($results);

        $iterator = $results->getIterator();
        $iterator->uasort(
            function ($a, $b) {
                return $b['timestamp']->getTimestamp() . $a['source'] . $a['text'] < $a['timestamp']->getTimestamp() . $b['source'] . $b['text'] ? -1 : 1 ;
            }
        );
        $results  = new ArrayCollection(iterator_to_array($iterator, false));

        return $results->toArray();
    }
}
