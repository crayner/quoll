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
     * @Route("/custom/field/list",name="custom_field_list")
     * @Route("/custom/field/{customField}/delete",name="custom_field_delete")
     * @IsGranted("ROLE_ROUTE")
     */
    public function list(CustomFieldPagination $pagination)
    {
        $content = ProviderFactory::getRepository(CustomField::class)->findBy([], ['name' => 'ASC']);
        $pagination->setStack($this->getPageManager()->getStack())->setContent($content)
            ->setStoreFilterURL($this->generateUrl('custom_field_filter_save'))
            ->setAddElementRoute($this->generateUrl('custom_field_add'));

        return $this->getPageManager()->createBreadcrumbs('Manage Custom Fields')
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

        if ($this->getRequest()->getSession()->has('newCustomField') && $this->getRequest()->getSession()->get('newCustomField')) {
            $form->get('options')->addError(
                new FormError(
                    TranslationHelper::translate('You will need ensure that these options are appropriate for your field.', [], 'People')
                )
            );
            $this->getRequest()->getSession()->remove('newCustomField');
            $this->addFlash('warning', ['You will need ensure that these options are appropriate for your field.', [], 'People']);
        }

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
     * @return JsonResponse
     * @Route("/custom/field/filter/save/",name="custom_field_filter_save")
     * @IsGranted("ROLE_ROUTE")
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
}