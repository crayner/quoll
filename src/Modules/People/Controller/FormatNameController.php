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
 * Date: 25/07/2020
 * Time: 13:01
 */
namespace App\Modules\People\Controller;

use App\Container\Container;
use App\Container\ContainerManager;
use App\Container\Panel;
use App\Container\Section;
use App\Controller\AbstractPageController;
use App\Modules\People\Form\FormatNameSettingType;
use App\Modules\People\Manager\PersonNameManager;
use App\Modules\System\Manager\SettingFactory;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class FormatNameController
 * @package App\Modules\People\Controller
 * @author Craig Rayner <craig@craigrayner.com>
 */
class FormatNameController extends AbstractPageController
{
    /**
     * settings
     * @param ContainerManager $manager
     * @param string $tabName
     * @return JsonResponse
     * @Route("/format/name/settings/{tabName}",name="format_name_settings")
     * @IsGranted("ROLE_ROUTE")
     * 25/07/2020 13:02
     */
    public function settings(ContainerManager $manager, string $tabName = 'General')
    {
        $container = new Container($tabName);
        foreach(PersonNameManager::getPersonTypeList() as $type) {
            $form = $this->getForm($type);
            $panel = new Panel($type, 'People', new Section('form', $type));
            $container->addForm($type, $form->createView())->addPanel($panel);
        }
        $manager->addContainer($container);

        return $this->getPageManager()
            ->createBreadcrumbs('Format Name Settings')
            ->render(['containers' => $manager->getBuiltContainers()]);
    }

    /**
     * getForm
     * @param string $tabName
     * @return FormInterface
     * 25/07/2020 13:10
     */
    private function getForm(string $tabName)
    {
        $class = "App\Modules\People\Form\FormatNameSetting" . $tabName . 'Type';
        return $this->createForm($class, null,
            [
                'action' => $this->generateUrl('format_name_save', ['tabName' => $tabName]),
                'tabName' => $tabName,
                'person' => $this->getUser()->getPerson(),
            ]
        );
    }

    /**
     * saveFormatNameSettings
     * @param ContainerManager $manager
     * @param string $tabName
     * @return JsonResponse
     * @Route("/format/name/{tabName}/save/",name="format_name_save")
     * @IsGranted("ROLE_ROUTE")
     * 25/07/2020 13:12
     */
    public function saveFormatNameSettings(ContainerManager $manager, string $tabName)
    {
        $form = $this->getForm($tabName);

        $data = SettingFactory::getSettingManager()->handleSettingsForm($form, $this->getRequest(), []);
        if ($data['status'] === 'success') {
            $form = $this->getForm($tabName);
        }

        $manager->singlePanel($form->createView());
        $data['form'] = $manager->getFormFromContainer();

        return new JsonResponse($data);
    }
}
