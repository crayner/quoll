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
namespace App\Modules\Comms\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use App\Modules\Comms\Entity\NotificationEvent;
use App\Modules\Comms\Entity\NotificationListener;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
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

    /**
     * isUnique
     * @param NotificationListener $listener
     * @return bool
     * 23/06/2020 16:25
     */
    public function isUnique(NotificationListener $listener): bool
    {
        try {
            return intval($this->createQueryBuilder('l')
                    ->select(['COUNT(l.id)'])
                    ->where('l.person = :person')
                    ->andWhere('l.id != :identifier')
                    ->andWhere('l.event = :event')
                    ->andWhere('l.scopeType = :scopeType')
                    ->andWhere('l.scopeIdentifier = :scopeIdentifier')
                    ->setParameters($listener->toArray('unique'))
                    ->getQuery()
                    ->getSingleScalarResult()) === 0;
        } catch (NoResultException | NonUniqueResultException $e) {
            return true;
        }
    }

    /**
     * deleteAllForEvent
     * @param NotificationEvent $event
     * @return NotificationListenerRepository
     * 25/06/2020 12:07
     */
    public  function deleteAllForEvent(NotificationEvent $event): NotificationListenerRepository
    {
        $this->createQueryBuilder('l')
            ->delete(NotificationListener::class, 'l')
            ->where('l.event = :event')
            ->setParameter('event', $event)
            ->getQuery()
            ->getResult();
        return $this;
    }
}
