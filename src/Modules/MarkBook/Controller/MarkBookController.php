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
use App\Modules\MarkBook\Form\MarkBookSettingType;
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
     * @param ContainerManager $manager
     * @param string $tabName
     * @return JsonResponse
     * @Route("/mark/book/view",name="mark_book_view")
     * @Route("/mark/book/settings/{tabName}", name="mark_book_settings")
     * @Route("/mark/book/settings/{tabName}", name="mark_book_configure")
     * @IsGranted("ROLE_ROUTE")
     * 2/06/2020 13:46
     */
    public function settings(ContainerManager $manager, string $tabName = 'Features')
    {
        $request = $this->getRequest();

        $settingProvider = SettingFactory::getSettingManager();
        $settingProvider->getSettingsByScope('Mark Book');
        $container = new Container($tabName);

        $form = $this->createForm(MarkBookSettingType::class, null, ['action' => $this->generateUrl('mark_book_settings')]);

        if ($request->getContent() !== '') {
            $data = [];
            $data['status'] = 'success';
            try {
                $data['errors'] = $settingProvider->handleSettingsForm($form,$request);
                $form = $this->createForm(MarkbookSettingType::class, null, ['action' => $this->generateUrl('mark_book_settings')]);
            } catch (\Exception $e) {
                $data = ErrorMessageHelper::getDatabaseErrorMessage([], true);
            }

            $manager->singlePanel($form->createView());
            $data['form'] = $manager->getFormFromContainer();

            return new JsonResponse($data, 200);
        }

        $panel = new Panel('Features','MarkBook', new Section('form', 'single'));
        $container->setTranslationDomain('MarkBook')
            ->addForm('single', $form->createView())
            ->addPanel($panel);
        $panel = new Panel('Interface','MarkBook', new Section('form', 'single'));
        $container->addPanel($panel);
        $panel = new Panel('Warnings','MarkBook', new Section('form', 'single'));
        $container->addPanel($panel);

        // Finally Finished
        $manager->addContainer($container);

        return $this->getPageManager()->createBreadcrumbs('Mark Book Settings')
            ->render(['containers' => $manager->getBuiltContainers()]);
    }

}