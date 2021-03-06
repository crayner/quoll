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
 * Time: 17:09
 */
namespace App\Modules\Attendance\Controller;

use App\Container\Container;
use App\Container\Panel;
use App\Container\Section;
use App\Controller\AbstractPageController;
use App\Modules\Attendance\Entity\AttendanceRecorderLog;
use App\Modules\Attendance\Form\AttendanceByStudentType;
use App\Modules\Attendance\Entity\AttendanceStudent;
use App\Modules\Attendance\Manager\AttendanceByStudentManager;
use App\Modules\Attendance\Pagination\AttendanceRecorderLogPagination;
use App\Modules\School\Util\AcademicYearHelper;
use App\Modules\Student\Entity\Student;
use App\Provider\ProviderFactory;
use App\Util\TranslationHelper;
use DateTimeImmutable;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class ByStudentController
 *
 * 27/10/2020 17:09
 * @package App\Modules\Attendance\Controller
 * @author Craig Rayner <craig@craigrayner.com>
 */
class ByStudentController extends AbstractPageController
{
    /**
     * byStudent
     *
     * 27/10/2020 17:12
     * @param AttendanceByStudentManager $manager
     * @param AttendanceRecorderLogPagination $pagination
     * @param string $dailyTime
     * @param Student|null $student
     * @param DateTimeImmutable|null $date
     * @return JsonResponse
     * @Route("/attendance/by/student/{dailyTime}/{date}/{student}",name="attendance_by_student")
     * @IsGranted("ROLE_TEACHER")
     */
    public function byStudent(AttendanceByStudentManager $manager, AttendanceRecorderLogPagination $pagination, string $dailyTime = 'all_day', ?Student $student = null, ?DateTimeImmutable $date = null)
    {
        $as = ProviderFactory::getRepository(AttendanceStudent::class)->findOneBy(['student' => $student, 'date' => $date, 'dailyTime' => $dailyTime]) ?: new AttendanceStudent();
        $date = $date ?: new DateTimeImmutable();
        $as->setDate($date)
            ->setDailyTime($dailyTime)
            ->setStudent($student)
            ->setContext('Person')
        ;

        $studentAccess = $student instanceof Student ? $this->isGranted('ROLE_STUDENT_ACCESS', $student) : false;

        $form = $this->createAttendanceByStudentForm($as, $studentAccess);

        if ($this->isPostContent()) {
            $content = $this->jsonDecode();
            if ($content['student'] !== ($student instanceof Student ? $student->getId() : '')) {
                $student = ProviderFactory::getRepository(Student::class)->find($content['student']);
                $as->setStudent($student);
                $form = $this->createAttendanceByStudentForm($as, $studentAccess);
            }

            if ($manager->isSelectionChanged($content, $this->getRequest())) {
                $manager->isSelectionValid($form, $content, $student);
                $this->getStatusManager()->setReDirect($this->generateUrl('attendance_by_student', ['student' => $content['student'], 'date' => $content['date'] !== '' ? $content['date'] : date('Y-m-d'), 'dailyTime' => $content['dailyTime'] === '' ? 'all_day' : $content['dailyTime']]), true);
            } else if ($manager->isSelectionValid($form,$content,$student)) {
                return $this->singleForm($manager->handleSubmit($form, $content));
            }

            return $this->singleForm($form);
        }
        $params = $this->getRequest()->get('_route_params');
        if (key_exists('student', $params) && $params['student'] !== null) {
            $manager->isSelectionValid($form, $params, $student);
        }

        $container = new Container();
        $panel = new Panel('single', 'Attendance', new Section('form', 'single'));
        $pagination->setContent(ProviderFactory::getRepository(AttendanceRecorderLog::class)->findBy(['logKey' => 'Student', 'logId' => $as ? $as->getId() : null],['recordedOn' => 'ASC']));
        if ($studentAccess) $panel->addSection(new Section('pagination', $pagination));
        $container->addForm('single', $form)
            ->addPanel(AcademicYearHelper::academicYearWarning($panel));

        return $this->getPageManager()
            ->createBreadcrumbs('Take Attendance by Student')
            ->setUrl($this->generateUrl('attendance_by_student', ['student' => $student ? $student->getId() : null, 'date' => $date->format('Y-m-d'), 'dailyTime' => $dailyTime]))
            ->render(
                [
                    'containers' => $this->getContainerManager()
                        ->addContainer($container)
                        ->getBuiltContainers()
                ]
            );
    }

    /**
     * createAttendanceByStudentForm
     *
     * 11/11/2020 10:13
     * @param AttendanceStudent $as
     * @param bool $studentAccess
     * @return FormInterface
     */
    private function createAttendanceByStudentForm(AttendanceStudent $as, bool $studentAccess): FormInterface
    {
        $date = $as->getDate();
        $dailyTime = $as->getDailyTime();
        $student = $as->getStudent();
        return $this->createForm(AttendanceByStudentType::class, $as,
            [
                'action' => $this->generateUrl('attendance_by_student', ['student' => $student ? $student->getId() : null, 'date' => $date->format('Y-m-d'), 'dailyTime' => $dailyTime]),
                'studentAccess' => $studentAccess,
            ]
        );
    }
}