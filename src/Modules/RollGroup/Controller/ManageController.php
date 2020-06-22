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
 * Date: 22/06/2020
 * Time: 13:28
 */
namespace App\Modules\RollGroup\Controller;

use App\Container\ContainerManager;
use App\Controller\AbstractPageController;
use App\Modules\RollGroup\Entity\RollGroup;
use App\Modules\RollGroup\Form\RollGroupType;
use App\Modules\RollGroup\Pagination\RollGroupListPagination;
use App\Modules\School\Entity\AcademicYear;
use App\Modules\School\Util\AcademicYearHelper;
use App\Provider\ProviderFactory;
use App\Twig\PageHeader;
use App\Util\ErrorMessageHelper;
use App\Util\TranslationHelper;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class ManageController
 * @package App\Modules\RollGroup\Controller
 * @author Craig Rayner <craig@craigrayner.com>
 */
class ManageController extends AbstractPageController
{
    /**
     * list
     * @param RollGroupListPagination $pagination
     * @return JsonResponse
     * @Route("/roll/group/list/",name="roll_group_list")
     * @Route("/roll/group/list/",name="roll_group_catalogue")
     * @Route("/roll/group/list/",name="report_generate")
     * @IsGranted("ROLE_ROUTE")
     * 17/06/2020 12:30
     */
    public function list(RollGroupListPagination $pagination)
    {
        $academicYear = AcademicYearHelper::getCurrentAcademicYear();
        $rollGroups = ProviderFactory::getRepository(RollGroup::class)->findBy(['academicYear' => $academicYear],['name' => 'ASC']);

        $pagination->setCurrentUser($this->getUser())
            ->setContent($rollGroups);

        $pageHeader = new PageHeader('Roll Groups');
        $pageHeader->setContent(TranslationHelper::translate('This page shows all roll groups in the current academic year.', [], 'Roll Group'))
            ->setContentAttr(['className' => 'info']);
        return $this->getPageManager()
            ->setPageHeader($pageHeader)
            ->createBreadcrumbs('Roll Groups')
            ->setUrl($this->generateUrl('roll_group_list'))
            ->render(
                [
                    'pagination' => $pagination->toArray(),
                ]
            );
    }
    /**
     * edit
     * @Route("/roll/group/{rollGroup}/edit/", name="roll_group_edit")
     * @Route("/roll/group/add/", name="roll_group_add")
     * @IsGranted("ROLE_ROUTE")
     * @param ContainerManager $manager
     * @param RollGroup|null $rollGroup
     * @return JsonResponse
     */
    public function edit(ContainerManager $manager, ?RollGroup $rollGroup = null)
    {
        if (!$rollGroup instanceof RollGroup) {
            $rollGroup = new RollGroup();
            $rollGroup->setAcademicYear(AcademicYearHelper::getCurrentAcademicYear());
            $action = $this->generateUrl('roll_group_add');
        } else {
            $action = $this->generateUrl('roll_group_edit', ['rollGroup' => $rollGroup->getId()]);
        }

        $form = $this->createForm(RollGroupType::class, $rollGroup, ['action' => $action]);

        if ($this->getRequest()->getContent() !== '') {
            $content = json_decode($this->getRequest()->getContent(), true);
            $form->submit($content);
            $data = [];
            $data['status'] = 'success';
            if ($form->isValid()) {
                $id = $rollGroup->getId();
                $provider = ProviderFactory::create(RollGroup::class);
                $year = AcademicYearHelper::getCurrentAcademicYear();
                $year = ProviderFactory::getRepository(AcademicYear::class)->find($year->getId());
                $rollGroup->setAcademicYear($year);
                $data = $provider->persistFlush($rollGroup, $data);
                if ($data['status'] === 'success') {
                    $form = $this->createForm(RollGroupType::class, $rollGroup, ['action' => $this->generateUrl('roll_group_edit', ['rollGroup' => $rollGroup->getId()])]);
                    if ($id !== $rollGroup->getId()) {
                        $data['status'] = 'redirect';
                        $data['redirect'] = $this->generateUrl('roll_group_edit', ['rollGroup' => $rollGroup->getId()]);
                        $this->addFlash('success', ErrorMessageHelper::onlySuccessMessage(true));
                    }
                }
            } else {
                $data = ErrorMessageHelper::getInvalidInputsMessage($data, true);
            }

            $manager->singlePanel($form->createView());
            $data['form'] = $manager->getFormFromContainer();

            return new JsonResponse($data);
        }

        $manager->setReturnRoute($this->generateUrl('roll_group_list'))
            ->singlePanel($form->createView());
        if (null !== $rollGroup->getId()) {
            $manager->setAddElementRoute($this->generateUrl('roll_group_add'));
        }

        return $this->getPageManager()
            ->createBreadcrumbs($rollGroup->getId() === null ? 'New Roll Group' : ['Roll Group {rollGroup}', ['{rollGroup}' => $rollGroup->getName()]])
            ->render(['containers' => $manager->getBuiltContainers()]);
    }

    /**
     * delete
     * @Route("/roll/group/{rollGroup}/delete/", name="roll_group_delete")
     * @IsGranted("ROLE_ROUTE")
     * @param RollGroup $rollGroup
     * @param RollGroupListPagination $pagination
     * @return JsonResponse
     */
    public function delete(RollGroup $rollGroup, RollGroupListPagination $pagination)
    {
        $provider = ProviderFactory::create(RollGroup::class);

        $provider->delete($rollGroup);

        $data = $provider->getMessageManager()->pushToJsonData();

        return $this->list($pagination);
    }
}