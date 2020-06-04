<?php
/**
 * Created by PhpStorm.
 *
 * Quoll
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
use App\Modules\School\Entity\Facility;
use App\Modules\School\Form\FacilitySettingsType;
use App\Modules\School\Form\FacilityType;
use App\Modules\School\Pagination\FacilityPagination;
use App\Modules\System\Entity\Setting;
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
     * @param FacilityPagination $pagination
     * @param array $data
     * @return JsonResponse
     * @Route("/facility/list/", name="facility_list")
     * @IsGranted("ROLE_ROUTE")
     * 3/06/2020 15:50
     */
    public function list(FacilityPagination $pagination, array $data = [])
    {
        $content = ProviderFactory::getRepository(Facility::class)->findBy([], ['name' => 'ASC']);
        
        $pagination->setContent($content)->setAddElementRoute($this->generateUrl('facility_add'));
        
        return $this->getPageManager()->createBreadcrumbs('Facilities')
            ->render(['pagination' => $pagination->toArray()]);
    }

    /**
     * edit
     * @param ContainerManager $manager
     * @param Facility|null $facility
     * @return JsonResponse
     * @Route("/facility/{facility}/edit/", name="facility_edit")
     * @Route("/facility/add/", name="facility_add")
     * @IsGranted("ROLE_ROUTE")
     * 3/06/2020 16:00
     */
    public function edit(ContainerManager $manager, ?Facility $facility = null)
    {
        $request = $this->getRequest();

        if (!$facility instanceof Facility) {
            $facility = new Facility();
            $action = $this->generateUrl('facility_add');
        } else {
            $action = $this->generateUrl('facility_edit', ['facility' => $facility->getId()]);
        }

        $form = $this->createForm(FacilityType::class, $facility, ['action' => $action, 'facility_setting_uri' => $this->generateUrl('facility_settings')]);

        if ($request->getContent() !== '') {
            $content = json_decode($request->getContent(), true);
            $form->submit($content);
            if ($form->isValid()) {
                $id = $facility->getId();
                $provider = ProviderFactory::create(Facility::class);
                $data = $provider->persistFlush($facility, []);
                if ($data['status'] === 'success' && $id === $facility->getId()) {
                    $data['redirect'] = $this->generateUrl('facility_edit', ['facility' => $facility->getId()]);
                    $data['status'] = 'redirect';
                    $this->addFlash('success', ErrorMessageHelper::onlySuccessMessage());
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
     * @param Facility $facility
     * @param FacilityPagination $pagination
     * @return JsonResponse
     * @Route("/facility/{facility}/delete/", name="facility_delete")
     * @IsGranted("ROLE_ROUTE")
     * 3/06/2020 15:59
     */
    public function delete(Facility $facility, FacilityPagination $pagination)
    {
        $provider = ProviderFactory::create(Facility::class);

        $provider->delete($facility);

        $data = $provider->getMessageManager()->pushToJsonData();

        return $this->list($pagination, $data);
    }

    /**
     * settings
     * @param ContainerManager $manager
     * @return JsonResponse
     * @Route("/facility/settings/", name="facility_settings")
     * @IsGranted("ROLE_ROUTE")
     * 3/06/2020 16:08
     */
    public function settings(ContainerManager $manager)
    {
        $request = $this->getRequest();

        $form = $this->createForm(FacilitySettingsType::class, null, ['action' => $this->generateUrl('facility_settings',)]);

        if ($request->getContent() !== '') {
            $data = [];
            $data['status'] = 'success';
            try {
                $data['errors'] = ProviderFactory::create(Setting::class)->handleSettingsForm($form, $request);
                if ($data['status'] === 'success')
                    $form = $this->createForm(FacilitySettingsType::class, null, ['action' => $this->generateUrl('facility_settings',)]);
            } catch (\Exception $e) {
                $data = ErrorMessageHelper::getDatabaseErrorMessage($data, true);
            }

            $manager->singlePanel($form->createView());
            $data['form'] = $manager->getFormFromContainer();

            return new JsonResponse($data);
        }

        $manager->singlePanel($form->createView());

        return $this->getPageManager()->createBreadcrumbs('Facility Settings')
            ->render(['containers' => $manager->getBuiltContainers()]);
    }
}