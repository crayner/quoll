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

        $childrenPagination->setContent(FamilyManager::getChildren($family, true))->setPageMax(25)->setTargetElement('pagination');
        $child = new FamilyChild($family);
        $addChild = $this->createForm(FamilyChildType::class, $child, ['action' => $this->generateUrl('family_child_add', ['family' => $family->getId() ?: 0]), 'postFormContent' => $childrenPagination->toArray()]);

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
        $panel = new Panel('Relationships', 'People');
        $content = $this->renderView('people/family/relationships.html.twig', [
            'relationship' => $relationship->createView(),
            'family' => $family,
        ]);
        $container->addPanel($panel->setDisabled(intval($family->getId()) === 0)->setContent($content));

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
     * @Route("/", name="family_child_add")
     * @Route("/", name="family_adult_add")
     * @Route("/", name="family_relationships")
     * @Route("/", name="family_adult_sort")
     */
    public function stiff(){}
}