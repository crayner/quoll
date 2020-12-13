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
 * Date: 2/10/2020
 * Time: 15:58
 */
namespace App\Modules\Attendance\Controller;

use App\Controller\AbstractPageController;
use App\Modules\Attendance\Form\AttendanceByClassType;
use App\Modules\Attendance\Manager\AttendanceByClassManager;
use App\Modules\Enrolment\Entity\CourseClass;
use App\Modules\Timetable\Entity\TimetablePeriodClass;
use DateTimeImmutable;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class ByClassController
 * @package App\Modules\Attendance\Controller
 * @author Craig Rayner <craig@craigrayner.com>
 */
class ByClassController extends AbstractPageController
{
    /**
     * byClass
     *
     * 12/11/2020 09:13
     * @param AttendanceByClassManager $manager
     * @param CourseClass|null $courseClass
     * @param DateTimeImmutable|null $date
     * @return JsonResponse
     * @Route("/attendance/by/class/{date}/{courseClass}",name="attendance_by_course_class")
     * @IsGranted("ROLE_ROUTE")
     */
    public function byClass(AttendanceByClassManager $manager, ?CourseClass $courseClass = null, ?DateTimeImmutable $date = null)
    {
        $manager->setDate($date ?: new DateTimeImmutable())
            ->setCourseClass($courseClass);

        $form = $this->createForm(AttendanceByClassType::class, $manager, ['action' => $this->generateUrl('attendance_by_course_class',
            [
                'courseClass' => $manager->getCourseClass() ? $manager->getCourseClass()->getId() : null,
                'date' => $manager->getDate()->format('Y-m-d'),
            ]
        )]);

        if ($this->isPostContent()) {
            $content = $this->jsonDecode();
            $form->submit($content);
            if ($form->isValid() && $manager->isSelectionChanged($this->getRequest()->attributes->get('_route_params'))) {
                $this->getStatusManager()->warning('A change was made in the attendance selection.  No data has been saved.', [], 'Attendance');
                $this->getStatusManager()->setReDirect($this->generateUrl('attendance_by_course_class', ['date' => $manager->getDate()->format('Y-m-d'),'courseClass' => $manager->getCourseClass() ? $manager->getCourseClass()->getId() : null]), true);
                return $this->singleForm($form);
            }
            if ($form->isValid()) {
                $manager->handleForm($form, $this->getStatusManager());
                if ($this->isStatusSuccess()) {
                    $this->getStatusManager()->setReDirect($this->generateUrl('attendance_by_course_class',
                        [
                            'courseClass' => $manager->getCourseClass() ? $manager->getCourseClass()->getId() : null,
                            'date' => $manager->getDate()->format('Y-m-d'),
                        ]
                    ), true);
                }
            } else {
                $this->getStatusManager()->invalidInputs();
            }
            return $this->singleForm($form);
        }

        return $this->getPageManager()
            ->createBreadcrumbs('Take Attendance by Class')
            ->render(
                [
                    'containers' => $this->getContainerManager()
                        ->singlePanel($form)
                        ->getBuiltContainers(),
                ]
            )
        ;
    }
}
