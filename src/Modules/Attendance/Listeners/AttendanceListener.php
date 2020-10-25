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

use App\Modules\Attendance\Entity\AttendanceLogClass;
use App\Modules\Attendance\Entity\AttendanceLogRollGroup;
use App\Modules\Attendance\Entity\AttendanceLogStudent;
use App\Modules\System\Manager\SettingFactory;
use App\Util\TranslationHelper;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Psr\Log\LoggerInterface;

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
     * @var LoggerInterface
     */
    private LoggerInterface $log;

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

        if ($entity instanceof AttendanceLogStudent) {
            $entity->setCreator()
                ->setCreationDate();
            $this->logEntityChanges($entity, []);
        }

        if ($entity instanceof AttendanceLogRollGroup) {
            $entity->setCreator()
                ->setCreationDate();
        }

        if ($entity instanceof AttendanceLogClass) {
            $entity->setCreator()
                ->setCreationDate();
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

        if ($entity instanceof AttendanceLogStudent) {
            $changed = false;
            foreach ($changeSet as $name => $compare) {
                if (in_array($name, ['reason','comment','code','context'])) {
                    $changed = true;
                    break;
                }
            }

            if ($changed) {
                $logStatus = SettingFactory::getSettingManager()->get('Attendance', 'logAttendance');
                if ($logStatus !== 'None') {
                    if ($entity->getAttendanceRollGroup() !== null && in_array($logStatus, ['All','Daily Only'])) {
                        $this->logEntityChanges($entity, $changeSet);
                    }
                    if ($entity->getAttendanceClass() !== null && in_array($logStatus, ['All','Class Only'])) {
                        $this->logEntityChanges($entity, $changeSet);
                    }
                }
                $entity->setRecorderDate()
                    ->setRecorder();

            } else {
                $uow->clearEntityChangeSet(spl_object_hash($entity));
            }
        }

        if ($entity instanceof AttendanceLogRollGroup) {
            $entity->setRecordedDate()
                ->setRecorder();
        }

        if ($entity instanceof AttendanceLogClass) {
            $entity->setRecordedDate()
                ->setRecorder();
        }
    }

    /**
     * Log
     *
     * @return LoggerInterface
     */
    public function getLog(): LoggerInterface
    {
        return $this->log;
    }

    /**
     * Log
     *
     * @param LoggerInterface $log
     * @return AttendanceListener
     */
    public function setLog(LoggerInterface $log): AttendanceListener
    {
        $this->log = $log;
        return $this;
    }

    /**
     * logEntityChanges
     *
     * 24/10/2020 10:50
     * @param AttendanceLogStudent $entity
     * @param array $changeSet
     */
    public function logEntityChanges(AttendanceLogStudent $entity, array $changeSet)
    {
        $result = [];
        foreach ($changeSet as $name=>$item) {
            switch ($name) {
                case 'context':
                case 'reason':
                case 'comment':
                    $result[] = TranslationHelper::translate('attendance_log_change', ['name' => TranslationHelper::translate(strtolower('attendancelogstudent.'.$name), [], 'Attendance'),'original' => $item[0], 'change' => $item[1]]);
                    break;
                case 'code':
                    $result[] = TranslationHelper::translate('attendance_log_change', ['name' => TranslationHelper::translate(strtolower('attendancelogstudent.'.$name), [], 'Attendance'),'original' => $item[0]->getName(), 'change' => $item[1]->getName()]);
                    break;
            }
        }
        if ($changeSet === []) {
            $result[] = TranslationHelper::translate('attendance_log_change', ['name' => TranslationHelper::translate(strtolower('attendancelogstudent.code'), [], 'Attendance'),'original' => 'persist', 'change' => $entity->getCode()->getName()], 'Attendance');
            $result[] = TranslationHelper::translate('attendance_log_change', ['name' => TranslationHelper::translate(strtolower('attendancelogstudent.context'), [], 'Attendance'),'original' => 'persist', 'change' => $entity->getContext()], 'Attendance');
            $result[] = TranslationHelper::translate('attendance_log_change', ['name' => TranslationHelper::translate(strtolower('attendancelogstudent.reason'), [], 'Attendance'),'original' => 'persist', 'change' => $entity->getReason()], 'Attendance');
            $result[] = TranslationHelper::translate('attendance_log_change', ['name' => TranslationHelper::translate(strtolower('attendancelogstudent.comment'), [], 'Attendance'),'original' => 'persist', 'change' => $entity->getComment()], 'Attendance');
        }

        if ($entity->getAttendanceRollGroup() !== null) {
            $this->getLog()->notice(TranslationHelper::translate('attendance_log_student_roll_group', ['result' => implode(', ', $result), 'student' => $entity->getStudent()->getFullName(), 'recorder' => $entity->getRecorder()->getFullName(), 'date' => $entity->getDate()->format('Y-m-d'), 'time' => $entity->getDailyTime()], 'Attendance'));
        }
        if ($entity->getAttendanceClass() !== null) {
            $this->getLog()->notice(TranslationHelper::translate('attendance_log_student_class', ['result' => implode(', ', $result), 'student' => $entity->getStudent()->getFullName(), 'recorder' => $entity->getRecorder()->getFullName(), 'date' => $entity->getDate()->format('Y-m-d'), 'class' => $entity->getAttendanceClass()->getFullName()], 'Attendance'));
        }
    }
}