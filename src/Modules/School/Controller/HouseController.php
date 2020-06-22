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
 * Date: 3/06/2020
 * Time: 08:00
 */
namespace App\Modules\School\Controller;

use App\Container\ContainerManager;
use App\Controller\AbstractPageController;
use App\Modules\School\Entity\House;
use App\Modules\School\Form\HouseType;
use App\Modules\School\Pagination\HousePagination;
use App\Provider\ProviderFactory;
use App\Util\ErrorMessageHelper;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class HouseController
 * @package App\Modules\School\Controller
 * @author Craig Rayner <craig@craigrayner.com>
 */
class HouseController extends AbstractPageController
{
    /**
     * list
     * @param HousePagination $pagination
     * @param array $data
     * @return JsonResponse
     * @Route("/house/list/", name="house_list")
     * @IsGranted("ROLE_ROUTE")
     * 3/06/2020 08:03
     */
    public function list(HousePagination $pagination, array $data = [])
    {
        $content = ProviderFactory::getRepository(House::class)->findBy([], ['name' => 'ASC']);
        $pagination->setContent($content)
            ->setAddElementRoute($this->generateUrl('house_add'));

        return $this->getPageManager()->createBreadcrumbs('Houses')
            ->render(['pagination' => $pagination->toArray()]);
    }

    /**
     * edit
     * @param ContainerManager $manager
     * @param House|null $house
     * @return JsonResponse
     * @Route("/house/{house}/edit/", name="house_edit")
     * @Route("/house/add/", name="house_add")
     * @IsGranted("ROLE_ROUTE")
     * 3/06/2020 09:22
     */
    public function edit(ContainerManager $manager, ?House $house = null)
    {
        $request = $this->getPageManager()->getRequest();
        if (!$house instanceof House) {
            $house = new House();
            $action = $this->generateUrl('house_add');
        } else {
            $action = $this->generateUrl('house_edit', ['house' => $house->getId()]);
        }

        $form = $this->createForm(HouseType::class, $house, ['action' => $action]);

        if ($request->getContent() !== '') {
            $content = json_decode($request->getContent(), true);
            $form->submit($content);
            if ($form->isValid()) {
                $id = $house->getId();
                $provider = ProviderFactory::create(House::class);
                $data = $provider->persistFlush($house, []);
                if ($data['status'] === 'success' && $id !== $house->getId()) {
                    $data['status'] = 'redirect';
                    $data['redirect'] = $this->generateUrl('house_edit', ['house' => $house->getId()]);
                } else if ($data['status'] !== 'success') {
                    $data = ErrorMessageHelper::getDatabaseErrorMessage([], true);
                }

            } else {
                $data = ErrorMessageHelper::getInvalidInputsMessage([], true);
            }

            $manager->singlePanel($form->createView());
            $data['form'] = $manager->getFormFromContainer('formContent', 'single');

            return new JsonResponse($data);
        }

        if ($house->getId() !== null) {
            $manager->setAddElementRoute($this->generateUrl('house_add'));
        }

        $manager->setReturnRoute($this->generateUrl('house_list'))->singlePanel($form->createView());

        return $this->getPageManager()->createBreadcrumbs($house->getId() !== null ? 'Edit House' : 'Add House',
            [
                ['uri' => 'house_list', 'name' => 'Houses']
            ]
        )
            ->render(['containers' => $manager->getBuiltContainers()]);
    }

    /**
     * delete
     * @param House $house
     * @param HousePagination $pagination
     * @return JsonResponse
     * @Route("/house/{house}/delete/", name="house_delete")
     * @IsGranted("ROLE_ROUTE")
     * 3/06/2020 09:21
     */
    public function delete(House $house, HousePagination $pagination)
    {
        $provider = ProviderFactory::create(House::class);

        $provider->delete($house);

        $data = $provider->getMessageManager()->pushToJsonData();

        return $this->list($pagination, $data);
    }
}