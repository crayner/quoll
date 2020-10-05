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
use App\Modules\Attendance\Manager\Hidden\AttendanceByClass;
use App\Modules\Enrolment\Entity\CourseClass;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
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
     * 2/10/2020 15:58
     * @Route("/attendance/by/class/on/date/",name="attendance_by_class")
     * @IsGranted("ROLE_ROUTE")
     */
    public function byClass()
    {
        $attendanceByClass = new AttendanceByClass();
        $form = $this->createForm(AttendanceByClassType::class, $attendanceByClass, ['action' => $this->generateUrl('attendance_by_class')]);

        if ($this->isPostContent()) {
            $content = $this->jsonDecode();
            $form->submit($content);
            if ($form->isValid()) {

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
            );
    }
}
