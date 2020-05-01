<?php
/**
 * Created by PhpStorm.
 *
 * quoll
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
use App\Modules\People\Entity\FamilyAdult;
use App\Modules\People\Entity\FamilyChild;
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
            ->setAddElementRoute($this->generateUrl('family_add'))
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
            $content = $manager->findBySearch();
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
                    $form = $this->createForm(FamilyGeneralType::class, $family,
                        ['action' => $this->generateUrl('family_edit', ['family' => $family->getId(), $tabName => 'General'])]
                    );
                }
                $manager->singlePanel($form->createView());
                $data['form'] = $manager->getFormFromContainer();
                return new JsonResponse($data,200);
            } else {
                $data = ErrorMessageHelper::getInvalidInputsMessage([],true);
                $manager->singlePanel($form->createView());
                $data['form'] = $manager->getFormFromContainer();
                return new JsonResponse($data,200);
            }
        }

        $container = new Container();
        $container->setSelectedPanel($tabName);

        $panel = new Panel('General', 'People');
        $container->addForm('General', $form->createView())->addPanel($panel);

        $childrenPagination->setContent(FamilyManager::getChildren($family))->setPageMax(25)->setTargetElement('pagination');
        $child = new FamilyChild($family);
        $addChild = $this->createForm(FamilyChildType::class, $child, ['action' => $this->generateUrl('family_student_add', ['family' => $family->getId() ?: 0]), 'postFormContent' => $childrenPagination->toArray()]);

        $panel = new Panel('Students', 'People');
        $container->addPanel($panel->setDisabled(intval($family->getId()) === 0))->addForm('Students', $addChild->createView());

        $adultsPagination->setDraggableSort()
            ->setDraggableRoute('family_adult_sort')
            ->setContent(FamilyManager::getAdults($family, true))
            ->setPageMax(25)
            ->setTargetElement('pagination');
        $adult = new FamilyAdult($family);
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

        return $this->getPageManager()->createBreadcrumbs($family->getId() > 0 ? 'Edit Family' : 'Add Family')
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
     * @param FamilyAdult|null $adult
     * @return JsonResponse
     * @Route("/family/{family}/adult/{adult}/edit/",name="family_adult_edit")
     * @Route("/family/{family}/adult/add/",name="family_adult_add")
     * @IsGranted("ROLE_ROUTE")
     */
    public function familyAdultEdit(ContainerManager $manager, Family $family, ?FamilyAdult $adult = null)
    {
        $request = $this->getPageManager()->getRequest();
        $action = $this->generateUrl('family_adult_edit', ['family' => $family->getId(), 'adult' => $adult->getId()]);
        if ($request->get('_route') === 'family_adult_add') {
            $adult = new FamilyAdult($family);
            $action = $this->generateUrl('family_adult_add', ['family' => $family->getId()]);
        }
        dump($adult);
        $form = $this->createForm(FamilyAdultType::class, $adult, ['action' => $action]);

        if ($request->getContent() !== '') {
            $data = [];
            $data['status'] = 'success';
            $data['errors'] = [];
            $content = json_decode($request->getContent(), true);
            if ($content['contactPriority'] === '' || $adult->getId() == 0)
                $content['contactPriority'] = ProviderFactory::getRepository(FamilyAdult::class)->getNextContactPriority($family);
            if (key_exists('showHideForm', $content))
                unset($content['showHideForm']);
            $form->submit($content);
            dump($content, $form, $adult);
            if ($form->isValid()) {
                $data = ProviderFactory::create(FamilyAdult::class)->persistFlush($adult, $data);

                $manager->singlePanel($form->createView());
                $data['form'] = $manager->getFormFromContainer('formContent', 'single');
                if ($data['status'] === 'success') {
                    $data['status'] = 'redirect';
                    $data['redirect'] = $this->generateUrl('family_edit', ['family' => $family->getId(), 'tabName' => 'Adults']);
                    $this->addFlash('success', ErrorMessageHelper::onlySuccessMessage(true));
                }
                return new JsonResponse($data);
            } else {
                $data = ErrorMessageHelper::getInvalidInputsMessage($data, true);
                $manager->singlePanel($form->createView());
                $data['form'] = $manager->getFormFromContainer('formContent', 'single');
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
     * @param FamilyAdult $source
     * @param FamilyAdult $target
     * @param FamilyAdultsPagination $pagination
     * @param FamilyManager $familyManager
     * @return JsonResponse
     * @Route("/family/adult/{source}/{target}/sort/", name="family_adult_sort")
     * @IsGranted("ROLE_ROUTE")
     */
    public function familyAdultSort(FamilyAdult $source, FamilyAdult $target, FamilyAdultsPagination $pagination, FamilyManager $familyManager)
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
     * @param FamilyAdult $adult
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function familyAdultRemove(Family $family, FamilyAdult $adult)
    {
        $request = $this->getPageManager();
        if ($adult->getFamily()->isEqualTo($family)) {

            $data = ProviderFactory::create(FamilyAdult::class)->remove($adult, []);

            $messages = array_unique($data['errors'], SORT_REGULAR);
            foreach($messages as $message)
                $request->getSession()->getBag('flashes')->add($message['class'], $message['message']);
            if ($data['status'] === 'success') {
                $priority = 1;
                foreach (FamilyManager::getAdults($family, false) as $q => $adult) {
                    ProviderFactory::create(FamilyAdult::class)->persistFlush($adult->setContactPriority($priority++), [], false);
                    $result[$q] = $adult;
                }
                ProviderFactory::create(FamilyAdult::class)->flush();
            }
        } else {
            $request->getSession()->getBag('flashes')->add('error', ErrorMessageHelper::onlyInvalidInputsMessage(true));
        }

        return $this->redirectToRoute('family_edit', ['family' => $family->getId(), 'tabName' => 'Adults']);
    }

    /**
     * @Route("/", name="family_delete")
     * @Route("/", name="family_student_edit")
     * @Route("/", name="family_student_add")
     * @Route("/", name="family_student_remove")
     */
    public function stiff(){
        return new Response('<h3>Nothing to see here.</h3>');
    }
}