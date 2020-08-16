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
 * Date: 9/06/2020
 * Time: 12:21
 */
namespace App\Modules\IndividualNeed\Controller;

use App\Controller\AbstractPageController;
use App\Manager\EntitySortManager;
use App\Manager\MessageStatusManager;
use App\Modules\IndividualNeed\Entity\INDescriptor;
use App\Modules\IndividualNeed\Form\INDescriptorType;
use App\Modules\IndividualNeed\Pagination\INDescriptorPagination;
use App\Provider\ProviderFactory;
use App\Twig\PageHeader;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class ManageController
 * @package App\Modules\IndividualNeed
 * @author Craig Rayner <craig@craigrayner.com>
 */
class ManageController extends AbstractPageController
{
    /**
     * list
     * @param INDescriptorPagination $pagination
     * @param array $data
     * @return JsonResponse
     * @Route("/individual/need/list/",name="individual_need_list")
     * @IsGranted("ROLE_ROUTE")
     * 9/06/2020 12:26
     */
    public function list(INDescriptorPagination $pagination, array $data = [])
    {
        $content = ProviderFactory::getRepository(INDescriptor::class)->findBy([], ['sortOrder' => 'ASC']);
        $pagination->setContent($content)
            ->setDraggableRoute('individual_need_descriptor_sort')
            ->setAddElementRoute($this->generateUrl('individual_need_add'));

        return $this->getPageManager()
            ->setUrl($this->generateUrl('individual_need_list'))
            ->setMessages($data['errors'] ?? [])
            ->createBreadcrumbs('Need Descriptors')
            ->render(['pagination' => $pagination->toArray()]);
    }

    /**
     * edit
     *
     * 9/06/2020 12:44
     * @param INDescriptor|null $need
     * @return JsonResponse
     * @Route("/individual/need/add/",name="individual_need_add")
     * @Route("/individual/need/{need}/edit/",name="individual_need_edit")
     * @IsGranted("ROLE_ROUTE")
     */
    public function edit(?INDescriptor $need = null)
    {
        $manager = $this->getContainerManager();
        if (!$need instanceof INDescriptor) {
            $need = new INDescriptor();
            $action = $this->generateUrl('individual_need_add');
        } else {
            $action = $this->generateUrl('individual_need_edit', ['need' =>$need->getId()]);
        }

        $form = $this->createForm(INDescriptorType::class, $need, ['action' => $action]);

        if ($this->getRequest()->getContent() !== '') {
            $content = json_decode($this->getRequest()->getContent(), true);
            $form->submit($content);
            if ($form->isValid()) {
                $id = $need->getId();
                $provider = ProviderFactory::create(INDescriptor::class);
                $provider->persistFlush($need);
                if ($provider->isStatusSuccess() && $id !== $need->getId()) {
                    $data['redirect'] = $this->generateUrl('individual_need_edit', ['need' => $need->getId()]);
                    $data['status'] = 'redirect';
                    $this->getMessageStatusManager()->convertToFlash();
                }
            } else {
                $this->getMessageStatusManager()->error(MessageStatusManager::INVALID_INPUTS);
            }

            $manager->singlePanel($form->createView());
            return $this->getMessageStatusManager()->toJsonResponse(['form' => $manager->getFormFromContainer()]);
        }

        $pageHeader = new PageHeader($need->getId() !== null ? 'Edit Individual Need' : 'Add Individual Need');
        $manager->setReturnRoute($this->generateUrl('individual_need_list'))
            ->singlePanel($form->createView());
        if ($need->getId() !== null) {
            $manager->setAddElementRoute($this->generateUrl('individual_need_add'));
        }

        return $this->getPageManager()
            ->setPageHeader($pageHeader)
            ->createBreadcrumbs($pageHeader->getHeader(),
                [
                    ['uri' => 'individual_need_list', 'name' => 'Individual Need Descriptors']
                ]
            )
            ->render(['containers' => $manager->getBuiltContainers()]);
    }

    /**
     * sortDescriptor
     * @param INDescriptor $source
     * @param INDescriptor $target
     * @param INDescriptorPagination $pagination
     * @Route("/individual/need/{source}/descriptor/{target}/sort/",name="individual_need_descriptor_sort")
     * @IsGranted("ROLE_ROUTE")
     * @return JsonResponse
     * 9/06/2020 14:37
     */
    public function sortDescriptor(INDescriptor $source, INDescriptor $target, INDescriptorPagination $pagination)
    {
        $manager = new EntitySortManager();
        $manager->setSortField('sortOrder')
            ->setIndexName('sort_order')
            ->execute($source, $target, $pagination);

        return new JsonResponse($manager->getDetails());
    }

    /**
     * delete
     * @param INDescriptor $need
     * @param INDescriptorPagination $pagination
     * @Route("/individual/need/{need}/delete/",name="individual_need_delete")
     * @IsGranted("ROLE_ROUTE")
     * 9/06/2020 15:50
     */
    public function delete(INDescriptor $need, INDescriptorPagination $pagination)
    {
        ProviderFactory::create(INDescriptor::class)->delete($need);

        $data = ProviderFactory::create(INDescriptor::class)->getMessageManager()->pushToJsonData();

        return $this->list($pagination, $data);
    }
}