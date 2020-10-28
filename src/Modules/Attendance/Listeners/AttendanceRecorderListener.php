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
 * Date: 27/10/2020
 * Time: 09:09
 */
namespace App\Modules\Attendance\Listeners;

use App\Modules\Attendance\Entity\AttendanceRecorderLog;
use App\Modules\Attendance\Entity\AttendanceStudent;
use App\Modules\Attendance\Manager\AttendanceLogger;
use App\Modules\Security\Util\SecurityHelper;
use App\Provider\ProviderFactory;
use App\Util\TranslationHelper;
use DateTimeImmutable;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\TerminateEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Class AttendanceRecorderListener
 *
 * 27/10/2020 09:09
 * @package App\Modules\Attendance\Listeners
 * @author Craig Rayner <craig@craigrayner.com>
 */
class AttendanceRecorderListener implements EventSubscriberInterface
{
    /**
     * @var LoggerInterface
     */
    private LoggerInterface $log;

    /**
     * @var AttendanceLogger
     */
    private AttendanceLogger $logger;

    /**
     * AttendanceRecorderListener constructor.
     *
     * @param LoggerInterface $log
     * @param AttendanceLogger $logger
     */
    public function __construct(LoggerInterface $log, AttendanceLogger $logger)
    {
        $this->log = $log;
        $this->logger = $logger;
    }


    /**
     * getSubscribedEvents
     * @return array|array[]
     */
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::TERMINATE => ['recorder'],
        ];
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
     * recorder
     *
     * 27/10/2020 15:08
     * @param TerminateEvent $event
     */
    public function recorder(TerminateEvent $event) {
        if (count($this->getLogger()->getEvents()) > 0) {
            $em = ProviderFactory::getEntityManager();
            $attendanceLog = $this->getLogger()->getEvents();

            $rollGroups = [];
            $courseClasses = [];
            $recorder = SecurityHelper::getCurrentUser()->getStaff();
            foreach ($attendanceLog as $details) {
                $entity = $details['entity'];
                $rollGroups = $this->addRollGroup($rollGroups, $details['entity']);
                $courseClasses = $this->addCourseClass($courseClasses, $details['entity']);
                $changeSet = $details['changeSet'];
                $result = [];

                foreach ($changeSet as $name => $item) {
                    switch ($name) {
                        case 'context':
                        case 'reason':
                        case 'comment':
                            $result[] = TranslationHelper::translate('attendance_log_change', ['name' => TranslationHelper::translate(strtolower('AttendanceStudent.' . $name), [], 'Attendance'), 'original' => $item[0], 'change' => $item[1]]);
                            break;
                        case 'code':
                            $result[] = TranslationHelper::translate('attendance_log_change', ['name' => TranslationHelper::translate(strtolower('AttendanceStudent.' . $name), [], 'Attendance'), 'original' => $item[0]->getName(), 'change' => $item[1]->getName()]);
                            break;
                    }
                }


                if ($changeSet === []) {
                    $result[] = TranslationHelper::translate('attendance_log_change', ['name' => TranslationHelper::translate(strtolower('AttendanceStudent.code'), [], 'Attendance'), 'original' => 'persist', 'change' => $entity->getCode()->getName()], 'Attendance');
                    $result[] = TranslationHelper::translate('attendance_log_change', ['name' => TranslationHelper::translate(strtolower('AttendanceStudent.context'), [], 'Attendance'), 'original' => 'persist', 'change' => $entity->getContext()], 'Attendance');
                    $result[] = TranslationHelper::translate('attendance_log_change', ['name' => TranslationHelper::translate(strtolower('AttendanceStudent.reason'), [], 'Attendance'), 'original' => 'persist', 'change' => $entity->getReason()], 'Attendance');
                    $result[] = TranslationHelper::translate('attendance_log_change', ['name' => TranslationHelper::translate(strtolower('AttendanceStudent.comment'), [], 'Attendance'), 'original' => 'persist', 'change' => $entity->getComment()], 'Attendance');
                }
                $this->getLog()->notice(trim(TranslationHelper::translate('attendance_log_student',
                    [
                        'type' => $entity->getContextType(),
                        'time' => $entity->getDailyTime(),
                        'date' => $entity->getDate()->format('Y-m-d'),
                        'recorder' => $recorder->getFullName(),
                        'student' => $entity->getStudent()->getFullName(),
                        'roll_group' => $entity->getAttendanceRollGroup() ? ' (' . $entity->getAttendanceRollGroup()->getRollGroup()->getName() . ')' : '',
                        'result' => implode(', ', $result),
                    ], 'Attendance')));
                $arl = new AttendanceRecorderLog();
                $arl->setRecorder($recorder)
                    ->setRecordedOn(new DateTimeImmutable())
                    ->setLogKey('Student')
                    ->setLogId($entity->getId())
                    ->setCode($entity->getCode())
                    ->setReason($entity->getReason())
                    ->setComment($entity->getComment())
                    ->setContext($entity->getContext())
                ;
                $em->persist($arl);
            }

            foreach ($rollGroups as $rollGroup) {
                $arl = new AttendanceRecorderLog();
                $arl->setRecorder($recorder)
                    ->setRecordedOn(new DateTimeImmutable())
                    ->setLogKey('Roll Group')
                    ->setContext('Roll Group')
                    ->setLogId($rollGroup->getId());
               $em->persist($arl);
            }

            foreach ($courseClasses as $rollGroup) {
                $arl = new AttendanceRecorderLog();
                $arl->setRecorder($recorder)
                    ->setRecordedOn(new DateTimeImmutable())
                    ->setLogKey('Course Class')
                    ->setContext('Class')
                    ->setLogId($rollGroup->getId());
                $em->persist($arl);
            }
            $em->flush();
        }
    }

    /**
     * addRollGroup
     *
     * 27/10/2020 12:31
     * @param array $rollGroups
     * @param $entity
     * @return array
     */
    private function addRollGroup(array $rollGroups, AttendanceStudent $entity): array
    {
        if ($entity->getAttendanceRollGroup() !== null) {
            $rollGroups[$entity->getAttendanceRollGroup()->getId()] = $entity->getAttendanceRollGroup();
        }
        return $rollGroups;
    }

    /**
     * addCourseClass
     *
     * 27/10/2020 12:32
     * @param array $courseClasses
     * @param $entity
     * @return array
     */
    private function addCourseClass(array $courseClasses, AttendanceStudent $entity): array
    {
        if ($entity->getAttendanceClass() !== null) {
            $courseClasses[$entity->getAttendanceClass()->getId()] = $entity->getAttendanceClass();
        }
        return $courseClasses;
    }

    /**
     * Logger
     *
     * @return AttendanceLogger
     */
    public function getLogger(): AttendanceLogger
    {
        return $this->logger;
    }
}
