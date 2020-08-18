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
use App\Container\Panel;
use App\Container\Section;
use App\Controller\AbstractPageController;
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
     *
     * 19/08/2020 09:30
     * @param string $tabName
     * @Route("/format/name/settings/{tabName}",name="format_name_settings")
     * @IsGranted("ROLE_ROUTE")
     * @return JsonResponse
     */
    public function settings(string $tabName = 'General')
    {
        $container = new Container($tabName);
        foreach(PersonNameManager::getPersonTypeList() as $type) {
            $form = $this->getForm($type);
            $panel = new Panel($type, 'People', new Section('form', $type));
            $container->addForm($type, $form->createView())
                ->addPanel($panel);
        }

        return $this->getPageManager()
            ->createBreadcrumbs('Format Name Settings')
            ->render(['containers' => $this->getContainerManager()->addContainer($container)->getBuiltContainers()]);
    }

    /**
     * getForm
     *
     * 19/08/2020 09:32
     * @param string $tabName
     * @return FormInterface
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
     *
     * 19/08/2020 09:31
     * @param string $tabName
     * @Route("/format/name/{tabName}/save/",name="format_name_save")
     * @IsGranted("ROLE_ROUTE")
     * @return JsonResponse
     */
    public function saveFormatNameSettings(string $tabName)
    {
        $form = $this->getForm($tabName);

        SettingFactory::getSettingManager()->handleSettingsForm($form, $this->getRequest());
        if ($this->isStatusSuccess()) {
            $form = $this->getForm($tabName);
        }

        return $this->generateJsonResponse(
            [
                'form' => $this->getContainerManager()
                    ->singlePanel($form->createView())
                    ->getFormFromContainer()
            ]
        );
    }
}
