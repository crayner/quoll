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
 * Time: 10:29
 */
namespace App\Modules\Planner\Controller;

use App\Container\Container;
use App\Container\ContainerManager;
use App\Container\Panel;
use App\Container\Section;
use App\Controller\AbstractPageController;
use App\Manager\MessageStatusManager;
use App\Modules\Planner\Form\PlannerSettingType;
use App\Modules\System\Manager\SettingFactory;
use Exception;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class SettingController
 * @package App\Modules\Planner\Controller
 * @author Craig Rayner <craig@craigrayner.com>
 */
class SettingController extends AbstractPageController
{
    /**
     * settings
     *
     * 17/08/2020 16:11
     * @param string $tabName
     * @Route("/planner/settings/",name="planner_settings")
     * @Route("/planner/settings/",name="planner_configure")
     * @Route("/planner/settings/",name="planner_view")
     * @IsGranted("ROLE_ROUTE")
     * @return JsonResponse
     */
    public function settings(string $tabName = 'Templates')
    {
        $settingProvider = SettingFactory::getSettingManager();
        $settingProvider->getSettingsByScope('Planner');

        $form = $this->createForm(PlannerSettingType::class, null, ['action' => $this->generateUrl('planner_settings', ['tabName' => $tabName])]);
        $manager = $this->getContainerManager();

        if ($this->getRequest()->getContent() !== '') {
            try {
                $settingProvider->handleSettingsForm($form, $this->getRequest());
                if ($this->getMessageStatusManager()->isStatusSuccess()) {
                    $form = $this->createForm(PlannerSettingType::class, null, ['action' => $this->generateUrl('planner_settings', ['tabName' => $tabName])]);
                }
            } catch (Exception $e) {
                $this->getMessageStatusManager()->error(MessageStatusManager::INVALID_INPUTS);
            }

            $manager->singlePanel($form->createView());
            return $this->generateJsonResponse(['form' => $manager->getFormFromContainer()]);
        }

        $container = new Container($tabName);
        $panel = new Panel('Templates', 'Planner', new Section('form', 'single'));
        $container->setTranslationDomain('School')
            ->addForm('single', $form->createView())
            ->addPanel($panel)
        ;
        $panel = new Panel('Access', 'Planner', new Section('form', 'single'));
        $container->addPanel($panel);
        $panel = new Panel('Miscellaneous', 'Planner', new Section('form', 'single'));
        $container->addPanel($panel);

        $manager->addContainer($container);
        return $this->getPageManager()->createBreadcrumbs('Planner Settings', [])
            ->render(['containers' => $manager->getBuiltContainers()]);
    }
}
