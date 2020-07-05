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
 * Date: 11/05/2020
 * Time: 09:03
 */
namespace App\Modules\People\Controller;

use App\Container\ContainerManager;
use App\Controller\AbstractPageController;
use App\Modules\People\Entity\Person;
use App\Modules\People\Entity\Phone;
use App\Modules\People\Form\PhoneType;
use App\Modules\People\Pagination\PhonePagination;
use App\Provider\ProviderFactory;
use App\Util\ErrorMessageHelper;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Form\ChoiceList\View\ChoiceView;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class PhoneController
 * @package App\Modules\People\Controller
 */
class PhoneController extends AbstractPageController
{
    /**
     * list
     * @Route("/phone/list/",name="phone_list")
     * @IsGranted("ROLE_SUPPORT")
     * @param PhonePagination $pagination
     * @return JsonResponse
     */
    public function list(PhonePagination $pagination)
    {
        return $this->getPagination($pagination);
    }

    /**
     * edit
     * @param ContainerManager $manager
     * @param Phone|null $phone
     * @Route("/phone/add/popup/",name="phone_add_popup")
     * @Route("/phone/add/",name="phone_add")
     * @Route("/phone/{phone}/edit/popup/",name="phone_edit_popup")
     * Popup size is 700x350, in window called Phone_Details
     * @IsGranted("ROLE_SUPPORT")
     */
    public function edit(ContainerManager $manager, ?Phone $phone)
    {
        if (null === $phone) {
            $phone = new Phone();
            $action = $this->generateUrl('phone_add_popup');
        } else {
            $action = $this->generateUrl('phone_edit_popup', ['phone' => $phone->getId()]);
        }

        $form = $this->createForm(PhoneType::class, $phone, ['action' => $action]);

        if ($this->getRequest()->getContent() !== '') {
            $content = json_decode($this->getRequest()->getContent(), true);
            $form->submit($content);
            if ($form->isValid()) {
                $id = $phone->getId();
                $data = ProviderFactory::create(Phone::class)->persistFlush($phone);
                if ($id !== $phone->getId() && $data['status'] === 'success') {
                    $action = $this->generateUrl('phone_edit_popup', [ 'phone' => $phone->getId()]);
                    $form = $this->createForm(PhoneType::class, $phone, ['action' => $action]);
                    $data['redirect'] = $action;
                    $data['status'] = 'redirect';
                    $this->addFlash('success', ErrorMessageHelper::onlySuccessMessage());
                }
            } else {
                $data = ErrorMessageHelper::getInvalidInputsMessage([], true);
            }
            $manager->singlePanel($form->createView());
            $data['form'] = $manager->getFormFromContainer();
            return new JsonResponse($data);
        }

        if ($phone->getId() !== null) {
            $manager->setAddElementRoute($this->generateUrl('phone_add'));
        }
        $manager->singlePanel($form->createView());

        return $this->getPageManager()->setPopup()->createBreadcrumbs($phone->getId() !== null ? 'Edit Phone' : 'Add Phone',
            [
                ['uri' => 'phone_list', 'name' => 'Manage Phones'],
            ]
        )
            ->render(['containers' => $manager->getBuiltContainers()]);
    }

    /**
     * refreshChoiceList
     * @Route("/phone/list/refresh/",name="phone_refresh")
     * @IsGranted("ROLE_ROUTE")
     */
    public function refreshChoiceList()
    {
        $result = [];
        foreach(ProviderFactory::getRepository(Phone::class)->findBy([],['phoneNumber' => 'ASC']) as $phone) {
            $result[] = new ChoiceView($phone, $phone->getId(), $phone->__toString());
        }
        return new JsonResponse(['choices' => $result]);
    }

    /**
     * delete
     * @param Phone $phone
     * @param PhonePagination $pagination
     * @return JsonResponse
     * @Route("/phone/{phone}/delete/",name="phone_delete")
     * @IsGranted("ROLE_ROUTE")
     */
    public function delete(Phone $phone, PhonePagination $pagination)
    {
        ProviderFactory::create(Phone::class)->delete($phone);
        return $this->getPagination($pagination, ProviderFactory::create(Person::class)->getMessageManager()->pushToJsonData()['errors']);
    }

    /**
     * getPagination
     * @param PhonePagination $pagination
     * @param array $messages
     * @return JsonResponse
     */
    private function getPagination(PhonePagination $pagination, array $messages = [])
    {
        $content = ProviderFactory::getRepository(Phone::class)->findBy([],['phoneNumber' => 'ASC']);
        $pagination->setContent($content)
            ->setStack($this->getPageManager()->getStack())
            ->setAddElementRoute(['url' => $this->generateUrl('phone_add_popup'), 'target' => 'Phone_Details', 'options' => 'width=700,height=350'])
            ->setRefreshRoute($this->generateUrl('phone_list'))
        ;
        return $this->getPageManager()->createBreadcrumbs('Manage Phones')->setMessages($messages)
            ->render(
                [
                    'pagination' => $pagination->toArray(),
                    'url' => $this->generateUrl('phone_list'),
                    'title' => 'Manage Phones',
                ]
            );
    }
}
