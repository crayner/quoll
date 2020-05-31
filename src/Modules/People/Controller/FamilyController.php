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
 * Date: 27/04/2020
 * Time: 13:51
 */

namespace App\Modules\People\Controller;

use App\Container\Container;
use App\Container\ContainerManager;
use App\Container\Panel;
use App\Controller\AbstractPageController;
use App\Modules\People\Entity\Family;
use App\Modules\People\Entity\FamilyMember;
use App\Modules\People\Entity\FamilyMemberAdult;
use App\Modules\People\Entity\FamilyMemberChild;
use App\Modules\People\Form\FamilyAdultType;
use App\Modules\People\Form\FamilyChildType;
use App\Modules\People\Form\FamilyGeneralType;
use App\Modules\People\Form\RelationshipsType;
use App\Modules\People\Manager\FamilyManager;
use App\Modules\People\Manager\FamilyRelationshipManager;
use App\Modules\People\Manager\Hidden\FamilyAdultSort;
use App\Modules\People\Pagination\FamilyAdultsPagination;
use App\Modules\People\Pagination\FamilyChildrenPagination;
use App\Modules\People\Pagination\FamilyPagination;
use App\Provider\ProviderFactory;
use App\Util\ErrorMessageHelper;
use App\Util\TranslationHelper;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class FamilyController
 * @package App\Modules\People\Controller
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
     * @param FamilyChildrenPagination $childrenPagination
     * @param FamilyAdultsPagination $adultsPagination
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
        FamilyChildrenPagination $childrenPagination,
        FamilyAdultsPagination $adultsPagination,
        ContainerManager $manager,
        FamilyRelationshipManager $relationshipManager,
        ?Family $family = null,
        string $tabName = 'General'
    ) {
        $request = $this->getRequest();

        TranslationHelper::setDomain('People');

        $family = $family ?: new Family();
        $action = intval($family->getId()) > 0 ? $this->generateUrl('family_edit', ['family' => $family->getId(), 'tabName' => $tabName]) : $this->generateUrl('family_add', ['tabName' => $tabName]);
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

        $container = new Container();
        $container->setSelectedPanel($tabName);

        $panel = new Panel('General', 'People');
        $container->addForm('General', $form->createView())->addPanel($panel);

        $childrenPagination->setContent(FamilyManager::getChildren($family))->setPageMax(25)->setTargetElement('pagination');
        $child = new FamilyMemberChild($family);
        $addChild = $this->createForm(FamilyChildType::class, $child, ['action' => $this->generateUrl('family_student_add', ['family' => $family->getId() ?: 0]), 'postFormContent' => $childrenPagination->toArray()]);

        $panel = new Panel('Students', 'People');
        $container->addPanel($panel->setDisabled(intval($family->getId()) === 0))->addForm('Students', $addChild->createView());

        $adultsPagination->setDraggableSort()
            ->setDraggableRoute('family_adult_sort')
            ->setContent(FamilyManager::getAdults($family, true))
            ->setPageMax(25)
            ->setTargetElement('pagination');
        $adult = new FamilyMemberAdult($family);
        $addAdult = $this->createForm(FamilyAdultType::class, $adult, ['action' => $this->generateUrl('family_adult_add', ['family' => $family->getId() ?: 0]), 'postFormContent' => $adultsPagination->toArray()]);

        $panel = new Panel('Adults', 'People');
        $container->addPanel($panel->setDisabled(intval($family->getId()) === 0))->addForm('Adults', $addAdult->createView());

        $relationship = $this->createForm(RelationshipsType::class, $relationshipManager->getRelationships($family),
            ['action' => $this->generateUrl('family_relationships', ['family' => $family->getId() ?: 0])]
        );

        $relationshipManager->setFamily($family)->setForm($relationship->createView()->vars['toArray']);
        $panel = new Panel('Relationships', 'People');
        $panel->setSpecial($relationshipManager)
            ->setDisabled(intval($family->getId()) === 0);

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
     * familyAdultEdit
     * @param ContainerManager $manager
     * @param Family $family
     * @return JsonResponse
     * @Route("/family/{family}/adult/{adult}/edit/",name="family_adult_edit")
     * @Route("/family/{family}/adult/add/",name="family_adult_add")
     * @IsGranted("ROLE_ROUTE")
     */
    public function familyAdultEdit(ContainerManager $manager, Family $family, ?FamilyMemberAdult $adult = null)
    {
        $request = $this->getRequest();

        if (is_null($adult) || $request->get('_route') === 'family_adult_add') {
            $adult = new FamilyMemberAdult($family);
            $action = $this->generateUrl('family_adult_add', ['family' => $family->getId()]);
        } else {
            $action = $this->generateUrl('family_adult_edit', ['family' => $family->getId(), 'adult' => $adult->getId()]);
        }

        $form = $this->createForm(FamilyAdultType::class, $adult, ['action' => $action]);

        if ($request->getContent() !== '') {
            $data = [];
            $data['status'] = 'success';
            $data['errors'] = [];
            $content = json_decode($request->getContent(), true);
            if ($content['contactPriority'] === '' || $adult->getId() == 0)
                $content['contactPriority'] = ProviderFactory::getRepository(FamilyMemberAdult::class)->getNextContactPriority($family);
            if (key_exists('showHideForm', $content))
                unset($content['showHideForm']);
            $form->submit($content);
            dump($content, $form, $adult);
            if ($form->isValid()) {
                $data = ProviderFactory::create(FamilyMemberAdult::class)->persistFlush($adult, $data);

                $manager->singlePanel($form->createView());
                $data['form'] = $manager->getFormFromContainer();
                if ($data['status'] === 'success') {
                    $data['status'] = 'redirect';
                    $data['redirect'] = $this->generateUrl('family_edit', ['family' => $family->getId(), 'tabName' => 'Adults']);
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
        $manager->setReturnRoute($this->generateUrl('family_edit', ['family' => $family->getId(), 'tabName' => 'Adults']))->singlePanel($form->createView());

        return $this->getPageManager()->createBreadcrumbs('Edit Adult',
            [
                ['uri' => 'family_list', 'name' => 'Manage Families'],
                ['uri' => 'family_edit', 'uri_params' => ['family' => $family->getId(), 'tabName' => 'Adults'] , 'name' => 'Edit Family']
            ]
        )
            ->render(
                [
                    'containers' => $manager->getBuiltContainers(),
                ]
            );
    }

    /**
     * familyAdultSort
     * @param FamilyMemberAdult $source
     * @param FamilyMemberAdult $target
     * @param FamilyAdultsPagination $pagination
     * @param FamilyManager $familyManager
     * @return JsonResponse
     * @Route("/family/adult/{source}/{target}/sort/", name="family_adult_sort")
     * @IsGranted("ROLE_ROUTE")
     */
    public function familyAdultSort(FamilyMemberAdult $source, FamilyMemberAdult $target, FamilyAdultsPagination $pagination, FamilyManager $familyManager)
    {
        $manager = new FamilyAdultSort($source, $target, $pagination);
        $manager->setContent($familyManager::getAdults($source->getFamily(), true));

        return new JsonResponse($manager->getDetails());
    }

    /**
     * familyAdultRemove
     * @Route("/family/{family}/remove/{adult}/adult/",name="family_adult_remove")
     * @IsGranted("ROLE_ROUTE")
     * @param Family $family
     * @param FamilyMember $adult
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function familyAdultRemove(Family $family, FamilyMember $adult)
    {
        $request = $this->getPageManager();
        if ($adult->getFamily()->isEqualTo($family)) {

            $data = ProviderFactory::create(FamilyMemberAdult::class)->remove($adult, []);

            $messages = array_unique($data['errors'], SORT_REGULAR);
            foreach($messages as $message)
                $request->getSession()->getBag('flashes')->add($message['class'], $message['message']);
            if ($data['status'] === 'success') {
                $priority = 1;
                foreach (FamilyManager::getAdults($family, false) as $q => $adult) {
                    ProviderFactory::create(FamilyMemberAdult::class)->persistFlush($adult->setContactPriority($priority++), [], false);
                    $result[$q] = $adult;
                }
                ProviderFactory::create(FamilyMemberAdult::class)->flush();
            }
        } else {
            $request->getSession()->getBag('flashes')->add('error', ErrorMessageHelper::onlyInvalidInputsMessage(true));
        }

        return $this->redirectToRoute('family_edit', ['family' => $family->getId(), 'tabName' => 'Adults']);
    }

    /**
     * familyStudentEdit
     * @param Family $family
     * @param ContainerManager $manager
     * @param FamilyMemberChild|null $student
     * @return JsonResponse
     * @Route("/family/{family}/student/{student}/edit/",name="family_student_edit")
     * @Route("/family/{family}/student/add/",name="family_student_add")
     * @IsGranted("ROLE_ROUTE")
     */
    public function familyStudentEdit(Family $family, ContainerManager $manager, ?FamilyMemberChild $student = null)
    {
        $request = $this->getRequest();

        if (is_null($student) || $request->get('_route') === 'family_student_add') {
            $action = $this->generateUrl('family_student_add', ['family' => $family->getId()]);
            $student = new FamilyMemberChild($family);
        } else {
            $action = $this->generateUrl('family_student_edit', ['family' => $family->getId(), 'student' => $student->getId()]);
        }

        $form = $this->createForm(FamilyChildType::class, $student, ['action' => $action]);

        if ($request->getContent() !== '')
        {
            $content = json_decode($request->getContent(), true);
            $form->submit($content);

            if ($form->isValid()) {

                $data = ProviderFactory::create(FamilyMemberChild::class)->persistFlush($student, []);

                $manager->singlePanel($form->createView());
                $data['form'] = $manager->getFormFromContainer();
                if ($data['status'] === 'success') {
                    $data['status'] = 'redirect';
                    $data['redirect'] = $this->generateUrl('family_edit', ['family' => $family->getId(), 'tabName' => 'Students']);
                    $this->addFlash('success', ErrorMessageHelper::onlySuccessMessage(true));
                }
                return new JsonResponse($data, 200);
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
     * @param FamilyMember $student
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function familyStudentRemove(Family $family, FamilyMemberChild $student)
    {
        $request = $this->getPageManager()->getRequest();
        if ($student->getFamily()->isEqualTo($family)) {

            $data = ProviderFactory::create(FamilyMemberChild::class)->remove($student, []);

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