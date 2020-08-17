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
 * Time: 15:46
 */
namespace App\Modules\School\Controller;

use App\Container\ContainerManager;
use App\Controller\AbstractPageController;
use App\Manager\MessageStatusManager;
use App\Modules\School\Entity\Facility;
use App\Modules\School\Form\FacilitySettingsType;
use App\Modules\School\Form\FacilityType;
use App\Modules\School\Pagination\FacilityPagination;
use App\Modules\System\Manager\SettingFactory;
use App\Provider\ProviderFactory;
use App\Util\ErrorMessageHelper;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class FacilityController
 * @package App\Modules\School\Controller
 * @author Craig Rayner <craig@craigrayner.com>
 */
class FacilityController extends AbstractPageController
{
    /**
     * list
     *
     * 17/08/2020 13:54
     * @param FacilityPagination $pagination
     * @Route("/facility/list/", name="facility_list")
     * @IsGranted("ROLE_ROUTE")
     * @return JsonResponse
     */
    public function list(FacilityPagination $pagination)
    {
        $content = ProviderFactory::getRepository(Facility::class)->findBy([], ['name' => 'ASC']);
        
        $pagination->setContent($content)
            ->setAddElementRoute($this->generateUrl('facility_add'));
        
        return $this->getPageManager()->createBreadcrumbs('Facilities')
            ->setMessages($this->getMessageStatusManager()->getMessageArray())
            ->render(['pagination' => $pagination->toArray()]);
    }

    /**
     * edit
     *
     * 17/08/2020 13:51
     * @param Facility|null $facility
     * @Route("/facility/{facility}/edit/", name="facility_edit")
     * @Route("/facility/add/", name="facility_add")
     * @IsGranted("ROLE_ROUTE")
     * @return JsonResponse
     */
    public function edit(?Facility $facility = null)
    {
        $request = $this->getRequest();

        if (!$facility instanceof Facility) {
            $facility = new Facility();
            $action = $this->generateUrl('facility_add');
        } else {
            $action = $this->generateUrl('facility_edit', ['facility' => $facility->getId()]);
        }

        $form = $this->createForm(FacilityType::class, $facility, ['action' => $action, 'facility_setting_uri' => $this->generateUrl('facility_settings')]);
        $manager = $this->getContainerManager();

        if ($request->getContent() !== '') {
            $content = json_decode($request->getContent(), true);
            $form->submit($content);
            if ($form->isValid()) {
                $id = $facility->getId();
                ProviderFactory::create(Facility::class)->persistFlush($facility);
                if ($this->getMessageStatusManager()->isStatusSuccess() && $id !== $facility->getId()) {
                    $this->getMessageStatusManager()
                        ->setReDirect($this->generateUrl('facility_edit', ['facility' => $facility->getId()]))
                        ->convertToFlash();
                } else if ($this->getMessageStatusManager()->isStatusSuccess()) {
                    $form = $this->createForm(FacilityType::class, $facility, ['action' => $action, 'facility_setting_uri' => $this->generateUrl('facility_settings')]);
                }
            } else {
                $this->getMessageStatusManager()->error(MessageStatusManager::INVALID_INPUTS);
            }

            $manager->singlePanel($form->createView());
            return $this->generateJsonResponse(['form' => $manager->getFormFromContainer()]);
        }
        
        if ($facility->getId() !== null) {
            $manager->setAddElementRoute($this->generateUrl('facility_add'));
        }
        $manager->setReturnRoute($this->generateUrl('facility_list'))
            ->singlePanel($form->createView());

        return $this->getPageManager()->createBreadcrumbs($facility->getId() !== null ? 'Edit Facility' : 'Add Facility',
            [
                ['uri' => 'facility_list', 'name' => 'Facilities']
            ]
        )
            ->render(['containers' => $manager->getBuiltContainers()]);
    }

    /**
     * delete
     *
     * 17/08/2020 13:54
     * @param Facility $facility
     * @param FacilityPagination $pagination
     * @Route("/facility/{facility}/delete/", name="facility_delete")
     * @IsGranted("ROLE_ROUTE")
     * @return JsonResponse
     */
    public function delete(Facility $facility, FacilityPagination $pagination)
    {
        ProviderFactory::create(Facility::class)
            ->delete($facility);

        return $this->list($pagination);
    }

    /**
     * settings
     *
     * 17/08/2020 13:51
     * @Route("/facility/settings/", name="facility_settings")
     * @IsGranted("ROLE_ROUTE")
     * @return JsonResponse
     */
    public function settings()
    {
        $request = $this->getRequest();
        $manager = $this->getContainerManager();

        $form = $this->createForm(FacilitySettingsType::class, null, ['action' => $this->generateUrl('facility_settings',)]);

        if ($request->getContent() !== '') {
            try {
                SettingFactory::getSettingManager()->handleSettingsForm($form, $request);
                if ($this->getMessageStatusManager()->isStatusSuccess()) {
                    $form = $this->createForm(FacilitySettingsType::class, null, ['action' => $this->generateUrl('facility_settings',)]);
                }
            } catch (\Exception $e) {
                $this->getMessageStatusManager()->error(MessageStatusManager::INVALID_INPUTS);
            }

            $manager->singlePanel($form->createView());
            return $this->generateJsonResponse(['form' => $manager->getFormFromContainer()]);
        }

        $manager->singlePanel($form->createView());

        return $this->getPageManager()->createBreadcrumbs('Facility Settings')
            ->render(['containers' => $manager->getBuiltContainers()]);
    }
}