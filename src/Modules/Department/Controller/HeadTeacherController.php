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
 * Date: 3/10/2020
 * Time: 14:24
 */
namespace App\Modules\Department\Controller;

use App\Container\Container;
use App\Container\Panel;
use App\Container\Section;
use App\Controller\AbstractPageController;
use App\Modules\Department\Entity\Department;
use App\Modules\Department\Entity\HeadTeacher;
use App\Modules\Department\Form\HeadTeacherType;
use App\Modules\Department\Pagination\HeadTeacherPagination;
use App\Modules\School\Entity\YearGroup;
use App\Provider\ProviderFactory;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class HeadTeacherController
 * @package App\Modules\Department\Controller
 * @author Craig Rayner <craig@craigrayner.com>
 */
class HeadTeacherController extends AbstractPageController
{
    /**
     * manage
     *
     * 3/10/2020 14:25
     * @Route("/head/teacher/manage/",name="head_teacher_manage")
     * @param HeadTeacherPagination $pagination
     * @return JsonResponse
     */
    public function manage(HeadTeacherPagination $pagination)
    {
        $pagination->setContent(ProviderFactory::getRepository(HeadTeacher::class)->findHeadTeacherPaginationContent())
            ->setAddElementRoute($this->generateUrl('head_teacher_add'));

        $container = new Container();
        $panel = new Panel('null', 'Department', new Section('pagination', $pagination));
        $container->addPanel($panel);

        return $this->getPageManager()
            ->createBreadcrumbs('Manage Head Teachers')
            ->setMessages($this->getStatusManager()->getMessageArray())
            ->setUrl($this->generateUrl('head_teacher_manage'))
            ->render([
                'containers' => $this->getContainerManager()
                    ->addContainer($container)
                    ->getBuiltContainers(),
            ]);
    }

    /**
     * edit
     *
     * 5/10/2020 09:29
     * @param HeadTeacher|null $headTeacher
     * @Route("/head/teacher/add/",name="head_teacher_add")
     * @Route("/head/teacher/{headTeacher}/edit/",name="head_teacher_edit")
     * @IsGranted("ROLE_ROUTE")
     * @return JsonResponse
     */
    public function edit(?HeadTeacher $headTeacher = null)
    {
        $headTeacher = $headTeacher ?: new HeadTeacher();
        $action = $this->generateUrl('head_teacher_add');
        if ($headTeacher->getId()) {
            $action = $this->generateUrl('head_teacher_edit', ['headTeacher' => $headTeacher->getId()]);
            $this->getContainerManager()->setAddElementRoute($this->generateUrl('head_teacher_add'));
        }

        $form = $this->createForm(HeadTeacherType::class, $headTeacher, ['action' => $action]);

        if ($this->isPostContent()) {
            $content = $this->jsonDecode();
            $form->submit($content);
            if ($form->isValid()) {
                if ($form->get('department')->getData() instanceof Department) {
                    ProviderFactory::create(HeadTeacher::class)->addDepartmentClasses($form->get('department')->getData(), $headTeacher);
                    if ($this->isStatusSuccess()) $form = $this->createForm(HeadTeacherType::class, $headTeacher, ['action' => $action]);
                }
                if ($form->get('yearGroup')->getData() instanceof YearGroup) {
                    ProviderFactory::create(HeadTeacher::class)->addYearGroupClasses($form->get('yearGroup')->getData(), $headTeacher);
                    if ($this->isStatusSuccess()) $form = $this->createForm(HeadTeacherType::class, $headTeacher, ['action' => $action]);
                }
                $id = $headTeacher->getId();
                ProviderFactory::create(HeadTeacher::class)->persistFlush($headTeacher);
                if ($id !== $headTeacher->getId()) {
                    $this->getStatusManager()->setReDirect($this->generateUrl('head_teacher_edit', ['headTeacher' => $headTeacher->getId()]), true);
                }
            } else {
                $this->getStatusManager()->invalidInputs();
            }

            return $this->singleForm($form);
        }

        return $this->getPageManager()
            ->createBreadcrumbs($headTeacher->getId() ? 'Edit Head Teacher' : 'Add Head Teacher',
                [
                    ['name' => 'Manage Head Teachers', 'uri' => 'head_teacher_manage']
                ]
            )
            ->render(
                [
                    'containers' => $this->getContainerManager()
                        ->singlePanel($form->createView())
                        ->setReturnRoute($this->generateUrl('head_teacher_manage'))
                        ->getBuiltContainers(),
                ]
            )
        ;
    }

    /**
     * delete
     *
     * 5/10/2020 09:30
     * @param HeadTeacher $headTeacher
     * @param HeadTeacherPagination $pagination
     * @Route("/head/teacher/{headTeacher}/remove/",name="head_teacher_delete")
     * @IsGranted("ROLE_ROUTE")
     * @return JsonResponse
     */
    public function delete(HeadTeacher $headTeacher, HeadTeacherPagination $pagination)
    {
        ProviderFactory::create(HeadTeacher::class)->delete($headTeacher);

        return $this->manage($pagination);
    }
}
