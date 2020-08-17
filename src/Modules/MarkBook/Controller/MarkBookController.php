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
 * Date: 2/06/2020
 * Time: 13:44
 */
namespace App\Modules\MarkBook\Controller;

use App\Container\Container;
use App\Container\ContainerManager;
use App\Container\Panel;
use App\Container\Section;
use App\Controller\AbstractPageController;
use App\Manager\StatusManager;
use App\Modules\MarkBook\Form\MarkBookFeaturesSettingType;
use App\Modules\MarkBook\Form\MarkBookInterfaceSettingType;
use App\Modules\MarkBook\Form\MarkBookWarningsSettingType;
use App\Modules\System\Manager\SettingFactory;
use App\Provider\ProviderFactory;
use App\Util\ErrorMessageHelper;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class MarkBookController
 * @package App\Modules\MarkBook
 * @author Craig Rayner <craig@craigrayner.com>
 */
class MarkBookController extends AbstractPageController
{
    /**
     * settings
     *
     * 18/08/2020 08:52
     * @param string $tabName
     * @Route("/mark/book/view/{tabName}",name="mark_book_view",methods={"GET"})
     * @Route("/mark/book/settings/{tabName}", name="mark_book_settings",methods={"GET"})
     * @Route("/mark/book/settings/{tabName}", name="mark_book_configure",methods={"GET"})
     * @IsGranted("ROLE_ROUTE")
     * @return JsonResponse
     */
    public function settings(string $tabName = 'Features')
    {
        $container = new Container($tabName);
        $panel = new Panel('Features','MarkBook', new Section('form', 'Features'));
        $form = $this->createForm(MarkBookFeaturesSettingType::class, null, ['action' => $this->generateUrl('mark_book_features')]);
        $container->setTranslationDomain('MarkBook')
            ->addForm('Features', $form->createView())
            ->addPanel($panel);
        $panel = new Panel('Interface','MarkBook', new Section('form', 'Interface'));
        $form = $this->createForm(MarkBookInterfaceSettingType::class, null, ['action' => $this->generateUrl('mark_book_interface')]);
        $container->addPanel($panel)
            ->addForm('Interface', $form->createView());
        $panel = new Panel('Warnings','MarkBook', new Section('form', 'Warnings'));
        $form = $this->createForm(MarkBookWarningsSettingType::class, null, ['action' => $this->generateUrl('mark_book_warnings')]);
        $container->addPanel($panel)
            ->addForm('Warnings', $form->createView());

        // Finally Finished
        $this->getContainerManager()->addContainer($container);
        return $this->getPageManager()->createBreadcrumbs('Mark Book Settings')
            ->render(['containers' => $this->getContainerManager()->getBuiltContainers()]);
    }

    /**
     * saveFeatures
     *
     * 18/08/2020 08:37
     * @Route("/mark/book/features/settings/", name="mark_book_features",methods={"POST"})
     * @IsGranted("ROLE_ROUTE")
     * @return JsonResponse
     */
    public function saveFeatures()
    {
        $manager = SettingFactory::getSettingManager();
        $manager->getSettingsByScope('Mark Book');

        $form = $this->createForm(MarkBookFeaturesSettingType::class, null, ['action' => $this->generateUrl('mark_book_features')]);

        try {
            $manager->handleSettingsForm($form,$this->getRequest());
            if ($this->getStatusManager()->isStatusSuccess()) {
                $form = $this->createForm(MarkBookFeaturesSettingType::class, null, ['action' => $this->generateUrl('mark_book_features')]);
            }
        } catch (\Exception $e) {
            $this->getStatusManager()->error(StatusManager::INVALID_INPUTS);
        }

        $this->getContainerManager()->singlePanel($form->createView());
        return $this->generateJsonResponse(['form' => $this->getContainerManager()->getFormFromContainer()]);
    }

    /**
     * saveInterface
     *
     * 18/08/2020 08:37
     * @Route("/mark/book/interface/settings/", name="mark_book_interface",methods={"POST"})
     * @IsGranted("ROLE_ROUTE")
     * @return JsonResponse
     */
    public function saveInterface()
    {
        $manager = SettingFactory::getSettingManager();
        $manager->getSettingsByScope('Mark Book');

        $form = $this->createForm(MarkBookInterfaceSettingType::class, null, ['action' => $this->generateUrl('mark_book_interface')]);

        try {
            $manager->handleSettingsForm($form,$this->getRequest());
            if ($this->getStatusManager()->isStatusSuccess()) {
                $form = $this->createForm(MarkBookInterfaceSettingType::class, null, ['action' => $this->generateUrl('mark_book_interface')]);
            }
        } catch (\Exception $e) {
            $this->getStatusManager()->error(StatusManager::INVALID_INPUTS);
        }

        $this->getContainerManager()->singlePanel($form->createView());
        return $this->generateJsonResponse(['form' => $this->getContainerManager()->getFormFromContainer()]);
    }

    /**
     * saveInterface
     *
     * 18/08/2020 08:37
     * @Route("/mark/book/warnings/settings/", name="mark_book_warnings",methods={"POST"})
     * @IsGranted("ROLE_ROUTE")
     * @return JsonResponse
     */
    public function saveWarnings()
    {
        $manager = SettingFactory::getSettingManager();
        $manager->getSettingsByScope('Mark Book');

        $form = $this->createForm(MarkBookWarningsSettingType::class, null, ['action' => $this->generateUrl('mark_book_warnings')]);

        try {
            $manager->handleSettingsForm($form,$this->getRequest());
            if ($this->getStatusManager()->isStatusSuccess()) {
                $form = $this->createForm(MarkBookWarningsSettingType::class, null, ['action' => $this->generateUrl('mark_book_warnings')]);
            }
        } catch (\Exception $e) {
            $this->getStatusManager()->error(StatusManager::INVALID_INPUTS);
        }

        $this->getContainerManager()->singlePanel($form->createView());
        return $this->generateJsonResponse(['form' => $this->getContainerManager()->getFormFromContainer()]);
    }
}