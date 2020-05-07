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
     * @Route("/address/add/{return}",name="address_add")
     * @Route("/address/{address}/edit/{return}",name="address_edit")
     * @IsGranted("ROLE_ROUTE")
     * @param ContainerManager $manager
     * @param Address|null $address
     * @param string|null $return
     * @return JsonResponse
     */
    public function addressManage(ContainerManager $manager, ?Address $address = null, ?string $return = null)
    {
        $request = $this->getRequest();
        if (is_null($return)) {
            $return = $this->generateUrl('address_list');
        } else {
            $return = base64_decode($return);
        }

        if ($address === null) {
            $address = new Address();
            $action = $this->generateUrl('address_add', ['return' => base64_encode($return)]);
            $locality = new Locality();
        } else {
            $action = $this->generateUrl('address_edit', ['address' => $address->getId(), 'return' => base64_encode($return)]);
            $locality = $address->getLocality();
        }

        $form = $this->createForm(AddressType::class, $address, ['action' => $action]);
        if ($request->getContent() !== '') {
            $content = json_decode($request->getContent(), true);
            $form->submit($content['address']);
            if ($form->isValid()) {
                $data = ProviderFactory::create(Address::class)->persistFlush($address, []);
                dump($address);
            } else {
                $data = ErrorMessageHelper::getInvalidInputsMessage([], true);
            }
            $manager->singlePanel($form->createView());
            $data['form'] = $manager->getFormFromContainer();
            $data['address_id'] = $address->getId() > 0 ? $address->getId() : 0;
            $data['locality_id'] = $address->getLocality() ? $address->getLocality()->getId() : 0;
            return new JsonResponse($data);
        }

        if ($address->getId() > 0)
            $manager->setAddElementRoute($this->generateUrl('address_add', ['return' => base64_encode($return)]));

        $localityForm = $this->createForm(LocalityType::class, $locality, ['action' => $this->generateUrl('locality_edit', ['locality' => $locality->getId() ?: 0])]);

        $addressManager = new AddressManager($address, $form->createView(), $localityForm->createView());
        $addressManager->setReturn($return);

        return $this->getPageManager()->setPopup(true)->createBreadcrumbs($address->getId() > 0 ? 'Edit Address' : 'Add Address',
                [
                    ['uri' => 'address_list', 'name' => 'Manage Addresses'],
                ]
            )
            ->render(
                [
                    'special' => $addressManager->toArray(),
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
     * @param Locality|null $locality
     * @Route("/locality/{locality}/edit/",name="locality_edit")
     * @Route("/locality/add/",name="locality_add")
     */
    public function localityEdit(?Locality $locality = null)
    {
        if (is_null($locality)) {
            $locality = new Locality();
        }

        $form = $this->createForm(LocalityType::class, $locality, ['action' => $this->generateUrl('locality_add')]);

        $content = json_decode($this->getRequest()->getContent(), true);

        $data = [];
        $form->submit($content['locality']);
        if ($form->isValid()) {
            $data = ProviderFactory::create(Locality::class)->persistFlush($locality);
        } else {
            $data = ErrorMessageHelper::getInvalidInputsMessage([], true);
        }
        $data['form'] = $form->createView()->vars['toArray'];
        $data['locality_list'] = AddressManager::getLocalityList();
        $data['locality_choices'] = AddressManager::getLocalityChoices();
        $data['locality_id'] = $locality->getId();

        return new JsonResponse($data);
    }
}