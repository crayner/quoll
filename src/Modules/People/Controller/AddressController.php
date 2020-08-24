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

use App\Controller\AbstractPageController;
use App\Manager\StatusManager;
use App\Modules\People\Entity\Address;
use App\Modules\People\Entity\Locality;
use App\Modules\People\Form\AddressType;
use App\Modules\People\Form\LocalityType;
use App\Modules\People\Pagination\AddressPagination;
use App\Modules\People\Pagination\LocalityPagination;
use App\Provider\ProviderFactory;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Form\ChoiceList\View\ChoiceView;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class AddressController
 * @package App\Modules\People\Controller
 * @author Craig Rayner <craig@craigrayner.com>
 */
class AddressController extends AbstractPageController
{
    /**
     * list
     *
     * 25/08/2020 07:59
     * @param AddressPagination $pagination
     * @Route("/address/list/",name="address_list")
     * @IsGranted("ROLE_ROUTE")
     * @return JsonResponse
     */
    public function list(AddressPagination $pagination)
    {
        $content = ProviderFactory::getRepository(Address::class)->findBy([], ['streetName' => 'ASC', 'streetNumber' => 'ASC']);
        $pagination->setContent($content)
            ->setRefreshRoute($this->generateUrl('address_list'))
            ->setAddElementRoute(['url' => $this->generateUrl('address_add_popup'), 'target' => 'Address_Details', 'options' => 'width=800,height=600']);

        return $this->getPageManager()->createBreadcrumbs('Manage Addresses')
            ->setUrl($this->generateUrl('address_list'))
            ->render(['pagination' => $pagination->toArray()]);
    }

    /**
     * addressManage
     *
     * 25/08/2020 08:11
     * @param Address|null $address
     * @Route("/address/add/",name="address_add",methods={"GET"})
     * @Route("/address/add/popup/",name="address_add_popup",methods={"GET","POST"})
     * @Route("/address/{address}/edit/popup/",name="address_edit_popup",methods={"GET","POST"})
     * @IsGranted("ROLE_ROUTE")
     * @return JsonResponse
     */
    public function addressManage(?Address $address = null)
    {
        $request = $this->getRequest();

        if ($address === null) {
            $address = new Address();
            $action = $this->generateUrl('address_add_popup');
        } else {
            $action = $this->generateUrl('address_edit_popup', ['address' => $address->getId()]);
        }

        $form = $this->createForm(AddressType::class, $address, ['action' => $action]);
        if ($request->getContent() !== '') {
            $content = json_decode($request->getContent(), true);
            $form->submit($content);
            if ($form->isValid()) {
                $id = $address->getId();
                ProviderFactory::create(Address::class)->persistFlush($address);
                if ($this->isStatusSuccess() && $id !== $address->getId()) {
                    $this->getStatusManager()->setReDirect($this->generateUrl('address_edit_popup', ['address' => $address->getId()]), true);
                }
                $form = $this->createForm(AddressType::class, $address, ['action' => $action]);
            } else {
                $this->getStatusManager()->invalidInputs();
            }

            return $this->singleForm($form);
        }

        if ($address->getId() !== null) $this->getContainerManager()->setAddElementRoute($this->generateUrl('address_add'));

        return $this->getPageManager()->setPopup(true)->createBreadcrumbs($address->getId() !== null ? 'Edit Address' : 'Add Address',
                [
                    ['uri' => 'address_list', 'name' => 'Manage Addresses'],
                ]
            )
            ->render(
                [
                    'containers' => $this->getContainerManager()
                        ->singlePanel($form->createView())
                        ->getBuiltContainers(),
                ]
            );
    }

    /**
     * deleteAddress
     *
     * 25/08/2020 08:11
     * @param Address $address
     * @param AddressPagination $pagination
     * @Route("/address/{address}/delete/",name="address_delete")
     * @IsGranted("ROLE_ROUTE")
     * @return JsonResponse
     */
    public function deleteAddress(Address $address, AddressPagination $pagination)
    {
        ProviderFactory::create(Address::class)->delete($address);

        return $this->list($pagination);
    }

    /**
     * localityEdit
     *
     * 20/08/2020 15:22
     * @param Locality|null $locality
     * @Route("/locality/{locality}/edit/popup/",name="locality_edit_popup")
     * @Route("/locality/add/",name="locality_add")
     * @Route("/locality/add/popup/",name="locality_add_popup")
     * @IsGranted("ROLE_ROUTE")
     * @return JsonResponse
     */
    public function localityEdit(?Locality $locality = null)
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
                ProviderFactory::create(Locality::class)->persistFlush($locality);
                if ($this->isStatusSuccess() && $id !== $locality->getId()) {
                    $this->getStatusManager()->setReDirect($this->generateUrl('locality_edit_popup', ['locality' => $locality->getId()]))
                        ->convertToFlash();
                }
            } else {
                $this->getStatusManager()->error(StatusManager::INVALID_INPUTS);
            }
            return $this->singleForm($form);
        }

        if ($locality->getId() !== null) {
            $this->getContainerManager()->setAddElementRoute($this->generateUrl('locality_add'));
        }

        return $this->getPageManager()
            ->render(
                [
                    'containers' => $this->getContainerManager()->singlePanel($form->createView())->getBuiltContainers()
                ]
            );
    }

    /**
     * refreshChoiceList
     *
     * 20/08/2020 15:22
     * @Route("/address/list/refresh/",name="address_list_refresh")
     * @IsGranted("ROLE_ROUTE")
     * @return JsonResponse
     */
    public function refreshChoiceList()
    {
        $result = [];
        foreach(ProviderFactory::getRepository(Address::class)->findBy([],['streetNumber' => 'ASC','streetName' => 'ASC']) as $address) {
            $result[] = new ChoiceView($address, (string)$address->getId(), (string)$address->toString());
        }
        return new JsonResponse(['choices' => $result]);
    }

    /**
     * refreshLocalityChoiceList
     *
     * 20/08/2020 15:23
     * @Route("/locality/list/refresh/",name="locality_list_refresh")
     * @IsGranted("ROLE_ROUTE")
     * @return JsonResponse
     */
    public function refreshLocalityChoiceList()
    {
        $result = [];
        foreach(ProviderFactory::getRepository(Locality::class)->findBy([],['name' => 'ASC','territory' => 'ASC']) as $locality) {
            $result[] = new ChoiceView($locality, (string)$locality->getId(), (string)$locality->toString());
        }
        return new JsonResponse(['choices' => $result]);
    }

    /**
     * listLocality
     *
     * 20/08/2020 15:23
     * @param LocalityPagination $pagination
     * @Route("/locality/list/",name="locality_list")
     * @IsGranted("ROLE_ROUTE")
     * @return JsonResponse
     */
    public function listLocality(LocalityPagination $pagination)
    {
        $content = ProviderFactory::getRepository(Locality::class)->findBy([], ['name' => 'ASC']);
        $pagination->setContent($content)
            ->setRefreshRoute($this->generateUrl('locality_list'))
            ->setAddElementRoute(['url' => $this->generateUrl('locality_add_popup'), 'target' => 'Locality_Details', 'options' => 'width=800,height=450']);

        return $this->getPageManager()->createBreadcrumbs('Manage Localities')
            ->setMessages($this->getStatusManager()->getMessageArray())
            ->render(['pagination' => $pagination->toArray()]);
    }

    /**
     * deleteLocality
     *
     * 20/08/2020 15:23
     * @param Locality $locality
     * @param LocalityPagination $pagination
     * @Route("/locality/{locality}/delete/",name="locality_delete")
     * @IsGranted("ROLE_ROUTE")
     * @return JsonResponse
     */
    public function deleteLocality(Locality $locality, LocalityPagination $pagination)
    {
        ProviderFactory::create(Locality::class)->delete($locality);

        return $this->listLocality($pagination);
    }
}