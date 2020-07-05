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
 * Date: 2/05/2020
 * Time: 12:21
 */
namespace App\Modules\People\Controller;

use App\Container\Container;
use App\Container\ContainerManager;
use App\Container\Panel;
use App\Container\Section;
use App\Controller\AbstractPageController;
use App\Modules\People\Form\PeopleSettingsType;
use App\Modules\People\Form\UpdaterSettingsType;
use App\Modules\People\Manager\RequiredUpdates;
use App\Modules\System\Manager\SettingFactory;
use App\Provider\ProviderFactory;
use App\Util\ErrorMessageHelper;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class SettingController
 * @package App\Modules\People\Controller
 */
class SettingController extends AbstractPageController
{
    /**
     * peopleSettings
     * @Route("/people/settings/{tabName}",name="people_settings")
     * @IsGranted("ROLE_ROUTE")
     * @param ContainerManager $manager
     * @param string|null $tabName
     * @return JsonResponse
     */
    public function peopleSettings(ContainerManager $manager, ?string $tabName = 'Field Values')
    {
        $provider = SettingFactory::getSettingManager();
        $request = $this->getRequest();

        // System Settings
        $form = $this->createForm(PeopleSettingsType::class, null, ['action' => $this->generateUrl('people_settings', ['tabName' => $tabName])]);

        if ($request->getContent() !== '') {

            $data = [];
            try {
                $data['errors'] = $provider->handleSettingsForm($form, $request);
                if ('success' === $provider->getStatus())
                    $form = $this->createForm(PeopleSettingsType::class, null, ['action' => $this->generateUrl('people_settings', ['tabName' => $tabName])]);
            } catch (\Exception $e) {
                $data = ErrorMessageHelper::getDatabaseErrorMessage([],true);
            }

            $manager->singlePanel($form->createView());
            $data['form'] = $manager->getFormFromContainer();

            return new JsonResponse($data, 200);
        }

        $section = new Section('form', 'single');
        $container = new Container($tabName);
        $panel = new Panel('Field Values', 'People', $section);
        $container->addPanel($panel);
        $panel = new Panel('Privacy / Data Options', 'People', $section);
        $container->addPanel($panel);
        $panel = new Panel('Day Type Options', 'People', $section);
        $container->addPanel($panel)->addForm('single', $form->createView());

        $manager->addContainer($container);

        return $this->getPageManager()->createBreadcrumbs('People Settings')
            ->render(['containers' => $manager->getBuiltContainers()]);
    }

    /**
     * updaterSettings
     * @Route("/updater/settings/", name="updater_settings")
     * @IsGranted("ROLE_ROUTE")
     * @param ContainerManager $manager
     * @return JsonResponse|Response
     */
    public function updaterSettings(ContainerManager $manager)
    {
        $form = $this->createForm(UpdaterSettingsType::class, null, ['action' => $this->generateUrl('updater_settings')]);
        $request = $this->getRequest();
        $required = new RequiredUpdates();

        if ($request->getContent() !== '') {
            $settingProvider = SettingFactory::getSettingManager();
            $data = [];
            try {
                $data['errors'] = $settingProvider->handleSettingsForm($form, $request);
                if ('success' === $settingProvider->getStatus()) {
                    $form = $this->createForm(UpdaterSettingsType::class, null, ['action' => $this->generateUrl('updater_settings')]);
                }
            } catch (\Exception $e) {
                $data = ErrorMessageHelper::getDatabaseErrorMessage();
            }
            $manager->singlePanel($form->createView());
            $data['form'] = $manager->getFormFromContainer();
            return new JsonResponse($data);
        }

        $container = new Container();
        $panel = new Panel('Settings', 'People', new Section('form', 'Settings'));
        $container->addPanel($panel);
        $container->addForm('Settings', $form->createView());
        $panel = new Panel('Required Data', 'People', new Section('special', $required->toArray()));
        $container->addPanel($panel);
        $manager->addContainer($container);

        return $this->getPageManager()
            ->createBreadcrumbs('Data Updater Settings')
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