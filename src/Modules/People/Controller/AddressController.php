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
 * Date: 5/05/2020
 * Time: 13:27
 */
namespace App\Modules\People\Controller;

use App\Container\ContainerManager;
use App\Controller\AbstractPageController;
use App\Modules\People\Entity\Address;
use App\Modules\People\Entity\Locality;
use App\Modules\People\Form\AddressType;
use App\Modules\People\Form\LocalityType;
use App\Modules\People\Pagination\AddressPagination;
use App\Modules\People\Pagination\LocalityPagination;
use App\Provider\ProviderFactory;
use App\Util\ErrorMessageHelper;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Form\ChoiceList\View\ChoiceView;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class AddressController
 * @package App\Modules\People\Controller
 */
class AddressController extends AbstractPageController
{
    /**
     * list
     * @Route("/address/list/",name="address_list")
     * @IsGranted("ROLE_ROUTE")
     * @param AddressPagination $pagination
     * @return JsonResponse
     */
    public function list(AddressPagination $pagination)
    {
        $content = ProviderFactory::getRepository(Address::class)->findBy([], ['streetName' => 'ASC', 'streetNumber' => 'ASC']);
        $pagination->setContent($content)
            ->setRefreshRoute($this->generateUrl('address_list'))
            ->setAddElementRoute(['url' => $this->generateUrl('address_add_popup'), 'target' => 'Address_Details', 'options' => 'width=800,height=600']);

        return $this->getPageManager()->createBreadcrumbs('Manage Addresses')
            ->render(['pagination' => $pagination->toArray()]);
    }

    /**
     * Manage Address
     * @Route("/address/add/",name="address_add",methods={"GET"})
     * @Route("/address/add/popup/",name="address_add_popup",methods={"GET","POST"})
     * @Route("/address/{address}/edit/popup/",name="address_edit_popup",methods={"GET","POST"})
     * @IsGranted("ROLE_ROUTE")
     * @param ContainerManager $manager
     * @param Address|null $address
     * @return JsonResponse
     */
    public function addressManage(ContainerManager $manager, ?Address $address = null)
    {
        $request = $this->getRequest();

        if ($address === null) {
            $address = new Address();
            $action = $this->generateUrl('address_add_popup');
            $locality = new Locality();
        } else {
            $action = $this->generateUrl('address_edit_popup', ['address' => $address->getId()]);
            $locality = $address->getLocality();
        }

        $form = $this->createForm(AddressType::class, $address, ['action' => $action]);
        if ($request->getContent() !== '') {
            $content = json_decode($request->getContent(), true);
            $form->submit($content);
            if ($form->isValid()) {
                $id = $address->getId();
                $data = ProviderFactory::create(Address::class)->persistFlush($address, []);
                if ($data['status'] === 'success' && $id !== $address->getId()) {
                    $action = $this->generateUrl('address_edit_popup', ['address' => $address->getId()]);
                    $form = $this->createForm(AddressType::class, $address, ['action' => $action]);
                    $data['status'] = 'redirect';
                    $data['redirect'] = $action;
                    $this->addFlash('success', ErrorMessageHelper::onlySuccessMessage());
                }
            } else {
                $data = ErrorMessageHelper::getInvalidInputsMessage([], true);
            }
            $manager->singlePanel($form->createView());
            $data['form'] = $manager->getFormFromContainer();
            return new JsonResponse($data);
        }

        if ($address->getId() !== null)
            $manager->setAddElementRoute($this->generateUrl('address_add'));

        $manager->singlePanel($form->createView());

        return $this->getPageManager()->setPopup(true)->createBreadcrumbs($address->getId() !== null ? 'Edit Address' : 'Add Address',
                [
                    ['uri' => 'address_list', 'name' => 'Manage Addresses'],
                ]
            )
            ->render(
                [
                    'containers' => $manager->getBuiltContainers(),
                ]
            );
    }

    /**
     * @param Address $address
     * @param FlashBagInterface $bag
     * @return Response
     * @Route("/address/{address}/delete/",name="address_delete")
     * @IsGranted("ROLE_ROUTE")
     */
    public function deleteAddress(Address $address, FlashBagInterface $bag)
    {
        ProviderFactory::create(Address::class)->delete($address);
        ProviderFactory::create(Address::class)->getMessageManager()->pushToFlash($bag);

        return $this->forward(AddressController::class . '::list');
    }

    /**
     * localityEdit
     * @param ContainerManager $manager
     * @param Locality|null $locality
     * @return JsonResponse
     * @Route("/locality/{locality}/edit/popup/",name="locality_edit_popup")
     * @Route("/locality/add/",name="locality_add")
     * @Route("/locality/add/popup/",name="locality_add_popup")
     * @IsGranted("ROLE_ROUTE")
     */
    public function localityEdit(ContainerManager $manager, ?Locality $locality = null)
    {
        if (is_null($locality)) {
            $locality = new Locality();
            $action =  $this->generateUrl('locality_add_popup');
        } else {
            $action = $this->generateUrl('locality_edit_popup', ['locality' => $locality->getId()]);
        }

        $form = $this->createForm(LocalityType::class, $locality, ['action' => $action]);

        if ($this->getRequest()->getContent() !== '') {
            $content = json_decode($this->getRequest()->getContent(), true);
            $form->submit($content);
            if ($form->isValid()) {
                $id = $locality->getId();
                $data = ProviderFactory::create(Locality::class)->persistFlush($locality);
                if ($data['status'] === 'success' && $id !== $locality->getId()) {
                    $action = $this->generateUrl('locality_edit_popup', ['locality' => $locality->getId()]);
                    $form = $this->createForm(LocalityType::class, $locality, ['action' => $action]);
                    $data['status'] = 'redirect';
                    $data['redirect'] = $action;
                    $this->addFlash('success', ErrorMessageHelper::onlySuccessMessage());
                }
            } else {
                $data = ErrorMessageHelper::getInvalidInputsMessage([], true);
            }
            $manager->singlePanel($form->createView());
            $data['form'] = $manager->getFormFromContainer();
            return new JsonResponse($data);
        }

        if ($locality->getId() !== null) {
            $manager->setAddElementRoute($this->generateUrl('locality_add'));
        }
        $manager->singlePanel($form->createView());
        
        return $this->getPageManager()->render(['containers' => $manager->getBuiltContainers()]);
        
    }

    /**
     * refreshChoiceList
     * @Route("/address/list/refresh/",name="address_list_refresh")
     * @IsGranted("ROLE_ROUTE")
     */
    public function refreshChoiceList()
    {
        $result = [];
        foreach(ProviderFactory::getRepository(Address::class)->findBy([],['streetNumber' => 'ASC','streetName' => 'ASC']) as $address) {
            $result[] = new ChoiceView($address, $address->getId(), $address->toString());
        }
        return new JsonResponse(['choices' => $result]);
    }

    /**
     * refreshLocalityChoiceList
     * @Route("/locality/list/refresh/",name="locality_list_refresh")
     * @IsGranted("ROLE_ROUTE")
     */
    public function refreshLocalityChoiceList()
    {
        $result = [];
        foreach(ProviderFactory::getRepository(Locality::class)->findBy([],['name' => 'ASC','territory' => 'ASC']) as $locality) {
            $result[] = new ChoiceView($locality, $locality->getId(), $locality->toString());
        }
        return new JsonResponse(['choices' => $result]);
    }

    /**
     * list locality
     * @Route("/locality/list/",name="locality_list")
     * @IsGranted("ROLE_ROUTE")
     * @param LocalityPagination $pagination
     * @return JsonResponse
     */
    public function listLocality(LocalityPagination $pagination)
    {
        $content = ProviderFactory::getRepository(Locality::class)->findBy([], ['name' => 'ASC']);
        $pagination->setContent($content)
            ->setRefreshRoute($this->generateUrl('locality_list'))
            ->setAddElementRoute(['url' => $this->generateUrl('locality_add_popup'), 'target' => 'Locality_Details', 'options' => 'width=800,height=450']);

        return $this->getPageManager()->createBreadcrumbs('Manage Localities')
            ->render(['pagination' => $pagination->toArray()]);
    }

    /**
     * deleteLocality
     * @param Locality $locality
     * @param FlashBagInterface $bag
     * @return Response
     * @Route("/locality/{locality}/delete/",name="locality_delete")
     * @IsGranted("ROLE_ROUTE")
     */
    public function deleteLocality(Locality $locality, FlashBagInterface $bag)
    {
        ProviderFactory::create(Locality::class)->delete($locality);
        ProviderFactory::create(Locality::class)->getMessageManager()->pushToFlash($bag);

        return $this->forward(AddressController::class . '::listLocality');
    }
}