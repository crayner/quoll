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
use App\Modules\Attendance\Form\AttendanceByRollGroupType;
use App\Modules\Attendance\Manager\AttendanceByRollGroupManager;
use App\Modules\RollGroup\Entity\RollGroup;
use App\Modules\School\Util\AcademicYearHelper;
use DateTimeImmutable;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

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
     * @param DateTimeImmutable|null $date
     * @param RollGroup|null $rollGroup
     * @param string|null $dailyTime
     * @return JsonResponse
     * @Route("/attendance/roll/group/manager/{rollGroup}/{date}/{dailyTime}",name="attendance_roll_group_manage")
     * @IsGranted("ROLE_ROUTE")
     */
    public function manage(
        AttendanceByRollGroupManager $manager,
        ?DateTimeImmutable $date = null,
        ?RollGroup $rollGroup = null,
        ?string $dailyTime = null
    ) {
        $manager->setDate($date)
            ->setRollGroup($rollGroup)
            ->setDailyTime($dailyTime);

        $form = $this->createForm(AttendanceByRollGroupType::class, $manager,
            [
                'action' => $this->generateUrl('attendance_roll_group_manage',
                    [
                        'date' => $date ? $date->format('Y-m-d') : null,
                        'dailyTime' => $dailyTime,
                        'rollGroup' => $rollGroup ? $rollGroup->getId() : null
                    ]
                )
            ]
        );

        $submitClicked = '';
        if ($this->isPostContent()) {
            $content = $this->jsonDecode();
            if (key_exists('submit_clicked', $content)) {
                $submitClicked = $content['submit_clicked'];
                unset($content['submit_clicked']);
            }
            if (key_exists('students',$content)) {
                $students = [];
                foreach ($content['students'] as $q=>$w) {
                    foreach ($w as $name=>$value) {
                        if (!in_array($name, ['inOrOut','previousDays'])) {
                            $students[$q][$name] = $value;
                        }
                    }
                }
                $content['students'] = $students;
            }

            if ($submitClicked === 'changeAll') {
                $manager->changeAll($content['changeAll'], $this->getStatusManager());
                $this->getStatusManager()->setReDirect($this->generateUrl('attendance_roll_group_manage', ['rollGroup' => $rollGroup->getId(), 'date' => $date->format('Y-m-d'), 'dailyTime' => $dailyTime]), true);

                return $this->singleForm($form);
            }

            $form->submit($content);
            if ($form->isValid()) {
                if ($manager->requestEqualsSubmit($this->getRequest()->attributes->get('_route_params'))) {
                    $manager->storeAttendance($content);
                    $manager->getStudents();
                    $form = $this->createForm(AttendanceByRollGroupType::class, $manager,
                        [
                            'action' => $this->generateUrl('attendance_roll_group_manage',
                                [
                                    'date' => $date ? $date->format('Y-m-d') : null,
                                    'dailyTime' => $dailyTime,
                                    'rollGroup' => $rollGroup ? $rollGroup->getId() : null
                                ]
                            )
                        ]
                    );
                    $this->getStatusManager()->setReDirect($this->generateUrl('attendance_roll_group_manage', ['rollGroup' => $rollGroup->getId(), 'date' => $date->format('Y-m-d'), 'dailyTime' => $dailyTime]), true);
                } else {
                    $this->getStatusManager()
                        ->invalidInputs()
                        ->setReDirect($this->generateUrl('attendance_roll_group_manage', ['date' => $manager->getDate()->format('Y-m-d'), 'rollGroup' => $manager->getRollGroup()->getId(), 'dailyTime' => $manager->getDailyTime()]));
                }
            }
            return $this->singleForm($form);
        }

        if ($manager->isValid()) {
            $manager->getStudents();
            $form = $this->createForm(AttendanceByRollGroupType::class, $manager,
                [
                    'action' => $this->generateUrl('attendance_roll_group_manage',
                        [
                            'date' => $date ? $date->format('Y-m-d') : null,
                            'dailyTime' => $dailyTime,
                            'rollGroup' => $rollGroup ? $rollGroup->getId() : null
                        ]
                    )
                ]
            );
        }

        $container = new Container();
        $panel = new Panel('single', 'Attendance', new Section('html', $this->renderView('attendance/attendance_roll_group_status.html.twig', ['manager' => $manager])));

        $panel->addSection(new Section('form','single'));
        $container->addForm('single', $form)
            ->addPanel(AcademicYearHelper::academicYearWarning($panel));

        return $this->getPageManager()
            ->createBreadcrumbs('Take Attendance by Roll Group')
            ->setUrl($this->generateUrl('attendance_roll_group_manage', $rollGroup ? ['rollGroup' => $rollGroup->getId(), 'date' => $date->format('Y-m-d'), 'dailyTime' => $dailyTime] : []))
            ->render(
                [
                    'containers' => $this->getContainerManager()
                        ->setShowSubmitButton()
                        ->addContainer($container)
                        ->getBuiltContainers(),
                ]
            )
        ;
    }
}
