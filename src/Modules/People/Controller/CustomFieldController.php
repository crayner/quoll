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
 * Date: 18/05/2020
 * Time: 10:29
 */
namespace App\Modules\People\Controller;

use App\Controller\AbstractPageController;
use App\Manager\EntitySortManager;
use App\Modules\People\Entity\CustomField;
use App\Modules\People\Form\CustomFieldType;
use App\Modules\People\Pagination\CustomFieldPagination;
use App\Provider\ProviderFactory;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class CustomFieldController
 * @package App\Modules\People\Controller
 */
class CustomFieldController extends AbstractPageController
{
    /**
     * list
     * @param CustomFieldPagination $pagination
     * @return JsonResponse
     * @Route("/custom/field/list",name="custom_field_list")
     * @IsGranted("ROLE_ROUTE")
     */
    public function list(CustomFieldPagination $pagination)
    {
        $content = ProviderFactory::getRepository(CustomField::class)->findBy([], ['displayOrder' => 'ASC', 'name' => 'ASC']);
        $pagination->setStack($this->getPageManager()->getStack())->setContent($content)
            ->setStoreFilterURL($this->generateUrl('custom_field_filter_save'))
            ->setDraggableRoute('custom_field_sort')
            ->setAddElementRoute($this->generateUrl('custom_field_add'));

        return $this->getPageManager()
            ->setMessages($this->getStatusManager()->getMessageArray())
            ->setUrl($this->generateUrl('custom_field_list'))
            ->createBreadcrumbs('Manage Custom Fields')
            ->render(['pagination' => $pagination->toArray()]);
    }

    /**
     * edit
     *
     * 24/08/2020 14:07
     * @param CustomField|null $customField
     * @Route("/custom/field/{customField}/edit/",name="custom_field_edit")
     * @Route("/custom/field/add",name="custom_field_add")
     * @IsGranted("ROLE_ROUTE")
     * @return JsonResponse
     */
    public function edit(?CustomField $customField = null)
    {
        if (null === $customField) {
            $customField = new CustomField();
            $action = $this->generateUrl('custom_field_add');
        } else {
            $action = $this->generateUrl('custom_field_edit', ['customField' => $customField->getId()]);
        }

        $form = $this->createForm(CustomFieldType::class, $customField, ['action' => $action]);

        if ($this->getRequest()->getMethod() === 'POST' && $this->getRequest()->getContent() !== '') {
            $content = json_decode($this->getRequest()->getContent(), true);
            $form->submit($content);
            if ($form->isValid()) {
                $id = $customField->getId();
                ProviderFactory::create(CustomField::class)->persistFlush($customField);
                if ($this->isStatusSuccess() && $id !== $customField->getId()) {
                    $this->getStatusManager()->setReDirect($this->generateUrl('custom_field_edit', ['customField' => $customField->getId()]),true);
                } else if ($this->isStatusSuccess()) {
                    $form = $this->createForm(CustomFieldType::class, $customField, ['action' => $action]);
                }
            } else {
                $this->getStatusManager()->invalidInputs();
            }

            return $this->singleForm($form);
        }

        if (null !== $customField->getId()) $this->getContainerManager()->setAddElementRoute($this->generateUrl('custom_field_add'));

        return $this->getPageManager()
            ->createBreadcrumbs($customField->getId() === null ? 'Add Custom Field' : 'Edit Custom Field')
            ->render(
                [
                    'containers' => $this->getContainerManager()
                        ->setReturnRoute($this->generateUrl('custom_field_list'))
                        ->singlePanel($form->createView())
                        ->getBuiltContainers(),
                ]
            );
    }

    /**
     * filterSave
     * @param CustomFieldPagination $pagination
     * @Route("/custom/field/filter/save/",name="custom_field_filter_save")
     * @IsGranted("ROLE_ROUTE")
     * @return JsonResponse
     * 31/07/2020 08:51
     */
    public function filterSave(CustomFieldPagination $pagination)
    {
        if ($this->getPageManager()->getRequest()->getContent() !== '') {
            $filter = json_decode($this->getPageManager()->getRequest()->getContent(), true);
            $pagination->setStack($this->getPageManager()->getStack())
                ->writeFilter($filter);
        }
        return new JsonResponse([]);
    }

    /**
     * sortCustomFields
     *
     * 24/08/2020 14:47
     * @param CustomField $source
     * @param CustomField $target
     * @param CustomFieldPagination $pagination
     * @param EntitySortManager $manager
     * @Route("/custom/field/{source}/{target}/sort/",name="custom_field_sort")
     * @IsGranted("ROLE_ROUTE")
     * @return JsonResponse
     */
    public function sortCustomFields(CustomField $source, CustomField $target, CustomFieldPagination $pagination, EntitySortManager $manager)
    {
        $manager->setSortField('displayOrder')
            ->setIndexName('display_order')
            ->execute($source, $target, $pagination);

        return $manager->getMessages()->toJsonResponse(['content' => $manager->getPaginationContent()]);
    }

    /**
     * delete
     *
     * 24/08/2020 14:32
     * @param CustomFieldPagination $pagination
     * @param CustomField $customField
     * @Route("/custom/field/{customField}/delete",name="custom_field_delete")
     * @IsGranted("ROLE_ROUTE")
     * @return JsonResponse
     */
    public function delete(CustomFieldPagination $pagination, CustomField $customField)
    {
        ProviderFactory::create(CustomField::class)
            ->delete($customField);
        return $this->list($pagination);
    }
}