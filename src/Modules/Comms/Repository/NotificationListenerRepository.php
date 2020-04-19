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

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use App\Modules\Comms\Entity\NotificationEvent;
use App\Modules\Comms\Entity\NotificationListener;
use Doctrine\Persistence\ManagerRegistry;
use App\Modules\People\Entity\Person;

/**
 * Class NotificationListenerRepository
 * @package App\Modules\Comms\Repository
 */
class NotificationListenerRepository extends ServiceEntityRepository
{
    /**
     * ApplicationFormRepository constructor.
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, NotificationListener::class);
    }

    /**
     * selectNotificationListenersByScope
     * @param NotificationEvent $event
     * @param array $scopes
     * @return array
     */
    public function selectNotificationListenersByScope(NotificationEvent $event, array $scopes = []): array
    {
        $options['event'] = $event;
        $options['all'] = 'All';

        $query = $this->createQueryBuilder('nl')
            ->distinct()
            ->where('nl.event = :event')
        ;

        if (count($scopes) > 0)
        {
            $sql = '(nl.scopeType = :all ';
            foreach($scopes as $q=>$scope)
            {
                $sql .= "OR (nl.scopeType = :type{$q} AND nl.scopeID = :typeID{$q})";
                $options["type{$q}"] = $scope['type'];
                $options["typeID{$q}"] = $scope['id'];
            }
            $sql .= ')';
        } else {
            $sql = 'nl.scopeType = :all';
        }

        $result = $query->andWhere($sql)->setParameters($options)->getQuery()->getResult();
        $t = [];
        foreach($result as $w)
            $t[] = $w->getPerson();

        return $t;
    }

    /**
     * findNotAllByPerson
     * @param Person $person
     * @return array
     */
    public function findNotAllByPerson(Person $person): array
    {
        return $this->createQueryBuilder('l')
            ->where('l.person = :person')
            ->andWhere('l.scopeType != :all')
            ->setParameters(['all' => 'All', 'person' => $person])
            ->getQuery()
            ->getResult()
            ;
    }
}
