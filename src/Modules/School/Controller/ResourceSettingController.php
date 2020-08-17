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
 * Date: 12/06/2020
 * Time: 09:43
 */
namespace App\Modules\School\Controller;

use App\Container\Container;
use App\Container\Panel;
use App\Container\Section;
use App\Controller\AbstractPageController;
use App\Manager\StatusManager;
use App\Modules\School\Form\ResourceSettingsType;
use App\Modules\System\Manager\SettingFactory;
use Exception;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class ResourceSettingController
 * @package App\Modules\School\Controller
 * @author Craig Rayner <craig@craigrayner.com>
 */
class ResourceSettingController extends AbstractPageController
{
    /**
     * settings
     *
     * 17/08/2020 16:04
     * @param string|null $tabName
     * @Route("/resource/settings/",name="resource_settings")
     * @IsGranted("ROLE_ROUTE")
     * @return JsonResponse
     */
    public function settings(?string $tabName = 'Category')
    {
        $settingProvider = SettingFactory::getSettingManager();
        $settingProvider->getSettingsByScope('Resources');

        $form = $this->createForm(ResourceSettingsType::class, null, ['action' => $this->generateUrl('resource_settings', ['tabName' => $tabName])]);
        $manager = $this->getContainerManager();

        if ($this->getRequest()->getContent() !== '') {
            try {
                $settingProvider->handleSettingsForm($form, $this->getRequest());
                if ($this->getStatusManager()->isStatusSuccess()) {
                    $form = $this->createForm(ResourceSettingsType::class, null, ['action' => $this->generateUrl('resource_settings', ['tabName' => $tabName])]);
                }
            } catch (Exception $e) {
                $this->getStatusManager()->error(StatusManager::INVALID_INPUTS);
            }

            $manager->singlePanel($form->createView());
            return $this->generateJsonResponse(['form' => $manager->getFormFromContainer()]);
        }

        $container = new Container($tabName);
        $panel = new Panel('Category', 'School', new Section('form', 'Features'));
        $container->setTranslationDomain('School')
            ->addForm('Features', $form->createView())
            ->addPanel($panel)
        ;
        $panel = new Panel('Purpose', 'School', new Section('form', 'Features'));
        $container->addPanel($panel);

        $manager->addContainer($container);

        return $this->getPageManager()
            ->createBreadcrumbs('Resource Settings', [])
            ->render(['containers' => $manager->getBuiltContainers()]);
    }
}
