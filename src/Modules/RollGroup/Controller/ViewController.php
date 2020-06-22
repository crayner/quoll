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
 * Date: 17/06/2020
 * Time: 12:23
 */
namespace App\Modules\RollGroup\Controller;

use App\Container\ContainerManager;
use App\Controller\AbstractPageController;
use App\Modules\People\Entity\Person;
use App\Modules\RollGroup\Entity\RollGroup;
use App\Modules\RollGroup\Form\DetailStudentSortType;
use App\Modules\RollGroup\Pagination\RollGroupListPagination;
use App\Modules\School\Util\AcademicYearHelper;
use App\Modules\Security\Util\SecurityHelper;
use App\Provider\ProviderFactory;
use App\Twig\PageHeader;
use App\Twig\Sidebar\Photo;
use App\Twig\SidebarContent;
use App\Util\TranslationHelper;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class ViewController
 * @package App\Modules\RollGroup\Controller
 * @author Craig Rayner <craig@craigrayner.com>
 */
class ViewController extends AbstractPageController
{
    /**
     * detail
     * @param RollGroup $rollGroup
     * @param SidebarContent $sidebar
     * @param ContainerManager $manager
     * @return Response
     * @Route("/roll/group/{rollGroup}/detail/",name="roll_group_detail")
     * @IsGranted("ROLE_ROUTE")
     * @todo This method is not finished.
     * 17/06/2020 12:39
     */
    public function detail(RollGroup $rollGroup,SidebarContent $sidebar, ContainerManager $manager)
    {
        if ($rollGroup->getTutor()) {
            $image = new Photo($rollGroup->getTutor(), 'getImage240', 200, 'max200 user');
            $sidebar->addContent($image);
        }

        $canPrint = SecurityHelper::isActionAccessible('report_students_roll_group_print');

        $canViewStudents = $this->isGranted('ROLE_ROLL_GROUP', $rollGroup);
        $this->isGranted('ROLE_STUDENT_PROFILE');

        $sortBy = $this->getRequest()->request->has('detail_student_sort') ? $this->getRequest()->request->get('detail_student_sort')['sortBy'] : 'rollOrder';

        $form = $this->createForm(DetailStudentSortType::class, null,
            [
                'action' => $this->generateUrl('roll_group_detail', ['rollGroup' => $rollGroup->getId()])
            ]
        );
        $form->handleRequest($this->getRequest());

        $manager
            ->setContent($this->renderView('roll_group/details.html.twig',
                [
                    'rollGroup' => $rollGroup,
                    'staffView' => SecurityHelper::isActionAccessible('staff_view_details'),
                    'sortBy' => $sortBy,
                    'form' => $form->createView(),
                    'canPrint' => $canPrint,
                    'canViewStudents' => $canViewStudents,
                    'students' => ProviderFactory::getRepository(Person::class)->findStudentsByRollGroup($rollGroup, $sortBy),
                ]
            )
        );

        if ($canViewStudents) {
            $manager->singlePanel($form->createView());
        }

        return $this->getPageManager()
            ->createBreadcrumbs('Roll Groups')
            ->render(['containers' => $manager->getBuiltContainers()]);
    }
}