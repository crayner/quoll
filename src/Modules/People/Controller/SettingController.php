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
 * Date: 2/05/2020
 * Time: 12:21
 */
namespace App\Modules\People\Controller;

use App\Container\Container;
use App\Container\ContainerManager;
use App\Container\Panel;
use App\Controller\AbstractPageController;
use App\Modules\People\Form\PeopleSettingsType;
use App\Modules\People\Form\UpdaterSettingsType;
use App\Modules\People\Manager\RequiredUpdates;
use App\Modules\System\Entity\Setting;
use App\Provider\ProviderFactory;
use App\Util\ErrorMessageHelper;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class SettingController
 * @package App\Modules\People\Controller
 */
class SettingController extends AbstractPageController
{
    /**
     * peopleSettings
     * @Route("/people/settings/",name="people_settings")
     * @IsGranted("ROLE_ROUTE")
     * @param ContainerManager $manager
     * @return JsonResponse
     */
    public function peopleSettings(ContainerManager $manager)
    {
        $provider = ProviderFactory::create(Setting::class);
        $request = $this->getRequest();

        // System Settings
        $form = $this->createForm(PeopleSettingsType::class, null, ['action' => $this->generateUrl('people_settings')]);

        if ($request->getContent() !== '') {

            $data = [];
            try {
                $data['errors'] = $provider->handleSettingsForm($form, $request);
                if ('success' === $provider->getStatus())
                    $form = $this->createForm(PeopleSettingsType::class, null, ['action' => $this->generateUrl('people_settings')]);
            } catch (\Exception $e) {
                $data = ErrorMessageHelper::getDatabaseErrorMessage([],true);
            }

            $manager->singlePanel($form->createView());
            $data['form'] = $manager->getFormFromContainer('formContent', 'single');

            return new JsonResponse($data, 200);
        }

        $manager->singlePanel($form->createView());
        $manager->buildContainers();

        return $this->getPageManager()->createBreadcrumbs('People Settings')
            ->render(['containers' => $manager->getBuiltContainers()]);
    }

    /**
     * updaterSettings
     * @Route("/updater/settings/", name="updater_settings")
     * @IsGranted("ROLE_ROUTE")
     * @param ContainerManager $manager
     * @return JsonResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function updaterSettings(ContainerManager $manager)
    {
        $form = $this->createForm(UpdaterSettingsType::class, null, ['action' => $this->generateUrl('updater_settings')]);
        $request = $this->getRequest();
        $required = new RequiredUpdates();

        if ($request->getContent() !== '') {
            $settingProvider = ProviderFactory::create(Setting::class);
            $data = [];
            try {
                $data['errors'] = $settingProvider->handleSettingsForm($form, $request);
                if ('success' === $settingProvider->getStatus())
                    $form = $this->createForm(UpdaterSettingsType::class, null, ['action' => $this->generateUrl('updater_settings')]);
            } catch (\Exception $e) {
                $data = ErrorMessageHelper::getDatabaseErrorMessage();
            }
            $manager->singlePanel($form->createView());
            $data['form'] = $manager->getFormFromContainer();
            return new JsonResponse($data);
        }

        $container = new Container();
        $panel = new Panel('Settings');
        $container->addPanel($panel);
        $container->addForm('Settings', $form->createView());
        $panel = new Panel('Required Data');
        $panel->setSpecial($required);
        $container->addPanel($panel);
        $manager->addContainer($container);

        return $this->getPageManager()->createBreadcrumbs('Data Updater Settings')
            ->render(['containers' => $manager->getBuiltContainers()]);
    }

    /**
     * storeRequiredDataUpdates
     * @Route("/updater/store/required/")
     */
    public function storeRequiredDataUpdates()
    {
        $content = json_decode($this->getRequest()->getContent(), true);
        $required = new RequiredUpdates();
        $data = $required->handleRequest($content['requiredDataUpdates']);
        $data = ErrorMessageHelper::getSuccessMessage($data, true);
        $data['settings'] = $required->toArray();
        return new JsonResponse($data);

    }
}