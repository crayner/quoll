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

use App\Modules\Attendance\Entity\AttendanceStudent;
use App\Modules\Attendance\Manager\AttendanceLogger;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;

/**
 * Class AttendanceListener
 *
 * 24/10/2020 10:34
 * @package App\Modules\Attendance\Listeners
 * @author Craig Rayner <craig@craigrayner.com>
 */
class AttendanceListener implements EventSubscriber
{
    /**
     * @var AttendanceLogger
     */
    private AttendanceLogger $logger;

    /**
     * getSubscribedEvents
     */
    public function getSubscribedEvents()
    {
        return [
            Events::preUpdate,
            Events::prePersist,
        ];
    }

    /**
     * prePersist
     *
     * 24/10/2020 10:12
     * @param LifecycleEventArgs $args
     */
    public function prePersist(LifecycleEventArgs $args)
    {
        $entity = $args->getObject();

        if ($entity instanceof AttendanceStudent) {
            $this->addAttendanceRecorderLog($entity, []);
        }
    }

    /**
     * preUpdate
     *
     * 23/10/2020 11:41
     * @param PreUpdateEventArgs $args
     */
    public function preUpdate(PreUpdateEventArgs $args)
    {
        $entity = $args->getObject();
        $em = $args->getEntityManager();
        $uow = $em->getUnitOfWork();
        $uow->computeChangeSets(); // do not compute changes if inside a listener
        $changeSet = $uow->getEntityChangeSet($entity);

        if ($entity instanceof AttendanceStudent) {
            $changed = false;
            foreach ($changeSet as $name => $compare) {
                if (in_array($name, ['reason','comment','code','context'])) {
                    $changed = true;
                    break;
                }
            }

            if ($changed) {
                $this->addAttendanceRecorderLog($entity, $changeSet);
            } else {
                $uow->clearEntityChangeSet(spl_object_hash($entity));
            }
        }
    }

    /**
     * addAttendanceRecorderLog
     *
     * 27/10/2020 11:22
     * @param AttendanceStudent $entity
     * @param array $changeSet
     * @return AttendanceListener
     */
    private function addAttendanceRecorderLog(AttendanceStudent $entity, array $changeSet): AttendanceListener
    {
        $this->getLogger()->addEvent(['entity' => $entity, 'changeSet' => $changeSet]);
        return $this;
    }

    /**
     * Logger
     *
     * @return AttendanceLogger
     */
    private function getLogger(): AttendanceLogger
    {
        return $this->logger;
    }

    /**
     * Logger
     *
     * @param AttendanceLogger $logger
     * @return AttendanceListener
     */
    public function setLogger(AttendanceLogger $logger): AttendanceListener
    {
        $this->logger = $logger;
        return $this;
    }

}