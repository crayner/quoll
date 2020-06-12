<?php
/**
 * Created by PhpStorm.
 *
 * Quoll
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
use App\Container\ContainerManager;
use App\Container\Panel;
use App\Controller\AbstractPageController;
use App\Modules\School\Form\ResourceSettingsType;
use App\Modules\System\Entity\Setting;
use App\Provider\ProviderFactory;
use App\Util\ErrorMessageHelper;
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
     * @param ContainerManager $manager
     * @param string|null $tabName
     * @return JsonResponse
     * @Route("/resource/settings/",name="resource_settings")
     * @IsGranted("ROLE_ROUTE")
     * 12/06/2020 09:49
     */
    public function settings(ContainerManager $manager, ?string $tabName = 'Category')
    {
        $settingProvider = ProviderFactory::create(Setting::class);
        $settingProvider->getSettingsByScope('Resources');

        $form = $this->createForm(ResourceSettingsType::class, null, ['action' => $this->generateUrl('resource_settings', ['tabName' => $tabName])]);

        if ($this->getRequest()->getContent() !== '') {
            $data = [];
            $data['status'] = 'success';
            try {
                $data = $settingProvider->handleSettingsForm($form, $this->getRequest(), $data);
                if ($data['status'] === 'success') {
                    $form = $this->createForm(ResourceSettingsType::class, null, ['action' => $this->generateUrl('resource_settings', ['tabName' => $tabName])]);
                }
            } catch (\Exception $e) {
                $data = ErrorMessageHelper::getDatabaseErrorMessage($data, true);
            }

            $manager->singlePanel($form->createView());
            $data['form'] = $manager->getFormFromContainer();

            return new JsonResponse($data);
        }

        $container = new Container();
        $panel = new Panel('Category', 'School');
        $container->setTranslationDomain('School')
            ->addForm('Features', $form->createView())
            ->addPanel($panel)
            ->setSelectedPanel($tabName)
        ;
        $panel = new Panel('Purpose');
        $container->addPanel($panel);

        $manager->addContainer($container);

        return $this->getPageManager()
            ->createBreadcrumbs('Resource Settings', [])
            ->render(['containers' => $manager->getBuiltContainers()]);
    }

}
