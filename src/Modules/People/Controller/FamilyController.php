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
 * Date: 27/04/2020
 * Time: 13:51
 */
namespace App\Modules\People\Controller;

use App\Container\Container;
use App\Container\Panel;
use App\Container\Section;
use App\Controller\AbstractPageController;
use App\Manager\EntitySortManager;
use App\Modules\People\Entity\Family;
use App\Modules\People\Entity\FamilyMemberCareGiver;
use App\Modules\People\Entity\FamilyMemberStudent;
use App\Modules\People\Form\FamilyCareGiverType;
use App\Modules\People\Form\FamilyGeneralType;
use App\Modules\People\Form\FamilyStudentType;
use App\Modules\People\Form\RelationshipsType;
use App\Modules\People\Manager\FamilyManager;
use App\Modules\People\Manager\FamilyRelationshipManager;
use App\Modules\People\Pagination\FamilyCareGiversPagination;
use App\Modules\People\Pagination\FamilyPagination;
use App\Modules\People\Pagination\FamilyStudentsPagination;
use App\Provider\ProviderFactory;
use App\Util\TranslationHelper;
use Exception;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class FamilyController
 * @package App\Modules\People\Controller
 * @author Craig Rayner <craig@craigrayner.com>
 */
class FamilyController extends AbstractPageController
{
    /**
     * familyManage
     *
     * 21/08/2020 10:39
     * @param FamilyPagination $pagination
     * @Route("/family/list/",name="family_list")
     * @IsGranted("ROLE_ROUTE")
     * @return JsonResponse
     */
    public function familyManage(FamilyPagination $pagination)
    {
        $pagination->setStack($this->getPageManager()->getStack())
            ->setAddElementRoute($this->generateUrl('family_add'))
            ->setStoreFilterURL($this->generateUrl('family_filter_store'))
            ->setContentLoader($this->generateUrl('family_content_loader'));

        return $this->getPageManager()->createBreadcrumbs('Manage Families')
            ->setUrl($this->generateUrl('family_list'))
            ->render(['pagination' => $pagination->toArray()]);
    }

    /**
     * manageContent
     *
     * 21/08/2020 10:40
     * @param FamilyPagination $pagination
     * @param FamilyManager $manager
     * @Route("/family/content/loader/", name="family_content_loader")
     * @IsGranted("ROLE_ROUTE")
     * @return JsonResponse
     */
    public function manageContent(FamilyPagination $pagination, FamilyManager $manager)
    {
        try {
            $content = $manager->getPaginationContent();
            $pagination->setContent($content);
            return new JsonResponse(['content' => $pagination->getContent(), 'pageMax' => $pagination->getPageMax(), 'status' => 'success'], 200);
        } catch (Exception $e) {
            $this->getStatusManager()->databaseError();
            if ($this->getRequest()->server->get('APP_ENV') === 'dev') {
                $this->getStatusManager()->error($e->getMessage(), [], false);
            }
            return $this->getStatusManager()->toJsonResponse();
        }
    }

    /**
     * familyEdit
     *
     * 21/08/2020 10:39
     * @param FamilyStudentsPagination $studentPagination
     * @param FamilyCareGiversPagination $careGiversPagination
     * @param FamilyRelationshipManager $relationshipManager
     * @param Family|null $family
     * @param string $tabName
     * @Route("/family/{family}/edit/{tabName}",name="family_edit")
     * @Route("/family/add/{tabName}",name="family_add")
     * @IsGranted("ROLE_ROUTE")
     * @return JsonResponse
     */
    public function familyEdit(
        FamilyStudentsPagination $studentPagination,
        FamilyCareGiversPagination $careGiversPagination,
        FamilyRelationshipManager $relationshipManager,
        ?Family $family = null,
        string $tabName = 'General'
    ) {

        TranslationHelper::setDomain('People');

        $family = $family ?: new Family();
        $action = $family->getId() !== null ? $this->generateUrl('family_edit', ['family' => $family->getId(), 'tabName' => $tabName]) : $this->generateUrl('family_add', ['tabName' => $tabName]);
        $form = $this->createForm(FamilyGeneralType::class, $family,
            ['action' => $action]
        );
        $provider = ProviderFactory::create(Family::class);

        $content = $this->getRequest()->getContent() !== '' ? json_decode($this->getRequest()->getContent(), true) : null;

        if ($this->getRequest()->getContent() !== '' && $content['panelName'] === 'General')
        {
            $form->submit($content);
            if ($form->isValid()) {
                $id = $family->getId();
                $provider->persistFlush($family);

                if ($this->isStatusSuccess() && $id !== $family->getId())
                {
                    $this->getStatusManager()->setReDirect($this->generateUrl('family_edit', ['family' => $family->getId()]))
                        ->convertToFlash();
                }
            } else {
                $this->getStatusManager()->invalidInputs();
            }

            return $this->singleForm($form);
        }

        $container = new Container($tabName);

        $panel = new Panel('General', 'People', new Section('form', 'General'));
        $container->addForm('General', $form->createView())->addPanel($panel);

        if ($family->getId() !== null) {
            $student = new FamilyMemberStudent($family);
            $addStudent = $this->createForm(FamilyStudentType::class, $student, ['action' => $this->generateUrl('family_student_add', ['family' => $family->getId() ])]);

            $panel = new Panel('Students', 'People', new Section('form', 'Students'));
            $studentPagination->setContentLoader($this->generateUrl('family_student_content_loader', ['family' => $family->getId()]));
            $panel->addSection(new Section('pagination', $studentPagination));
            $container->addPanel($panel)
                ->addForm('Students', $addStudent->createView());

            $careGiversPagination
                ->setDraggableRoute('family_care_giver_sort')
                ->setContentLoader($this->generateUrl('family_care_giver_content_loader', ['family' => $family->getId()]));
            $careGiver = new FamilyMemberCareGiver($family);
            $addCareGiver = $this->createForm(FamilyCareGiverType::class, $careGiver, ['action' => $this->generateUrl('family_care_giver_add', ['family' => $family->getId() ?: 0])]);

            $panel = new Panel('Care Givers', 'People', new Section('form', 'Care Givers'));
            $panel->addSection(new Section('pagination', $careGiversPagination));
            $container->addPanel($panel->setDisabled($family->getId() === null))
                ->addForm('Care Givers', $addCareGiver->createView());

            if ($family->getCareGivers()->count() !== 0 && $family->getStudents()->count() !== 0) {
                $relationship = $this->createForm(RelationshipsType::class, $relationshipManager->getRelationships($family),
                    ['action' => $this->generateUrl('family_relationships', ['family' => $family->getId() ?: 0])]
                );

                $relationshipManager->setFamily($family)
                    ->setForm($relationship->createView()->vars['toArray']);
                $panel = new Panel('Relationships', 'People', new Section('special', $relationshipManager->toArray()));
                $panel->setDisabled($family->getId() === null);

                $container->addPanel($panel);
            }

            $this->getContainerManager()->setAddElementRoute($this->generateUrl('family_add'));
        }

        return $this->getPageManager()->createBreadcrumbs($family->getId() !== null ? ['Edit Family: {name}', ['{name}' => $family->getName()], 'People'] : 'Add Family')
            ->render(
                [
                    'containers' => $this->getContainerManager()->setReturnRoute($this->generateUrl('family_list'))
                        ->addContainer($container)
                        ->getBuiltContainers(),
                ]
            );
    }


    /**
     * familyRelationships
     *
     * 21/08/2020 08:03
     * @param Family $family
     * @param FamilyRelationshipManager $manager
     * @Route("/family/{family}/relationships/",name="family_relationships", methods={"POST"})
     * @IsGranted("ROLE_ROUTE")
     * @return JsonResponse
     */
    public function familyRelationships(Family $family, FamilyRelationshipManager $manager)
    {
        $content = json_decode($this->getPageManager()->getRequest()->getContent(), true);
        $form = $this->createForm(RelationshipsType::class, $manager->getRelationships($family),
            ['action' => $this->generateUrl('family_relationships', ['family' => $family->getId() ?: 0])]
        );

        $form->submit($content);

        if ($form->isValid())
            $manager->handleRequest($content, $family, $form);
        else {
            $manager->setFamily($family);
            $this->getStatusManager()->invalidInputs();
        }

        $manager->setForm($form->createView()->vars['toArray']);
        return $this->getStatusManager()->toJsonResponse(['special' => $manager->toArray()]);
    }


    /**
     * familyCareGiverEdit
     *
     * 21/08/2020 13:56
     * @param Family $family
     * @param FamilyMemberCareGiver|null $careGiver
     * @Route("/family/{family}/care/giver/{careGiver}/edit/",name="family_care_giver_edit")
     * @Route("/family/{family}/care/giver/add/",name="family_care_giver_add")
     * @IsGranted("ROLE_ROUTE")
     * @return JsonResponse
     */
    public function familyCareGiverEdit(Family $family, FamilyMemberCareGiver $careGiver = null)
    {
        if (is_null($careGiver) || $this->getRequest()->get('_route') === 'family_care_giver_add') {
            $careGiver = new FamilyMemberCareGiver($family);
            $action = $this->generateUrl('family_care_giver_add', ['family' => $family->getId()]);
        } else {
            $action = $this->generateUrl('family_care_giver_edit', ['family' => $family->getId(), 'careGiver' => $careGiver->getId()]);
        }

        $form = $this->createForm(FamilyCareGiverType::class, $careGiver, ['action' => $action]);

        if ($this->getRequest()->getContent() !== '') {
            $content = json_decode($this->getRequest()->getContent(), true);
            if (key_exists('showHideForm', $content)) unset($content['showHideForm']);
            $careGiver->setFamily($family);
            $form->submit($content);
            if ($form->isValid()) {
                ProviderFactory::create(FamilyMemberCareGiver::class)->persistFlush($careGiver);

                if ($this->isStatusSuccess()) $this->getStatusManager()
                    ->setReDirect( $this->generateUrl('family_edit', ['family' => $family->getId(), 'tabName' => 'Care Givers']))
                    ->convertToFlash();
            } else {
                $this->getStatusManager()->invalidInputs();
            }
            return $this->singleForm($form);
        }

        return $this->getPageManager()->createBreadcrumbs('Edit care_giver',
            [
                ['uri' => 'family_list', 'name' => 'Manage Families'],
                ['uri' => 'family_edit', 'uri_params' => ['family' => $family->getId(), 'tabName' => 'Care Givers'] , 'name' => 'Edit Family']
            ]
        )
            ->render(
                [
                    'containers' => $this->getContainerManager()
                        ->setReturnRoute($this->generateUrl('family_edit', ['family' => $family->getId(), 'tabName' => 'Care Givers']))
                        ->singlePanel($form->createView())
                        ->getBuiltContainers(),
                ]
            );
    }

    /**
     * familyCareGiverSort
     *
     * 21/08/2020 11:55
     * @param FamilyMemberCareGiver $source
     * @param FamilyMemberCareGiver $target
     * @param FamilyCareGiversPagination $pagination
     * @param EntitySortManager $manager
     * @Route("/family/care_giver/{source}/{target}/sort/", name="family_care_giver_sort")
     * @IsGranted("ROLE_ROUTE")
     * @return JsonResponse
     */
    public function familyCareGiverSort(FamilyMemberCareGiver $source, FamilyMemberCareGiver $target, FamilyCareGiversPagination $pagination, EntitySortManager $manager)
    {
        $manager
            ->setSortField('contactPriority')
            ->setFindBy(['family' => $source->getFamily(), 'student' => null])
            ->setIndexColumns(['contactPriority','family'])
            ->setIndexName('family_contact_priority')
            ->setTableName('FamilyMember')
            ->execute($source,$target,$pagination)
        ;
        return $this->generateJsonResponse(['content' => $manager->getPaginationContent('care_giver')]);
    }

    /**
     * familyCareGiverRemove
     * @Route("/family/{family}/care/giver/{careGiver}/remove/",name="family_care_giver_remove")
     * @IsGranted("ROLE_ROUTE")
     * @param Family $family
     * @param FamilyMemberCareGiver $careGiver
     * @return RedirectResponse
     */
    public function familyCareGiverRemove(Family $family, FamilyMemberCareGiver $careGiver)
    {
        if ($careGiver->getFamily()->isEqualTo($family)) {

            ProviderFactory::create(FamilyMemberCareGiver::class)->delete($careGiver);

            if ($this->isStatusSuccess()) {
                $priority = 1;
                foreach (FamilyManager::getCareGivers($family, false) as $q => $careGiver) {
                    ProviderFactory::create(FamilyMemberCareGiver::class)->persistFlush($careGiver->setContactPriority($priority++), false);
                    $result[$q] = $careGiver;
                }
                ProviderFactory::create(FamilyMemberCareGiver::class)->flush();
            }
        } else {
            $this->getStatusManager()->invalidInputs();
        }
        $this->getStatusManager()->convertToFlash();
        return $this->redirectToRoute('family_edit', ['family' => $family->getId(), 'tabName' => 'Care Givers']);
    }

    /**
     * familyStudentEdit
     *
     * 21/08/2020 10:57
     * @param Family $family
     * @param FamilyMemberStudent|null $student
     * @Route("/family/{family}/student/{student}/edit/",name="family_student_edit")
     * @Route("/family/{family}/student/add/",name="family_student_add")
     * @IsGranted("ROLE_ROUTE")
     * @return JsonResponse
     */
    public function familyStudentEdit(Family $family, ?FamilyMemberStudent $student = null)
    {
        if (is_null($student) || $this->getRequest()->get('_route') === 'family_student_add') {
            $action = $this->generateUrl('family_student_add', ['family' => $family->getId()]);
            $student = new FamilyMemberStudent($family);
        } else {
            $action = $this->generateUrl('family_student_edit', ['family' => $family->getId(), 'student' => $student->getId()]);
        }

        $form = $this->createForm(FamilyStudentType::class, $student, ['action' => $action]);

        if ($this->getRequest()->getContent() !== '')
        {
            $content = json_decode($this->getRequest()->getContent(), true);
            $form->submit($content);

            if ($form->isValid()) {
                $student->setFamily($family);
                ProviderFactory::create(FamilyMemberStudent::class)->persistFlush($student);

                if ($this->isStatusSuccess()) {
                    $this->getStatusManager()
                        ->setReDirect($this->generateUrl('family_edit', ['family' => $family->getId(), 'tabName' => 'Students']))
                        ->convertToFlash();
                }
            } else {
                $this->getStatusManager()->invalidInputs();
            }
            return $this->singleForm($form);
        }

        return $this->getPageManager()->createBreadcrumbs($student->getId() !== null ? 'Edit Student' : 'Add Student',
            [
                ['uri' => 'family_list', 'name' => 'Manage Families'],
                ['uri' => 'family_edit', 'uri_params' => ['family' => $family->getId(), 'tabName' => 'Students'] , 'name' => 'Edit Family']
            ]
        )
            ->render(
                [
                    'containers' => $this->getContainerManager()
                        ->setReturnRoute($this->generateUrl('family_edit', ['family' => $family->getId(), 'tabName' => 'Students']))
                        ->singlePanel($form->createView())
                        ->getBuiltContainers(),
                ]
            );
    }


    /**
     * familyStudentRemove
     *
     * 21/08/2020 11:28
     * @param Family $family
     * @param FamilyMemberStudent $student
     * @Route("/family/{family}/remove/{student}/student/",name="family_student_remove")
     * @IsGranted("ROLE_ROUTE")
     * @return RedirectResponse
     */
    public function familyStudentRemove(Family $family, FamilyMemberStudent $student)
    {
        if ($student->getFamily()->isEqualTo($family)) {
            $provider = ProviderFactory::create(FamilyMemberStudent::class);
            $provider->delete($student);
        } else {
            $this->getStatusManager()->invalidInputs();
        }
        $this->getStatusManager()->convertToFlash();

        return $this->redirectToRoute('family_edit', ['family' => $family->getId(), 'tabName' => 'Students']);
    }

    /**
     * familyDelete
     * @Route("/family/{family}/delete/",name="family_delete")
     * @IsGranted("ROLE_ROUTE")
     * @param Family $family
     * @param FamilyManager $manager
     * @return RedirectResponse
     */
    public function familyDelete(Family $family, FamilyManager $manager)
    {
        $manager->deleteFamily($family, $this->getRequest()->getSession()->getFlashBag());

        return $this->redirectToRoute('family_list');
    }

    /**
     * Family Filter Store
     * @Route("/family/filter/store/",name="family_filter_store")
     * @param FamilyPagination $pagination
     * @return JsonResponse
     */
    public function familyFilterStore(FamilyPagination $pagination)
    {
        if ($this->getPageManager()->getRequest()->getContent() !== '') {
            $filter = json_decode($this->getPageManager()->getRequest()->getContent(), true);
            $pagination->setStack($this->getPageManager()->getStack())
                ->writeFilter($filter);
        }
        return new JsonResponse([]);
    }

    /**
     * studentContentLoader
     *
     * 22/08/2020 14:08
     * @param Family $family
     * @param FamilyStudentsPagination $pagination
     * @Route("/family/{family}/student/content/loader/",name="family_student_content_loader")
     * @IsGranted("ROLE_ROUTE")
     * @return JsonResponse
     */
    public function studentContentLoader(Family $family, FamilyStudentsPagination $pagination)
    {
        try {
            $pagination->setContent(FamilyManager::getStudents($family, false));
            return new JsonResponse(['content' => $pagination->getContent(), 'pageMax' => $pagination->getPageMax(), 'status' => 'success'], 200);
        } catch (Exception $e) {
            $this->getStatusManager()->databaseError();
            if ($this->getRequest()->server->get('APP_ENV') === 'dev') {
                $this->getStatusManager()->error($e->getMessage(), [], false);
            }
            return $this->getStatusManager()->toJsonResponse();
        }
    }

    /**
     * careGiverContentLoader
     *
     * 22/08/2020 14:08
     * @param Family $family
     * @param FamilyCareGiversPagination $pagination
     * @Route("/family/{family}/care/giver/content/loader/",name="family_care_giver_content_loader")
     * @IsGranted("ROLE_ROUTE")
     * @return JsonResponse
     */
    public function careGiverContentLoader(Family $family, FamilyCareGiversPagination $pagination)
    {
        try {
            $pagination->setContent(FamilyManager::getCareGivers($family, false));
            return new JsonResponse(['content' => $pagination->getContent(), 'pageMax' => $pagination->getPageMax(), 'status' => 'success'], 200);
        } catch (Exception $e) {
            $this->getStatusManager()->databaseError();
            if ($this->getRequest()->server->get('APP_ENV') === 'dev') {
                $this->getStatusManager()->error($e->getMessage(), [], false);
            }
            return $this->getStatusManager()->toJsonResponse();
        }
    }
}