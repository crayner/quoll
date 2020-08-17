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

use App\Controller\AbstractPageController;
use App\Manager\StatusManager;
use App\Modules\School\Form\DashboardSettingsType;
use App\Modules\System\Manager\SettingFactory;
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
     *
     * 18/08/2020 08:53
     * @Route("/dashboard/settings/",name="dashboard_settings")
     * @IsGranted("ROLE_ROUTE")
     * @return JsonResponse
     */
    public function settings()
    {
        // System Settings
        $form = $this->createForm(DashboardSettingsType::class, null, ['action' => $this->generateUrl('dashboard_settings',)]);

        if ($this->getRequest()->getContent() !== '') {
            try {
                SettingFactory::getSettingManager()->handleSettingsForm($form, $this->getRequest());
                if ($this->getStatusManager()->isStatusSuccess())
                    $form = $this->createForm(DashboardSettingsType::class, null, ['action' => $this->generateUrl('dashboard_settings')]);
            } catch (\Exception $e) {
                $this->getStatusManager()->error(StatusManager::INVALID_INPUTS);
            }

            $this->getContainerManager()->singlePanel($form->createView());
            return $this->generateJsonResponse(['form' => $this->getContainerManager()->getFormFromContainer()]);
        }

        return $this->getPageManager()->createBreadcrumbs('Dashboard Settings')
            ->render(
                [
                    'containers' => $this->getContainerManager()
                        ->singlePanel($form->createView())
                        ->getBuiltContainers()
                ]
            );
    }
}
