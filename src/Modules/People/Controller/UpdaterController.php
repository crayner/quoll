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
 * Date: 18/08/2020
 * Time: 13:43
 */
namespace App\Modules\People\Controller;

use App\Container\Container;
use App\Container\ContainerManager;
use App\Container\Panel;
use App\Container\Section;
use App\Controller\AbstractPageController;
use App\Manager\StatusManager;
use App\Modules\People\Form\UpdaterSettingsType;
use App\Modules\People\Manager\RequiredUpdates;
use App\Modules\System\Manager\SettingFactory;
use App\Util\ErrorMessageHelper;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class UpdaterController
 * @package App\Modules\People\Controller
 * @author Craig Rayner <craig@craigrayner.com>
 */
class UpdaterController extends AbstractPageController
{
    /**
     * updaterSettings
     *
     * 18/08/2020 13:44
     * @param RequiredUpdates $required
     * @param string $tabName
     * @Route("/updater/settings/{tabName}", name="updater_settings")
     * @IsGranted("ROLE_ROUTE")
     * @return JsonResponse
     */
    public function updaterSettings(RequiredUpdates $required, string $tabName = 'Settings')
    {
        $form = $this->createForm(UpdaterSettingsType::class, null, ['action' => $this->generateUrl('updater_settings')]);

        if ($this->getRequest()->getContent() !== '') {
            $manager = SettingFactory::getSettingManager();
            try {
                $manager->handleSettingsForm($form, $this->getRequest());
                if ($this->getStatusManager()->isStatusSuccess()) {
                    $form = $this->createForm(UpdaterSettingsType::class, null, ['action' => $this->generateUrl('updater_settings')]);
                }
            } catch (\Exception $e) {
                $this->getStatusManager()->error(StatusManager::INVALID_INPUTS);
            }
            $this->getContainerManager()->singlePanel($form->createView());
            return $this->generateJsonResponse(['form' => $this->getContainerManager()->getFormFromContainer()]);
        }

        $container = new Container($tabName);
        $panel = new Panel('Settings', 'People', new Section('form', 'Settings'));
        $container->addPanel($panel);
        $container->addForm('Settings', $form->createView());
        $panel = new Panel('Required Data', 'People', new Section('special', $required->toArray()));
        $container->addPanel($panel);

        return $this->getPageManager()
            ->createBreadcrumbs('Data Updater Settings')
            ->render(
                [
                    'containers' => $this->getContainerManager()->addContainer($container)->getBuiltContainers()
                ]
            );
    }

    /**
     * storeRequiredDataUpdates
     *
     * 18/08/2020 14:00
     * @param RequiredUpdates $required
     * @Route("/updater/store/required/", name="updater_settings_store")
     * @IsGranted("ROLE_ROUTE")
     * @return JsonResponse
     */
    public function storeRequiredDataUpdates(RequiredUpdates $required)
    {
        $content = json_decode($this->getRequest()->getContent(), true);
        $required->handleRequest($content['requiredDataUpdates']);

        return $this->generateJsonResponse(['settings' => $required->toArray()]);

    }

}
