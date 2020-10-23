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
 * Date: 23/10/2020
 * Time: 11:26
 */

namespace App\Modules\Attendance\Listeners;


use App\Modules\Attendance\Entity\AttendanceLogRollGroup;
use App\Modules\Attendance\Entity\AttendanceLogStudent;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;

class AttendanceListener implements EventSubscriber
{
    /**
     * getSubscribedEvents
     */
    public function getSubscribedEvents()
    {
        return [
            Events::preUpdate,
        ];
    }

    /**
     * preUpdate
     *
     * 23/10/2020 11:41
     * @param PreUpdateEventArgs $args
     */
    public function preUpdate(PreUpdateEventArgs $args)
    {
        dump($args);
        $entity = $args->getObject();
        $em = $args->getEntityManager();
        $uow = $em->getUnitOfWork();
        $uow->computeChangeSets(); // do not compute changes if inside a listener
        $changeSet = $uow->getEntityChangeSet($entity);

        if ($entity instanceof AttendanceLogStudent) {
            dump($changeSet);
        }

        if ($entity instanceof AttendanceLogRollGroup) {
            dump($changeSet);
        }
    }
}