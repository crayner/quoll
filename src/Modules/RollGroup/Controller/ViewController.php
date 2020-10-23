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

use App\Container\Container;
use App\Container\ContainerManager;
use App\Container\Panel;
use App\Container\Section;
use App\Controller\AbstractPageController;
use App\Modules\People\Entity\Person;
use App\Modules\RollGroup\Entity\RollGroup;
use App\Modules\RollGroup\Form\DetailStudentSortType;
use App\Modules\RollGroup\Pagination\RollGroupStudentsPagination;
use App\Modules\School\Util\AcademicYearHelper;
use App\Modules\Security\Util\SecurityHelper;
use App\Modules\Student\Entity\Student;
use App\Provider\ProviderFactory;
use App\Twig\SidebarContent;
use App\Twig\Sidebar\Photo;
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
     *
     * 18/10/2020 09:45
     * @param RollGroup $rollGroup
     * @param RollGroupStudentsPagination $pagination
     * @Route("/roll/group/{rollGroup}/detail/",name="roll_group_detail")
     * @IsGranted("ROLE_ROUTE")
     * @return JsonResponse
     */
    public function detail(RollGroup $rollGroup, RollGroupStudentsPagination $pagination)
    {
        if ($rollGroup->getTutor()) {
            $image = new Photo($rollGroup->getTutor()->getPerson()->getPersonalDocumentation(), 'getPersonalImage', 200, 'max200 user','/build/static/DefaultPerson.png');
            $this->getPageManager()
                ->getSidebar()
                ->addContent($image->setTitle($rollGroup->getTutor()->getFullName()));
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

        $section = new Section('html', $this->renderView('roll_group/details.html.twig',
                [
                    'rollGroup' => $rollGroup,
                    'staffView' => SecurityHelper::isActionAccessible('staff_view_details'),
                    'sortBy' => $sortBy,
                    'form' => $form->createView(),
                    'canPrint' => $canPrint,
                    'canViewStudents' => $canViewStudents,
                ]
            )
        );

        $panel = new Panel('Roll Group', 'RollGroup', $section);

        $container = new Container('Roll Group');
        $container->addForm('Roll Group', $form->createView());

        if ($canViewStudents) {
            $pagination->setContent(ProviderFactory::getRepository(Student::class)->findByRollGroup($rollGroup, $sortBy));
            $panel->addSection(new Section('pagination', $pagination));
        }
        $container->addPanel(AcademicYearHelper::academicYearWarning($panel));

        return $this->getPageManager()
            ->createBreadcrumbs(['View Roll Group {name}', ['{name}' => $rollGroup->getName()]],
                [
                    ['uri' => 'roll_group_list', 'name' => 'Roll Groups'],
                ]
            )
            ->render(['containers' => $this->getContainerManager()
                ->addContainer($container)
                ->getBuiltContainers()]);
    }
}