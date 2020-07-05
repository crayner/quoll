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
use App\Modules\Activity\Form\ActivitySettingsType;
use App\Modules\System\Manager\SettingFactory;
use App\Provider\ProviderFactory;
use App\Util\ErrorMessageHelper;
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
     * @param ContainerManager $manager
     * @return JsonResponse
     * @Route("/activity/settings/", name="activity_settings")
     * @Route("/activity/settings/", name="activity_configuration")
     * @IsGranted("ROLE_ROUTE")
     * 4/06/2020 10:52
     */
    public function settings(ContainerManager $manager)
    {
        $request = $this->getRequest();

        $settingProvider = SettingFactory::getSettingManager();
        $settingProvider->getSettingsByScope('Activities');

        $form = $this->createForm(ActivitySettingsType::class, null, ['action' => $this->generateUrl('activity_settings')]);

        if ($request->getContent() !== '') {
            $data = [];
            $data['status'] = 'success';
            try {
                $data['errors'] = $settingProvider->handleSettingsForm($form, $request);
                if ($data['status'] === 'success') {
                    $form = $this->createForm(ActivitySettingsType::class, null, ['action' => $this->generateUrl('activity_settings')]);
                }
            } catch (\Exception $e) {
                $data = ErrorMessageHelper::getDatabaseErrorMessage($data, true);
            }

            $manager->singlePanel($form->createView());
            $data['form'] = $manager->getFormFromContainer();

            return new JsonResponse($data);
        }

        // Finally Finished
        $manager->singlePanel($form->createView());
        return $this->getPageManager()->createBreadcrumbs('Activity Settings')
            ->render(['containers' => $manager->getBuiltContainers()]);
    }
}