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
use App\Container\ContainerManager;
use App\Container\Panel;
use App\Container\Section;
use App\Controller\AbstractPageController;
use App\Modules\People\Entity\Family;
use App\Modules\People\Entity\FamilyMember;
use App\Modules\People\Entity\FamilyMemberCareGiver;
use App\Modules\People\Entity\FamilyMemberStudent;
use App\Modules\People\Form\FamilyCareGiverType;
use App\Modules\People\Form\FamilyStudentType;
use App\Modules\People\Form\FamilyGeneralType;
use App\Modules\People\Form\RelationshipsType;
use App\Modules\People\Manager\FamilyManager;
use App\Modules\People\Manager\FamilyRelationshipManager;
use App\Modules\People\Manager\Hidden\Familycare_giverSort;
use App\Modules\People\Pagination\FamilyCareGiversPagination;
use App\Modules\People\Pagination\FamilyStudentsPagination;
use App\Modules\People\Pagination\FamilyPagination;
use App\Provider\ProviderFactory;
use App\Util\ErrorMessageHelper;
use App\Util\TranslationHelper;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
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
     * @Route("/family/list/",name="family_list")
     * @IsGranted("ROLE_ROUTE")
     * @param FamilyPagination $pagination
     * @return Response|JsonResponse
     */
    public function familyManage(FamilyPagination $pagination)
    {
        $pagination->setContent([])
            ->setStack($this->getPageManager()->getStack())
            ->setAddElementRoute($this->generateUrl('family_add'))
            ->setStoreFilterURL($this->generateUrl('family_filter_store'))
            ->setContentLoader($this->generateUrl('family_content_loader'));

        return $this->getPageManager()->createBreadcrumbs('Manage Families')
            ->render(['pagination' => $pagination->toArray()]);
    }

    /**
     * manageContent
     * @Route("/family/content/loader/", name="family_content_loader")
     * @IsGranted("ROLE_ROUTE")
     * @param FamilyPagination $pagination
     * @param FamilyManager $manager
     * @return JsonResponse
     */
    public function manageContent(FamilyPagination $pagination, FamilyManager $manager)
    {
        try {
            $content = $manager->getPaginationContent();
            $pagination->setContent($content);
            return new JsonResponse(['content' => $pagination->getContent(), 'pageMax' => $pagination->getPageMax(), 'status' => 'success'], 200);
        } catch (\Exception $e) {
            return new JsonResponse(['status' => 'error', 'message' => $e->getMessage()], 200);
        }
    }

    /**
     * familyEdit
     * @param FamilyStudentsPagination $studentPagination
     * @param FamilyCareGiversPagination $careGiversPagination
     * @param ContainerManager $manager
     * @param FamilyRelationshipManager $relationshipManager
     * @param Family|null $family
     * @param string $tabName
     * @return Response|JsonResponse
     * @Route("/family/{family}/edit/{tabName}",name="family_edit")
     * @Route("/family/add/{tabName}",name="family_add")
     * @IsGranted("ROLE_ROUTE")
     */
    public function familyEdit(
        FamilyStudentsPagination $studentPagination,
        FamilyCareGiversPagination $careGiversPagination,
        ContainerManager $manager,
        FamilyRelationshipManager $relationshipManager,
        ?Family $family = null,
        string $tabName = 'General'
    ) {
        $request = $this->getRequest();

        TranslationHelper::setDomain('People');

        $family = $family ?: new Family();
        $action = $family->getId() !== null ? $this->generateUrl('family_edit', ['family' => $family->getId(), 'tabName' => $tabName]) : $this->generateUrl('family_add', ['tabName' => $tabName]);
        $form = $this->createForm(FamilyGeneralType::class, $family,
            ['action' => $action]
        );
        $provider = ProviderFactory::create(Family::class);

        $content = $request->getContent() !== '' ? json_decode($request->getContent(), true) : null;

        if ($request->getContent() !== '' && $content['panelName'] === 'General')
        {
            $form->submit($content);
            if ($form->isValid()) {
                $id = $family->getId();
                $data = $provider->persistFlush($family);

                if ($data['status'] === 'success' && $id !== $family->getId())
                {
                    $data['status'] = 'redirect';
                    $data['redirect'] = $this->generateUrl('family_edit', ['family' => $family->getId()]);
                    $this->addFlash('success', ErrorMessageHelper::onlySuccessMessage());
                }
            } else {
                $data = ErrorMessageHelper::getInvalidInputsMessage([],true);
            }
            $manager->singlePanel($form->createView());
            $data['form'] = $manager->getFormFromContainer();
            return new JsonResponse($data,200);
        }

        $container = new Container($tabName);

        $panel = new Panel('General', 'People', new Section('form', 'General'));
        $container->addForm('General', $form->createView())->addPanel($panel);

        $student = new FamilyMemberStudent($family);
        $addStudent = $this->createForm(FamilyStudentType::class, $student, ['action' => $this->generateUrl('family_student_add', ['family' => $family->getId() ])]);

        $panel = new Panel('Students', 'People', new Section('form', 'Students'));
        $studentPagination->setContent(FamilyManager::getStudents($family, true));
        $panel->addSection(new Section('pagination', $studentPagination));
        $container->addPanel($panel->setDisabled($family->getId() === null))
            ->addForm('Students', $addStudent->createView());

        $careGiversPagination->setDraggableSort()
            ->setDraggableRoute('family_care_giver_sort')
            ->setContent(FamilyManager::getCareGivers($family, true));
        $careGiver = new FamilyMemberCareGiver($family);
        $addCareGiver = $this->createForm(FamilyCareGiverType::class, $careGiver, ['action' => $this->generateUrl('family_care_giver_add', ['family' => $family->getId() ?: 0])]);

        $panel = new Panel('Care Givers', 'People', new Section('form', 'Care Givers'));
        $panel->addSection(new Section('pagination', $careGiversPagination));
        $container->addPanel($panel->setDisabled($family->getId() === null))
            ->addForm('Care Givers', $addCareGiver->createView());

        $relationship = $this->createForm(RelationshipsType::class, $relationshipManager->getRelationships($family),
            ['action' => $this->generateUrl('family_relationships', ['family' => $family->getId() ?: 0])]
        );

        $relationshipManager->setFamily($family)
            ->setForm($relationship->createView()->vars['toArray']);
        $panel = new Panel('Relationships', 'People', new Section('special', $relationshipManager->toArray()));
        $panel->setDisabled($family->getId() === null);

        $container->addPanel($panel);

        $manager->setReturnRoute($this->generateUrl('family_list'));
        $manager->addContainer($container)->buildContainers();

        return $this->getPageManager()->createBreadcrumbs($family->getId() !== null ? 'Edit Family' : 'Add Family')
            ->render(
                [
                    'containers' => $manager->getBuiltContainers(),
                ]
            );
    }


    /**
     * familyManage
     * @Route("/family/{family}/relationships/",name="family_relationships", methods={"POST"})
     * @IsGranted("ROLE_ROUTE")
     * @param Family $family
     * @param FamilyRelationshipManager $manager
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
            $data = $manager->handleRequest($content, $family, $form);
        else {
            $manager->setFamily($family);
            $data = ErrorMessageHelper::getInvalidInputsMessage([], true);
        }

        $manager->setForm($form->createView()->vars['toArray']);

        $data['special'] = $manager->toArray();

        return new JsonResponse($data);
    }


    /**
     * familyCareGiverEdit
     * @param ContainerManager $manager
     * @param Family $family
     * @param FamilyMemberCareGiver|null $careGiver
     * @Route("/family/{family}/care/giver/{careGiver}/edit/",name="family_care_giver_edit")
     * @Route("/family/{family}/care/giver/add/",name="family_care_giver_add")
     * @IsGranted("ROLE_ROUTE")
     * @return JsonResponse
     * 24/07/2020 13:24
     */
    public function familyCareGiverEdit(ContainerManager $manager, Family $family, ?FamilyMemberCareGiver $careGiver = null)
    {
        $request = $this->getRequest();

        if (is_null($careGiver) || $request->get('_route') === 'family_care_giver_add') {
            $careGiver = new FamilyMemberCareGiver($family);
            $action = $this->generateUrl('family_care_giver_add', ['family' => $family->getId()]);
        } else {
            $action = $this->generateUrl('family_care_giver_edit', ['family' => $family->getId(), 'care_giver' => $careGiver->getId()]);
        }

        $form = $this->createForm(FamilyCareGiverType::class, $careGiver, ['action' => $action]);

        if ($request->getContent() !== '') {
            $data = [];
            $data['status'] = 'success';
            $data['errors'] = [];
            $content = json_decode($request->getContent(), true);
            if ($content['contactPriority'] === '' || $careGiver->getId() === null)
                $content['contactPriority'] = ProviderFactory::getRepository(FamilyMemberCareGiver::class)->getNextContactPriority($family);
            if (key_exists('showHideForm', $content))
                unset($content['showHideForm']);
            $form->submit($content);
            if ($form->isValid()) {
                $careGiver->setFamily($family);
                $data = ProviderFactory::create(FamilyMemberCareGiver::class)->persistFlush($careGiver, $data);

                $manager->singlePanel($form->createView());
                $data['form'] = $manager->getFormFromContainer();
                if ($data['status'] === 'success') {
                    $data['status'] = 'redirect';
                    $data['redirect'] = $this->generateUrl('family_edit', ['family' => $family->getId(), 'tabName' => 'care_givers']);
                    $this->addFlash('success', ErrorMessageHelper::onlySuccessMessage(true));
                }
                return new JsonResponse($data);
            } else {
                $data = ErrorMessageHelper::getInvalidInputsMessage($data, true);
                $manager->singlePanel($form->createView());
                $data['form'] = $manager->getFormFromContainer();
                return new JsonResponse($data);
            }
        }
        $manager->setReturnRoute($this->generateUrl('family_edit', ['family' => $family->getId(), 'tabName' => 'care_givers']))->singlePanel($form->createView());

        return $this->getPageManager()->createBreadcrumbs('Edit care_giver',
            [
                ['uri' => 'family_list', 'name' => 'Manage Families'],
                ['uri' => 'family_edit', 'uri_params' => ['family' => $family->getId(), 'tabName' => 'care_givers'] , 'name' => 'Edit Family']
            ]
        )
            ->render(
                [
                    'containers' => $manager->getBuiltContainers(),
                ]
            );
    }

    /**
     * familycare_giverSort
     * @param FamilyMemberCareGiver $source
     * @param FamilyMemberCareGiver $target
     * @param FamilyCareGiversPagination $pagination
     * @param FamilyManager $familyManager
     * @return JsonResponse
     * @Route("/family/care_giver/{source}/{target}/sort/", name="family_care_giver_sort")
     * @IsGranted("ROLE_ROUTE")
     */
    public function familycare_giverSort(FamilyMemberCareGiver $source, FamilyMemberCareGiver $target, FamilyCareGiversPagination $pagination, FamilyManager $familyManager)
    {
        $manager = new Familycare_giverSort($source, $target, $pagination);
        $manager->setContent($familyManager::getCareGivers($source->getFamily(), true));

        return new JsonResponse($manager->getDetails());
    }

    /**
     * familyCareGiverRemove
     * @Route("/family/{family}/remove/{careGiver}/care/giver/",name="family_care_giver_remove")
     * @IsGranted("ROLE_ROUTE")
     * @param Family $family
     * @param FamilyMember $careGiver
     * @return RedirectResponse
     */
    public function familyCareGiverRemove(Family $family, FamilyMember $careGiver)
    {
        $request = $this->getPageManager();
        if ($careGiver->getFamily()->isEqualTo($family)) {

            $data = ProviderFactory::create(FamilyMemberCareGiver::class)->remove($careGiver, []);

            $messages = array_unique($data['errors'], SORT_REGULAR);
            foreach($messages as $message)
                $request->getSession()->getBag('flashes')->add($message['class'], $message['message']);
            if ($data['status'] === 'success') {
                $priority = 1;
                foreach (FamilyManager::getCareGivers($family, false) as $q => $careGiver) {
                    ProviderFactory::create(FamilyMemberCareGiver::class)->persistFlush($careGiver->setContactPriority($priority++), [], false);
                    $result[$q] = $careGiver;
                }
                ProviderFactory::create(FamilyMemberCareGiver::class)->flush();
            }
        } else {
            $request->getSession()->getBag('flashes')->add('error', ErrorMessageHelper::onlyInvalidInputsMessage(true));
        }

        return $this->redirectToRoute('family_edit', ['family' => $family->getId(), 'tabName' => 'Care Givers']);
    }

    /**
     * familyStudentEdit
     * @param Family $family
     * @param ContainerManager $manager
     * @param FamilyMemberStudent|null $student
     * @return JsonResponse
     * @Route("/family/{family}/student/{student}/edit/",name="family_student_edit")
     * @Route("/family/{family}/student/add/",name="family_student_add")
     * @IsGranted("ROLE_ROUTE")
     */
    public function familyStudentEdit(Family $family, ContainerManager $manager, ?FamilyMemberStudent $student = null)
    {
        $request = $this->getRequest();

        if (is_null($student) || $request->get('_route') === 'family_student_add') {
            $action = $this->generateUrl('family_student_add', ['family' => $family->getId()]);
            $student = new FamilyMemberStudent($family);
        } else {
            $action = $this->generateUrl('family_student_edit', ['family' => $family->getId(), 'student' => $student->getId()]);
        }

        $form = $this->createForm(FamilyStudentType::class, $student, ['action' => $action]);

        if ($request->getContent() !== '')
        {
            $content = json_decode($request->getContent(), true);
            $form->submit($content);

            if ($form->isValid()) {
                $student->setFamily($family);
                $data = ProviderFactory::create(FamilyMemberStudent::class)->persistFlush($student, []);
                dump($data,$content,$student);

                if ($data['status'] === 'success') {
                    $data['status'] = 'redirect';
                    $data['redirect'] = $this->generateUrl('family_edit', ['family' => $family->getId(), 'tabName' => 'Students']);
                    $this->addFlash('success', ErrorMessageHelper::onlySuccessMessage(true));
                }

                $manager->singlePanel($form->createView());
                $data['form'] = $manager->getFormFromContainer();

                return new JsonResponse($data);
            } else {
                $data = ErrorMessageHelper::getInvalidInputsMessage([], true);
                $manager->singlePanel($form->createView());
                $data['form'] = $manager->getFormFromContainer();
                return new JsonResponse($data, 200);
            }
        }
        $manager->setReturnRoute($this->generateUrl('family_edit', ['family' => $family->getId(), 'tabName' => 'Students']))->singlePanel($form->createView());

        return $this->getPageManager()->createBreadcrumbs($student->getId() !== null ? 'Edit Student' : 'Add Student',
            [
                ['uri' => 'family_list', 'name' => 'Manage Families'],
                ['uri' => 'family_edit', 'uri_params' => ['family' => $family->getId(), 'tabName' => 'Students'] , 'name' => 'Edit Family']
            ]
        )
            ->render(
                [
                    'containers' => $manager->getBuiltContainers(),
                ]
            );
    }


    /**
     * familyStudentRemove
     * @Route("/family/{family}/remove/{student}/student/",name="family_student_remove")
     * @IsGranted("ROLE_ROUTE")
     * @param Family $family
     * @param FamilyMemberStudent $student
     * @return RedirectResponse
     */
    public function familyStudentRemove(Family $family, FamilyMemberStudent $student)
    {
        $request = $this->getPageManager()->getRequest();
        if ($student->getFamily()->isEqualTo($family)) {

            $data = ProviderFactory::create(FamilyMemberStudent::class)->delete($student);

            $messages = array_unique($data['errors'], SORT_REGULAR);
            foreach($messages as $message)
                $request->getSession()->getBag('flashes')->add($message['class'], $message['message']);
        } else {
            $request->getSession()->getBag('flashes')->add('error', ['return.error.1',[],'messages']);
        }

        return $this->redirectToRoute('family_edit', ['family' => $family->getId(), 'tabName' => 'Students']);
    }

    /**
     * familyDelete
     * @Route("/family/{family}/delete/",name="family_delete")
     * @IsGranted("ROLE_ROUTE")
     * @param Family $family
     * @param FamilyManager $manager
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
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
}