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
 * Date: 9/06/2020
 * Time: 11:27
 */
namespace App\Modules\IndividualNeed\Controller;

use App\Container\ContainerManager;
use App\Controller\AbstractPageController;
use App\Modules\IndividualNeed\Form\INTemplatesType;
use App\Modules\System\Manager\SettingFactory;
use App\Provider\ProviderFactory;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class SettingController
 * @package App\Modules\IndividualNeed\Controller
 * @author Craig Rayner <craig@craigrayner.com>
 */
class SettingController extends AbstractPageController
{
    /**
     * settings
     * @param ContainerManager $manager
     * @Route("/individual/need/settings/",name="individual_need_settings")
     * @Route("/individual/need/settings/",name="individual_need_config")
     * @IsGranted("ROLE_ROUTE")
     * 9/06/2020 11:29
     */
    public function settings(ContainerManager $manager)
    {
        SettingFactory::getSettingManager()->getSettingsByScope('Individual Needs');

        $form = $this->createForm(INTemplatesType::class, null, ['action' => $this->generateUrl('individual_need_settings')]);

        if ($this->getRequest()->getContent() !== '') {
            SettingFactory::getSettingManager()->handleSettingsForm($form, $this->getRequest());
            $data = SettingFactory::getSettingManager()->getMessageManager()->pushToJsonData();
            $manager->singlePanel($form->createView());
            $data['form'] = $manager->getFormFromContainer();
            return new JsonResponse($data);
        }

        $manager->singlePanel($form->createView());
        return $this->getPageManager()
            ->createBreadcrumbs( 'Individual Need Settings',[])
            ->render(['containers' => $manager->getBuiltContainers()]);
    }
}