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
 * Date: 13/06/2020
 * Time: 11:11
 */
namespace App\Modules\Behaviour\Controller;

use App\Container\Container;
use App\Container\ContainerManager;
use App\Container\Panel;
use App\Container\Section;
use App\Controller\AbstractPageController;
use App\Manager\StatusManager;
use App\Modules\Behaviour\Form\BehaviourSettingsType;
use App\Modules\System\Manager\SettingFactory;
use App\Provider\ProviderFactory;
use App\Util\ErrorMessageHelper;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class SettingController
 * @package App\Modules\Behaviour\Controller
 * @author Craig Rayner <craig@craigrayner.com>
 */
class SettingController extends AbstractPageController
{
    /**
     * settings
     * @param ContainerManager $manager
     * @param string $tabName
     * @return JsonResponse
     * @Route("/behaviour/settings/{tabName}",name="behaviour_settings")
     * @Route("/behaviour/settings/{tabName}",name="behaviour_configure")
     * @IsGranted("ROLE_ROUTE")
     * 13/06/2020 11:18
     */
    public function settings(string $tabName = 'Descriptors')
    {
        SettingFactory::getSettingManager()->getSettingsByScope('Behaviour');
        $manager = $this->getContainerManager();

        $form = $this->createForm(BehaviourSettingsType::class, null, ['action' => $this->generateUrl('behaviour_settings')]);

        if ($this->getRequest()->getContent() !== '') {
            try {
                SettingFactory::getSettingManager()->handleSettingsForm($form, $this->getRequest());
                if ($this->getStatusManager()->getStatus() === 'success') {
                    $form = $this->createForm(BehaviourSettingsType::class, null, ['action' => $this->generateUrl('behaviour_settings')]);
                }
            } catch (\Exception $e) {
                $this->getStatusManager()->error(StatusManager::INVALID_INPUTS);
            }

            $manager->singlePanel($form->createView());
            return $this->getStatusManager()->toJsonResponse(['form' => $manager->getFormFromContainer()]);
        }

        $container = new Container($tabName);
        $section = new Section('form','single');
        $panel = new Panel('Descriptors', 'Behaviour', $section);
        $container->setTranslationDomain('Behaviour')
            ->addForm('single', $form->createView())
            ->addPanel($panel);
        $panel = new Panel('Levels', 'Behaviour', $section);
        $container->addPanel($panel);
        $panel = new Panel('Letters', 'Behaviour', $section);
        $container->addPanel($panel);
        $panel = new Panel('Miscellaneous', 'Behaviour', $section);
        $container->addPanel($panel);

        $manager->addContainer($container);
        return $this->getPageManager()
            ->createBreadcrumbs('Behaviour Settings')
            ->render(['containers' => $manager->getBuiltContainers()]);
    }

}