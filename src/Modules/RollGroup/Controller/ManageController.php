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

use App\Container\Container;
use App\Container\Panel;
use App\Container\Section;
use App\Controller\AbstractPageController;
use App\Manager\StatusManager;
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
        $pageHeader->setContent(TranslationHelper::translate('This page shows all roll groups in the currently selected academic year.', [], 'Roll Group'))
            ->setContentAttr(['className' => 'info']);

        $container = new Container();
        $panel = new Panel('RollGroupList', 'RollGroup', new Section('pagination', $pagination));
        $container->addPanel(AcademicYearHelper::academicYearWarning($panel));

        return $this->getPageManager()
            ->setMessages($this->getStatusManager()->getMessageArray())
            ->setPageHeader($pageHeader)
            ->createBreadcrumbs('Roll Groups')
            ->setTitle('Roll Groups')
            ->setUrl($this->generateUrl('roll_group_list'))
            ->renderContainer($container);
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
                if ($this->getStatusManager()->isStatusSuccess()) {
                    $form = $this->createForm(RollGroupType::class, $rollGroup, ['action' => $this->generateUrl('roll_group_edit', ['rollGroup' => $rollGroup->getId()])]);
                    if ($id !== $rollGroup->getId()) {
                        $this->getStatusManager()
                            ->setReDirect($this->generateUrl('roll_group_edit', ['rollGroup' => $rollGroup->getId()]))
                            ->convertToFlash();
                    }
                }
            } else {
                $this->getStatusManager()->error(StatusManager::INVALID_INPUTS);
            }

            return $this->singleForm($form);
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

    /**
     * duplicate
     *
     * 9/10/2020 10:15
     * @param RollGroup $rollGroup
     * @param RollGroupListPagination $pagination
     * @Route("/roll/group/{rollGroup}/duplicate/",name="roll_group_duplicate")
     * @IsGranted("ROLE_ROUTE")
     * @return JsonResponse
     */
    public function duplicate(RollGroup $rollGroup, RollGroupListPagination $pagination)
    {
        if (!$rollGroup->canDuplicate()) {
            $this->getStatusManager()->warning('The Roll Group "{name}" is not available to copy to the next academic year.  Clear the "Next Roll Group" if set to allow this function.', ['{name}' => $rollGroup->getName()], 'RollGroup');
            $this->getStatusManager()->setReDirect($this->generateUrl('roll_group_list'), true);
            return $this->list($pagination);
        }

        $rg = new RollGroup();
        $rg->setAcademicYear(AcademicYearHelper::getNextAcademicYear())
            ->setYearGroup($rollGroup->getYearGroup()->getNextYearGroup())
            ->setTutor($rollGroup->getTutor())
            ->setTutor2($rollGroup->getTutor2())
            ->setTutor3($rollGroup->getTutor3())
            ->setAssistant($rollGroup->getAssistant())
            ->setAssistant2($rollGroup->getAssistant2())
            ->setAssistant3($rollGroup->getAssistant3())
            ->setFacility($rollGroup->getFacility())
            ->setAttendance($rollGroup->isAttendance())
            ->setWebsite($rollGroup->getWebsite())
            ->setName(TranslationHelper::translate('Copy', [], 'RollGroup') . ' ' . $rollGroup->getName())
            ->setAbbreviation(TranslationHelper::translate('Copy', [], 'RollGroup') . ' ' . $rollGroup->getAbbreviation())
        ;

        $form = $this->createForm(RollGroupType::class, $rg, ['action' => $this->generateUrl('roll_group_duplicate', ['rollGroup' => $rollGroup->getId()])]);

        if ($this->isPostContent()) {
            $content = $this->jsonDecode();
            $form->submit($content);
            if ($form->isValid()) {
                $rollGroup->setNextRollGroup($rg);
                ProviderFactory::create(RollGroup::class)->persist($rg);
                ProviderFactory::create(RollGroup::class)->persistFlush($rollGroup);
                $this->getStatusManager()->setReDirect($this->generateUrl('roll_group_list'), true);
            } else {
                $this->getStatusManager()->invalidInputs();
            }
            return $this->singleForm($form);
        }

        return $this->getPageManager()
            ->createBreadcrumbs('Copy Roll Group')
            ->render(
                [
                    'containers' => $this->getContainerManager()
                        ->singlePanel($form)
                        ->getBuiltContainers()
                ]
            );
    }
}
