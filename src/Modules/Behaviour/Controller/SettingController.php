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
use App\Modules\Behaviour\Form\BehaviourSettingsType;
use App\Modules\System\Entity\Setting;
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
    public function settings(ContainerManager $manager, string $tabName = 'Descriptors')
    {
        ProviderFactory::create(Setting::class)->getSettingsByScope('Behaviour');

        $form = $this->createForm(BehaviourSettingsType::class, null, ['action' => $this->generateUrl('behaviour_settings')]);

        if ($this->getRequest()->getContent() !== '') {
            $data = [];
            $data['status'] = 'success';
            try {
                $data['errors'] = ProviderFactory::create(Setting::class)->handleSettingsForm($form, $this->getRequest());
                $form = $this->createForm(BehaviourSettingsType::class, null, ['action' => $this->generateUrl('behaviour_settings')]);
            } catch (\Exception $e) {
                $data = ErrorMessageHelper::getDatabaseErrorMessage($data, true);
            }

            $manager->singlePanel($form->createView());
            $data['form'] = $manager->getFormFromContainer();

            return new JsonResponse($data, 200);
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