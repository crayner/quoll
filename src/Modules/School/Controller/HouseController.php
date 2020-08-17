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
use App\Manager\MessageStatusManager;
use App\Modules\School\Entity\House;
use App\Modules\School\Form\HouseType;
use App\Modules\School\Pagination\HousePagination;
use App\Provider\ProviderFactory;
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
     *
     * 17/08/2020 13:38
     * @param HousePagination $pagination
     * @Route("/house/list/", name="house_list")
     * @IsGranted("ROLE_ROUTE")
     * @return JsonResponse
     */
    public function list(HousePagination $pagination)
    {
        $content = ProviderFactory::getRepository(House::class)->findBy([], ['name' => 'ASC']);
        $pagination->setContent($content)
            ->setAddElementRoute($this->generateUrl('house_add'));

        return $this->getPageManager()->createBreadcrumbs('Houses')
            ->setMessages($this->getMessageStatusManager()->getMessageArray())
            ->render(['pagination' => $pagination->toArray()]);
    }

    /**
     * edit
     *
     * 17/08/2020 13:38
     * @param ContainerManager $manager
     * @param House|null $house
     * @Route("/house/{house}/edit/", name="house_edit")
     * @Route("/house/add/", name="house_add")
     * @IsGranted("ROLE_ROUTE")
     * @return JsonResponse
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
                $provider->persistFlush($house);
                if ($this->getMessageStatusManager()->isStatusSuccess() && $id !== $house->getId()) {
                    $this->getMessageStatusManager()
                        ->getReDirect($this->generateUrl('house_edit', ['house' => $house->getId()]))
                        ->convertToFlash();
                }
            } else {
                $this->getMessageStatusManager()->error(MessageStatusManager::INVALID_INPUTS);
            }

            $manager->singlePanel($form->createView());
            return $this->getMessageStatusManager()->toJsonResponse(['form' => $manager->getFormFromContainer()]);
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
     *
     * 17/08/2020 13:38
     * @param House $house
     * @param HousePagination $pagination
     * @Route("/house/{house}/delete/", name="house_delete")
     * @IsGranted("ROLE_ROUTE")
    * @return JsonResponse
     */
    public function delete(House $house, HousePagination $pagination)
    {
        ProviderFactory::create(House::class)
            ->delete($house);

        return $this->list($pagination);
    }
}