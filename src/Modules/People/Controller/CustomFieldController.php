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

use App\Container\ContainerManager;
use App\Controller\AbstractPageController;
use App\Manager\EntitySortManager;
use App\Modules\People\Entity\CustomField;
use App\Modules\People\Form\CustomFieldType;
use App\Modules\People\Pagination\CustomFieldPagination;
use App\Provider\ProviderFactory;
use App\Util\ErrorMessageHelper;
use App\Util\TranslationHelper;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Form\FormError;
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
     * @param array $errors
     * @return JsonResponse
     * @Route("/custom/field/list",name="custom_field_list")
     * @IsGranted("ROLE_ROUTE")
     */
    public function list(CustomFieldPagination $pagination, array $errors = [])
    {
        $content = ProviderFactory::getRepository(CustomField::class)->findBy([], ['displayOrder' => 'ASC', 'name' => 'ASC']);
        $pagination->setStack($this->getPageManager()->getStack())->setContent($content)
            ->setStoreFilterURL($this->generateUrl('custom_field_filter_save'))
            ->setDraggableRoute('custom_field_sort')
            ->setAddElementRoute($this->generateUrl('custom_field_add'));

        return $this->getPageManager()
            ->setMessages($errors)
            ->setUrl($this->generateUrl('custom_field_list'))
            ->createBreadcrumbs('Manage Custom Fields')
            ->render(['pagination' => $pagination->toArray()]);
    }

    /**
     * edit
     * @param ContainerManager $manager
     * @param CustomField|null $customField
     * @return JsonResponse
     * @Route("/custom/field/{customField}/edit/",name="custom_field_edit")
     * @Route("/custom/field/add",name="custom_field_add")
     * @IsGranted("ROLE_ROUTE")
     */
    public function edit(ContainerManager $manager, ?CustomField $customField = null)
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
            $data = [];
            if ($form->isValid()) {
                $id = $customField->getId();
                $data = ProviderFactory::create(CustomField::class)->persistFlush($customField, []);
                if ($data['status'] === 'success' && $id !== $customField->getId()) {
                    $data['status'] = 'redirect';
                    $data['redirect'] = $this->generateUrl('custom_field_edit', ['customField' => $customField->getId()]);
                    if (in_array($customField->getFieldType(), ['text', 'short_string', 'choice'])) {
                        $this->getRequest()->getSession()->set('newCustomField', true);
                    }
                    $this->addFlash('success', ErrorMessageHelper::onlySuccessMessage());
                    $this->addFlash('warning', ['You will need ensure that these options are appropriate for your field.', [], 'People']);
                } else if ($data['status'] === 'success') {
                    $form = $this->createForm(CustomFieldType::class, $customField, ['action' => $action]);
                }
            } else {
                $data = ErrorMessageHelper::getInvalidInputsMessage([],true);
            }
            $manager->singlePanel($form->createView());
            $data['form'] = $manager->getFormFromContainer();
            return new JsonResponse($data);
        }
        $manager->setReturnRoute($this->generateUrl('custom_field_list'));

        if (null !== $customField->getId()) {
            $manager->setAddElementRoute($this->generateUrl('custom_field_add'));
        }
        $manager->singlePanel($form->createView());

        return $this->getPageManager()->createBreadcrumbs($customField->getId() === null ? 'Add Custom Field' : 'Edit Custom Field')
            ->render(
                [
                    'containers' => $manager->getBuiltContainers(),
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
     * @param CustomField $source
     * @param CustomField $target
     * @param CustomFieldPagination $pagination
     * @Route("/custom/field/{source}/{target}/sort/",name="custom_field_sort")
     * @IsGranted("ROLE_ROUTE")
     * @return JsonResponse
     * 31/07/2020 08:51
     */
    public function sortCustomFields(CustomField $source, CustomField $target, CustomFieldPagination $pagination)
    {
        $manager = new EntitySortManager();
        $manager->setSortField('displayOrder')
            ->setIndexName('display_order')
            ->execute($source, $target, $pagination);

        return new JsonResponse($manager->getDetails());

    }

    /**
     * delete
     * @param CustomFieldPagination $pagination
     * @param CustomField $customField
     * @return JsonResponse
     * @Route("/custom/field/{customField}/delete",name="custom_field_delete")
     * @IsGranted("ROLE_ROUTE")
     * 1/08/2020 08:41
     */
    public function delete(CustomFieldPagination $pagination, CustomField $customField)
    {
        $provider = ProviderFactory::create(CustomField::class);
        $provider->delete($customField);
        $data = $provider->getMessageManager()->pushToJsonData();
        return $this->list($pagination, $data['errors'] ?? []);
    }
}