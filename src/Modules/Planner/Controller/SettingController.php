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
use App\Modules\Planner\Form\PlannerSettingType;
use App\Modules\System\Manager\SettingFactory;
use App\Provider\ProviderFactory;
use App\Util\ErrorMessageHelper;
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
     * @param ContainerManager $manager
     * @param string $tabName
     * @return JsonResponse
     * @Route("/planner/settings/",name="planner_settings")
     * @Route("/planner/settings/",name="planner_configure")
     * @Route("/planner/settings/",name="planner_view")
     * @IsGranted("ROLE_ROUTE")
     * 12/06/2020 10:34
     */
    public function settings(ContainerManager $manager, string $tabName = 'Templates')
    {
        $settingProvider = SettingFactory::getSettingManager();
        $settingProvider->getSettingsByScope('Planner');

        $form = $this->createForm(PlannerSettingType::class, null, ['action' => $this->generateUrl('planner_settings', ['tabName' => $tabName])]);

        if ($this->getRequest()->getContent() !== '') {
            $data = [];
            $data['status'] = 'success';
            try {
                $data = $settingProvider->handleSettingsForm($form, $this->getRequest(), $data);
                if ($data['status'] === 'success') {
                    $form = $this->createForm(PlannerSettingType::class, null, ['action' => $this->generateUrl('planner_settings', ['tabName' => $tabName])]);
                }
            } catch (\Exception $e) {
                $data = ErrorMessageHelper::getDatabaseErrorMessage($data, true);
            }

            $manager->singlePanel($form->createView());
            $data['form'] = $manager->getFormFromContainer();

            return new JsonResponse($data, 200);
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
