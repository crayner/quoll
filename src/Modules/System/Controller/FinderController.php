<?php
/**
 * Created by PhpStorm.
 *
 * Quoll
 * (c) 2020 Craig Rayner <craig@craigrayner.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * User: craig
 * Date: 20/04/2020
 * Time: 09:18
 */

namespace App\Modules\System\Controller;

use App\Controller\AbstractPageController;
use App\Modules\Enrolment\Entity\CourseClass;
use App\Provider\ProviderFactory;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class FinderController
 * @package App\Modules\System\Controller
 */
class FinderController extends AbstractPageController
{
    /**
     * finderRedirect
     * @Route("/finder/{id}/redirect/",name="finder_api_redirect", methods={"GET"})
     * @param string $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function finderRedirect(string $id)
    {
        $id = base64_decode($id);
        $type = substr($id, 0, 3);
        $id = substr($id, 4);

        switch ($type) {
            case 'Stu':
                dd($id);
                return $this->redirectToRoute('legacy', ['q' => '/modules/Students/student_view_details.php', 'gibbonPersonID' => $id]);
                break;
            case 'Act':
                $id = explode('/', $id);
                return $this->redirectToRoute($id[1]);
                break;
            case 'Sta':
                dd($id);
                return $this->redirectToRoute('legacy', ['q' => '/modules/Staff/staff_view_details.php', 'gibbonPersonID' => $id]);
                break;
            case 'Cla':
                $class = ProviderFactory::getRepository(CourseClass::class)->find($id);
                dd($class);
                return $this->redirectToRoute('course_class_details', ['class' => $class->getId(), 'course' => $class->getCourse()->getId(), 'department' => $class->getCourse()->getDepartment()->getId()]);
                break;
            default:
                throw new Exception(sprintf('The finder search failed for the unknown type of "%s".', $type));
        }
    }
}