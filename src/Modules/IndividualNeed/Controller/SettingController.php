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
     *
     * 16/08/2020 10:31
     * @Route("/individual/need/settings/",name="individual_need_settings")
     * @Route("/individual/need/settings/",name="individual_need_config")
     * @IsGranted("ROLE_ROUTE")
     * @return JsonResponse
     */
    public function settings()
    {
        $manager = SettingFactory::getSettingManager();
        $manager->getSettingsByScope('Individual Needs');

        $form = $this->createForm(INTemplatesType::class, null, ['action' => $this->generateUrl('individual_need_settings')]);

        if ($this->getRequest()->getContent() !== '') {
            if ($manager->handleSettingsForm($form, $this->getRequest())) {
                $form = $this->createForm(INTemplatesType::class, null, ['action' => $this->generateUrl('individual_need_settings')]);
            }
            $this->getContainerManager()->singlePanel($form->createView());
            $data = $this->getMessageStatusManager()->toArray($this->getContainerManager()->getFormFromContainer());

            return new JsonResponse($data);
        }

        $this->getContainerManager()->singlePanel($form->createView());
        return $this->getPageManager()
            ->createBreadcrumbs( 'Individual Need Settings',[])
            ->render(['containers' => $this->getContainerManager()->getBuiltContainers()]);
    }
}