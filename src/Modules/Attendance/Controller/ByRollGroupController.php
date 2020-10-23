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
 * Date: 16/10/2020
 * Time: 14:24
 */
namespace App\Modules\Attendance\Controller;

use App\Container\Container;
use App\Container\Panel;
use App\Container\Section;
use App\Controller\AbstractPageController;
use App\Modules\Attendance\Entity\AttendanceCode;
use App\Modules\Attendance\Entity\AttendanceLogRollGroup;
use App\Modules\Attendance\Entity\AttendanceLogStudent;
use App\Modules\Attendance\Form\AttendanceByRollGroupType;
use App\Modules\Attendance\Manager\AttendanceByRollGroupManager;
use App\Modules\Attendance\Pagination\AttendanceByRollGroupPagination;
use App\Modules\RollGroup\Entity\RollGroup;
use App\Modules\School\Provider\DaysOfWeekProvider;
use App\Modules\School\Util\AcademicYearHelper;
use App\Modules\Security\Util\SecurityHelper;
use App\Modules\Staff\Entity\Staff;
use App\Modules\Student\Entity\Student;
use App\Provider\ProviderFactory;
use DateTimeImmutable;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class ByRollGroupController
 *
 * 16/10/2020 14:24
 * @package App\Modules\Attendance\Controller
 * @author Craig Rayner <craig@craigrayner.com>
 */
class ByRollGroupController extends AbstractPageController
{
    /**
     * manage
     *
     * 16/10/2020 14:25
     * @param AttendanceByRollGroupManager $manager
     * @param AttendanceByRollGroupPagination $pagination
     * @param DateTimeImmutable|null $date
     * @param RollGroup|null $rollGroup
     * @param string|null $dailyTime
     * @return JsonResponse
     * @Route("/attendance/roll/group/manager/{rollGroup}/{date}/{dailyTime}",name="attendance_roll_group_manage")
     * @IsGranted("ROLE_ROUTE")
     */
    public function manage(
        AttendanceByRollGroupManager $manager,
        AttendanceByRollGroupPagination $pagination,
        ?DateTimeImmutable $date = null,
        ?RollGroup $rollGroup = null,
        ?string $dailyTime = null
    ) {
        $manager->setDate($date)
            ->setRollGroup($rollGroup)
            ->setDailyTime($dailyTime);
        $form = $this->createForm(AttendanceByRollGroupType::class, $manager, ['action' => $this->generateUrl('attendance_roll_group_manage',
            [
                'date' => $date ? $date->format('Y-m-d') : null,
                'dailyTime' => $dailyTime,
                'rollGroup' => $rollGroup ? $rollGroup->getId() : null
            ]
        )]);

        if ($this->isPostContent()) {
            $this->submitForm($form);
            if ($form->isValid()) {
                $this->getStatusManager()->setReDirect($this->generateUrl('attendance_roll_group_manage', ['date' => $manager->getDate()->format('Y-m-d'), 'rollGroup' => $manager->getRollGroup()->getId(), 'dailyTime' => $manager->getDailyTime()]));
            }
            return $this->singleForm($form);
        }


        $container = new Container();
        $panel = new Panel('single', 'Attendance', new Section('form','single'));

        if ($manager->isValid()) {
            $pagination
                ->addContext('Roll Group Name', $rollGroup->getName())
                ->addContext('Code Select', $manager->getAttendanceCodes())
                ->addContext('Reason Select', $manager->getReasons())
                ->addContext('Previous Days', $manager->getPreviousDays())
                ->setContent($manager->generateContent())
                ;
        }
        $panel->addSection(new Section('html', $this->renderView('attendance/attendance_roll_group.html.twig', ['pagination' => $pagination, 'manager' => $manager])));

        $container->addForm('single', $form)
            ->addPanel(AcademicYearHelper::academicYearWarning($panel));

        return $this->getPageManager()
            ->createBreadcrumbs('Take Attendance by Roll Group')
            ->setUrl($this->generateUrl('attendance_roll_group_manage', $rollGroup ? ['rollGroup' => $rollGroup->getId(), 'date' => $date->format('Y-m-d'), 'dailyTime' => $dailyTime] : []))
            ->render(
                [
                    'containers' => $this->getContainerManager()
                        ->addContainer($container)
                        ->getBuiltContainers(),
                ]
            )
        ;
    }


    /**
     * postRollGroupDetails
     *
     * 22/10/2020 12:50
     * @param RollGroup $rollGroup
     * @param DateTimeImmutable $date
     * @param string $dailyTime
     * @return RedirectResponse
     * @Route("/attendance/roll/group/{rollGroup}/on/{date}/{dailyTime}",name="attendance_roll_group_post_api")
     */
    public function postRollGroupDetails(
        RollGroup $rollGroup,
        DateTimeImmutable $date,
        string $dailyTime = 'all_day'
    ) {

        $post = $this->getRequest()->request->get('attendance_roll_group');
        $submittedToken = $post['_token'];

        if ($this->isCsrfTokenValid('attendance_roll_group', $submittedToken)) {
            $staff = SecurityHelper::getCurrentUser()->getStaff();
            ProviderFactory::getEntityManager()->refresh($staff);
            $alrg = ProviderFactory::getRepository(AttendanceLogRollGroup::class)->findOneBy(['rollGroup' => $rollGroup, 'date' => $date, 'dailyTime' => $dailyTime]) ?: new AttendanceLogRollGroup($rollGroup, $date, $dailyTime);
            $alrg->setRecorder($staff);
            if ($alrg->getId() === null) ProviderFactory::create(AttendanceLogRollGroup::class)->persist($alrg);
            $codes = [];
            foreach ($post['students'] as $student) {
                $codes[$student['code']] = key_exists($student['code'],$codes) ? $codes[$student['code']] : ProviderFactory::getRepository(AttendanceCode::class)->find($student['code']);
                $studentEntity = ProviderFactory::getRepository(Student::class)->find($student['student']);
                $als = ProviderFactory::getRepository(AttendanceLogStudent::class)->findOneBy(['dailyTime' => $dailyTime, 'date' => $date, 'attendanceRollGroup' => $alrg, 'student' => $studentEntity]) ?: new AttendanceLogStudent();
                $als->setStudent($studentEntity)
                    ->setAttendanceRollGroup($alrg)
                    ->setCode($codes[$student['code']])
                    ->setDate($date)
                    ->setDailyTime($dailyTime)
                    ->setComment($student['comment'])
                    ->setRecorder($staff)
                    ->setReason($student['reason'])
                    ->setContext('Roll Group');
                ProviderFactory::create(AttendanceLogStudent::class)->persist($als);
            }
            ProviderFactory::create(AttendanceLogStudent::class)->flush();
        } else {
            $this->getStatusManager()->invalidInputs();
        }
        $this->getStatusManager()->convertToFlash();

        return $this->redirectToRoute('attendance_roll_group_manage', ['rollGroup' => $rollGroup->getId(), 'date' => $date->format('Y-m-d'), 'dailyTime' => $dailyTime]);
    }
}
