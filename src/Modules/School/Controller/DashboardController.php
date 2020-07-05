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
 * Date: 4/06/2020
 * Time: 11:29
 */
namespace App\Modules\School\Controller;

use App\Container\ContainerManager;
use App\Controller\AbstractPageController;
use App\Modules\School\Form\DashboardSettingsType;
use App\Modules\System\Manager\SettingFactory;
use App\Provider\ProviderFactory;
use App\Util\ErrorMessageHelper;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class DashboardController
 * @package App\Modules\School\Controller
 * @author Craig Rayner <craig@craigrayner.com>
 */
class DashboardController extends AbstractPageController
{
    /**
     * settings
     * @param ContainerManager $manager
     * @return JsonResponse
     * @Route("/dashboard/settings/",name="dashboard_settings")
     * @IsGranted("ROLE_ROUTE")
     * 4/06/2020 11:32
     */
    public function settings(ContainerManager $manager)
    {
        $request = $this->getRequest();
        // System Settings
        $form = $this->createForm(DashboardSettingsType::class, null, ['action' => $this->generateUrl('dashboard_settings',)]);

        if ($request->getContent() !== '') {
            $data = [];
            $data['status'] = 'success';
            try {
                $data['errors'] = SettingFactory::getSettingManager()->handleSettingsForm($form, $request);
                if ($data['status'] === 'success')
                    $form = $this->createForm(DashboardSettingsType::class, null, ['action' => $this->generateUrl('dashboard_settings')]);
            } catch (\Exception $e) {
                $data = ErrorMessageHelper::getDatabaseErrorMessage($data, true);
            }

            $manager->singlePanel($form->createView());
            $data['form'] = $manager->getFormFromContainer();

            return new JsonResponse($data, 200);
        }

        $manager->singlePanel($form->createView());

        return $this->getPageManager()->createBreadcrumbs('Dashboard Settings')
            ->render(['containers' => $manager->getBuiltContainers()]);
    }

}