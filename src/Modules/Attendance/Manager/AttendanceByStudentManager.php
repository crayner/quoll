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
 * Time: 17:37
 */

namespace App\Modules\Attendance\Manager;

use App\Manager\StatusManager;
use App\Modules\Attendance\Entity\AttendanceRollGroup;
use App\Modules\Attendance\Entity\AttendanceStudent;
use App\Modules\Student\Entity\Student;
use App\Provider\ProviderFactory;
use App\Util\TranslationHelper;
use DateTimeImmutable;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;

/**
 * Class AttendanceByStudentManager
 *
 * 28/10/2020 08:02
 * @package App\Modules\Attendance\Manager
 * @author Craig Rayner <craig@craigrayner.com>
 */
class AttendanceByStudentManager
{
    /**
     * @var Student|null
     */
    private ?Student $student;

    /**
     * @var DateTimeImmutable|null
     */
    private ?DateTimeImmutable $date;

    /**
     * @var string
     */
    private string $dailyTime = 'all_day';

    /**
     * @var StatusManager
     */
    private StatusManager $status;

    /**
     * @var RouterInterface
     */
    private RouterInterface $router;

    /**
     * AttendanceByStudentManager constructor.
     *
     * @param StatusManager $status
     * @param RouterInterface $router
     */
    public function __construct(StatusManager $status, RouterInterface $router)
    {
        $this->status = $status;
        $this->router = $router;
    }

    /**
     * Student
     *
     * @return Student|null
     */
    public function getStudent(): ?Student
    {
        return isset($this->student) ? $this->student : null;
    }

    /**
     * Student
     *
     * @param Student|null $student
     * @return AttendanceByStudentManager
     */
    public function setStudent(?Student $student): AttendanceByStudentManager
    {
        $this->student = $student;
        return $this;
    }

    /**
     * Date
     *
     * @return DateTimeImmutable|null
     */
    public function getDate(): ?DateTimeImmutable
    {
        return isset($this->date) ? $this->date : null;
    }

    /**
     * Date
     *
     * @param DateTimeImmutable|null $date
     * @return AttendanceByStudentManager
     */
    public function setDate(?DateTimeImmutable $date): AttendanceByStudentManager
    {
        $this->date = $date;
        return $this;
    }

    /**
     * DailyTime
     *
     * @return string
     */
    public function getDailyTime(): string
    {
        return $this->dailyTime;
    }

    /**
     * DailyTime
     *
     * @param string $dailyTime
     * @return AttendanceByStudentManager
     */
    public function setDailyTime(string $dailyTime): AttendanceByStudentManager
    {
        $this->dailyTime = $dailyTime;
        return $this;
    }

    /**
     * handleSubmit
     *
     * 28/10/2020 07:52
     * @param FormInterface $form
     * @param Request $request
     */
    public function handleSubmit(FormInterface $form, Request $request)
    {
        $content = json_decode($request->getContent(), true);

        $params = $request->attributes->get('_route_params');

        $content['dailyTime'] = empty($content['dailyTime']) ? 'all_day' : $content['dailyTime'];

        $as = $form->getData();

        $same = true;
        foreach ($params as $name=>$value) {
            if ($content[$name] !== $value) {
                $same = false;
                break;
            }
        }

        if (!$same) {
            $this->getStatus()->info('A change was made in the attendance selection.  No data has been saved.',[],'Attendance');
            $this->getStatus()->setReDirect($this->getRouter()->generate('attendance_by_student', ['student' => $content['student'], 'date' => $content['date'], 'dailyTime' => $content['dailyTime']]), true);
            return;
        }

        if ($as->getAttendanceRollGroup() === null) {
            $arg = ProviderFactory::getRepository(AttendanceRollGroup::class)->findOneBy(['rollGroup' => $as->getStudent()->getCurrentEnrolment()->getRollGroup(), 'date' => new DateTimeImmutable($params['date']), 'dailyTime' => $params['dailyTime']]) ?: new AttendanceRollGroup($as->getStudent()->getCurrentEnrolment()->getRollGroup(), new DateTimeImmutable($params['date']), $params['dailyTime']);
            $as->setAttendanceRollGroup($arg);
            ProviderFactory::create(AttendanceRollGroup::class)->persistFlush($arg);
        }

        $form->submit($content);



        if ($form->isValid()) {
            $as = $form->getData();
            ProviderFactory::create(AttendanceStudent::class)->persistFlush($as);
            if ($this->getStatus()->isStatusSuccess()) {
                $this->getStatus()->setReDirect($this->getRouter()->generate('attendance_by_student', ['student' => $content['student'], 'date' => $content['date'], 'dailyTime' => $content['dailyTime']]), true);
            }
        }
    }

    /**
     * getStatus
     *
     * 28/10/2020 07:54
     * @return StatusManager
     */
    public function getStatus(): StatusManager
    {
        return $this->status;
    }

    /**
     * Router
     *
     * @return RouterInterface
     */
    public function getRouter(): RouterInterface
    {
        return $this->router;
    }

}
