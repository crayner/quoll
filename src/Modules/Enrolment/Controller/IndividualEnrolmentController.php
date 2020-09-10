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
 * Date: 10/09/2020
 * Time: 13:18
 */
namespace App\Modules\Enrolment\Controller;

use App\Container\Container;
use App\Container\Panel;
use App\Container\Section;
use App\Controller\AbstractPageController;
use App\Modules\Enrolment\Entity\CourseClassPerson;
use App\Modules\Enrolment\Pagination\IndividualEnrolmentPagination;
use App\Modules\People\Entity\Person;
use App\Modules\School\Util\AcademicYearHelper;
use App\Modules\Student\Entity\Student;
use App\Provider\ProviderFactory;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class IndividualEnrolmentController
 * @package App\Modules\Enrolment\Controller
 * @author Craig Rayner <craig@craigrayner.com>
 */
class IndividualEnrolmentController extends AbstractPageController
{
    /**
     * individualEnrolment
     *
     * 10/09/2020 13:20
     * @param IndividualEnrolmentPagination $pagination
     * @Route("/individual/enrolment/list/",name="individual_enrolment_list")
     * @IsGranted("ROLE_ROUTE")
     * @return JsonResponse
     */
    public function individualEnrolment(IndividualEnrolmentPagination $pagination)
    {
        $pagination->setContent(ProviderFactory::create(Person::class)->getIndividualEnrolmentPaginationContent())
            ->setPageMax(50);

        $container = new Container();
        $panel = new Panel('null', 'Enrolment', new Section('html', $this->renderView('enrolment/individual_search_warning.html.twig')));
        $panel->addSection(new Section('pagination', $pagination));
        $container->addPanel(AcademicYearHelper::academicYearWarning($panel));

        return $this->getPageManager()
            ->createBreadcrumbs('Individual Enrolment')
            ->setUrl($this->generateUrl('student_enrolment_list'))
            ->setMessages($this->getStatusManager()->getMessageArray())
            ->render([
                'containers' => $this->getContainerManager()
                    ->addContainer($container)
                    ->getBuiltContainers(),
            ]);
    }

    /**
     * manageIndividualEnrolment
     *
     * 10/09/2020 15:28
     * @Route("/individual/enrolment/{person}/manage/",name="individual_enrolment_manage")
     * @IsGranted("ROLE_ROUTE")
     * @param Person $person
     */
    public function manageIndividualEnrolment(Person $person){}
}
