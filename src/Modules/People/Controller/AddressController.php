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
use App\Modules\People\Manager\AddressManager;
use App\Modules\People\Pagination\AddressPagination;
use App\Provider\ProviderFactory;
use App\Util\ErrorMessageHelper;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Form\ChoiceList\View\ChoiceView;
use Symfony\Component\HttpFoundation\JsonResponse;
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
            ->setAddElementRoute($this->generateUrl('address_add'));

        return $this->getPageManager()->createBreadcrumbs('Manage Addresses')
            ->render(['pagination' => $pagination->toArray()]);
    }

    /**
     * Manage Address
     * @Route("/address/add/",name="address_add")
     * @Route("/address/add/popup/",name="address_add_popup")
     * @Route("/address/{address}/edit/",name="address_edit_popup")
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

        if ($address->getId() > 0)
            $manager->setAddElementRoute($this->generateUrl('address_add'));

        $manager->singlePanel($form->createView());

        return $this->getPageManager()->setPopup(true)->createBreadcrumbs($address->getId() > 0 ? 'Edit Address' : 'Add Address',
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
     * @Route("/address/{address}/delete/",name="address_delete")
     * @param Address $address
     */
    public function deleteAddress(Address $address){}

    /**
     * localityEdit
     * @param ContainerManager $manager
     * @param Locality|null $locality
     * @return JsonResponse
     * @Route("/locality/{locality}/edit/",name="locality_edit_popup")
     * @Route("/locality/add/",name="locality_add")
     * @Route("/locality/add/popup/",name="locality_add_popup")
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

            $data = [];
            $form->submit($content['locality']);
            if ($form->isValid()) {
                $data = ProviderFactory::create(Locality::class)->persistFlush($locality);
            } else {
                $data = ErrorMessageHelper::getInvalidInputsMessage([], true);
            }
            
            $manager->singlePanel($form->createView());
            $data['form'] = $manager->getFormFromContainer();

            return new JsonResponse($data);
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
     */
    public function refreshLocalityChoiceList()
    {
        $result = [];
        foreach(ProviderFactory::getRepository(Locality::class)->findBy([],['name' => 'ASC','territory' => 'ASC']) as $locality) {
            $result[] = new ChoiceView($locality, $locality->getId(), $locality->toString());
        }
        return new JsonResponse(['choices' => $result]);
    }

}