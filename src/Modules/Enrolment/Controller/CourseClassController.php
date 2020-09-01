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
 * Date: 31/08/2020
 * Time: 16:30
 */
namespace App\Modules\Enrolment\Controller;

use App\Controller\AbstractPageController;
use App\Modules\Enrolment\Entity\CourseClass;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class CourseClassController
 * @package App\Modules\Enrolment\Controller
 * @author Craig Rayner <craig@craigrayner.com>
 */
class CourseClassController extends AbstractPageController
{
    /**
     * edit
     *
     * 1/09/2020 09:16
     * @param CourseClass|null $class
     * @Route("/course/class/{class}/edit/",name="course_class_edit")
     * @Route("/course/class/{class}/delete/",name="course_class_delete")
     * @Route("/course/class/add/",name="course_class_add")
     * @IsGranted("ROLE_ROUTE")
     */
    public function edit(?CourseClass $class = null)
    {}
}