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
 * Time: 10:49
 */
namespace App\Modules\Activity\Controller;

use App\Container\ContainerManager;
use App\Controller\AbstractPageController;
use App\Manager\StatusManager;
use App\Modules\Activity\Form\ActivitySettingsType;
use App\Modules\System\Manager\SettingFactory;
use App\Provider\ProviderFactory;
use App\Util\ErrorMessageHelper;
use Exception;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class SettingController
 * @package App\Modules\Activity\Controller
 * @author Craig Rayner <craig@craigrayner.com>
 */
class SettingController extends AbstractPageController
{
    /**
     * settings
     *
     * 18/08/2020 09:04
     * @Route("/activity/settings/", name="activity_settings")
     * @Route("/activity/settings/", name="activity_configuration")
     * @IsGranted("ROLE_ROUTE")
     * @return JsonResponse
     */
    public function settings()
    {
        $manager = SettingFactory::getSettingManager();
        $manager->getSettingsByScope('Activities');

        $form = $this->createForm(ActivitySettingsType::class, null, ['action' => $this->generateUrl('activity_settings')]);

        if ($this->getRequest()->getContent() !== '') {
            try {
                $manager->handleSettingsForm($form, $this->getRequest());
                if ($this->getStatusManager()->isStatusSuccess()) {
                    $form = $this->createForm(ActivitySettingsType::class, null, ['action' => $this->generateUrl('activity_settings')]);
                }
            } catch (Exception $e) {
                $this->getStatusManager()->error(StatusManager::INVALID_INPUTS);
            }

            $this->getContainerManager()->singlePanel($form->createView());
            return $this->generateJsonResponse(['form' => $this->getContainerManager()->getFormFromContainer()]);
        }

        return $this->getPageManager()->createBreadcrumbs('Activity Settings')
            ->render(
                [
                    'containers' => $this->getContainerManager()
                        ->singlePanel($form->createView())
                        ->getBuiltContainers()
                ]
            );
    }
}