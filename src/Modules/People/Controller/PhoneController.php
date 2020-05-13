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
 * Date: 11/05/2020
 * Time: 09:03
 */
namespace App\Modules\People\Controller;

use App\Container\ContainerManager;
use App\Controller\AbstractPageController;
use App\Modules\People\Entity\Phone;
use App\Modules\People\Form\PhoneType;
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
     * edit
     * @param ContainerManager $manager
     * @param Phone|null $phone
     * @Route("/phone/list/",name="phone_list")
     * @Route("/phone/list/",name="phone_delete")
     * @Route("/phone/add/popup/",name="phone_add_popup")
     * @Route("/phone/add/",name="phone_add")
     * @Route("/phone/{phone}/edit/popup/",name="phone_edit_popup")
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

        if ($phone->getId() > 0) {
            $manager->setAddElementRoute($this->generateUrl('phone_add'));
        }
        $manager->singlePanel($form->createView());

        return $this->getPageManager()->setPopup()->createBreadcrumbs($phone->getId() > 0 ? 'Edit Phone' : 'Add Phone',
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
}
