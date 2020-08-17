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
 * Date: 22/06/2020
 * Time: 13:28
 */
namespace App\Modules\RollGroup\Controller;

use App\Container\ContainerManager;
use App\Controller\AbstractPageController;
use App\Manager\MessageStatusManager;
use App\Modules\RollGroup\Entity\RollGroup;
use App\Modules\RollGroup\Form\RollGroupType;
use App\Modules\RollGroup\Pagination\RollGroupListPagination;
use App\Modules\School\Entity\AcademicYear;
use App\Modules\School\Util\AcademicYearHelper;
use App\Provider\ProviderFactory;
use App\Twig\PageHeader;
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
     *
     * 17/08/2020 12:27
     * @param RollGroupListPagination $pagination
     * @Route("/roll/group/list/",name="roll_group_list")
     * @Route("/roll/group/list/",name="roll_group_catalogue")
     * @Route("/roll/group/list/",name="report_generate")
     * @IsGranted("ROLE_ROUTE")
     * @return JsonResponse
     */
    public function list(RollGroupListPagination $pagination)
    {
        $academicYear = AcademicYearHelper::getCurrentAcademicYear();
        $rollGroups = ProviderFactory::getRepository(RollGroup::class)->findBy(['academicYear' => $academicYear],['name' => 'ASC']);

        $pagination->setCurrentUser($this->getUser())
            ->setAddElementRoute($this->generateUrl('roll_group_add'))
            ->setContent($rollGroups);

        $pageHeader = new PageHeader('Roll Groups');
        $pageHeader->setContent(TranslationHelper::translate('This page shows all roll groups in the current academic year.', [], 'Roll Group'))
            ->setContentAttr(['className' => 'info']);

        return $this->getPageManager()
            ->setMessages($this->getMessageStatusManager()->getMessageArray())
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
     *
     * 17/08/2020 12:27
     * @param RollGroup|null $rollGroup
     * @Route("/roll/group/{rollGroup}/edit/", name="roll_group_edit")
     * @Route("/roll/group/add/", name="roll_group_add")
     * @IsGranted("ROLE_ROUTE")
     * @return JsonResponse
     */
    public function edit(?RollGroup $rollGroup = null)
    {
        if (!$rollGroup instanceof RollGroup) {
            $rollGroup = new RollGroup();
            $rollGroup->setAcademicYear(AcademicYearHelper::getCurrentAcademicYear());
            $action = $this->generateUrl('roll_group_add');
        } else {
            $action = $this->generateUrl('roll_group_edit', ['rollGroup' => $rollGroup->getId()]);
        }

        $form = $this->createForm(RollGroupType::class, $rollGroup, ['action' => $action]);
        $manager = $this->getContainerManager();

        if ($this->getRequest()->getContent() !== '') {
            $content = json_decode($this->getRequest()->getContent(), true);
            $form->submit($content);
            if ($form->isValid()) {
                $id = $rollGroup->getId();
                $provider = ProviderFactory::create(RollGroup::class);
                $year = AcademicYearHelper::getCurrentAcademicYear();
                $year = ProviderFactory::getRepository(AcademicYear::class)->find($year->getId());
                $rollGroup->setAcademicYear($year);
                $provider->persistFlush($rollGroup);
                if ($this->getMessageStatusManager()->isStatusSuccess()) {
                    $form = $this->createForm(RollGroupType::class, $rollGroup, ['action' => $this->generateUrl('roll_group_edit', ['rollGroup' => $rollGroup->getId()])]);
                    if ($id !== $rollGroup->getId()) {
                        $this->getMessageStatusManager()
                            ->setReDirect($this->generateUrl('roll_group_edit', ['rollGroup' => $rollGroup->getId()]))
                            ->convertToFlash();
                    }
                }
            } else {
                $this->getMessageStatusManager()->error(MessageStatusManager::INVALID_INPUTS);
            }

            $manager->singlePanel($form->createView());
            return $this->getMessageStatusManager()->toJsonResponse(['form' => $manager->getFormFromContainer()]);
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
     *
     * 17/08/2020 12:28
     * @param RollGroup $rollGroup
     * @param RollGroupListPagination $pagination
     * @Route("/roll/group/{rollGroup}/delete/", name="roll_group_delete")
     * @IsGranted("ROLE_ROUTE")
     * @return JsonResponse
     */
    public function delete(RollGroup $rollGroup, RollGroupListPagination $pagination)
    {
       ProviderFactory::create(RollGroup::class)
            ->delete($rollGroup);

        return $this->list($pagination);
    }
}