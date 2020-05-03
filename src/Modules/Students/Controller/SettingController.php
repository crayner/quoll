<?php
/**
 * Created by PhpStorm.
 *
 * quoll
 * (c) 2020 Craig Rayner <craig@craigrayner.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * User: craig
 * Date: 3/05/2020
 * Time: 14:06
 */

namespace App\Modules\Students\Controller;


use App\Container\Container;
use App\Container\ContainerManager;
use App\Container\Panel;
use App\Controller\AbstractPageController;
use App\Modules\Students\Entity\StudentNoteCategory;
use App\Modules\Students\Form\StudentSettingsType;
use App\Modules\Students\Pagination\StudentNoteCategoryPagination;
use App\Modules\System\Entity\Setting;
use App\Provider\ProviderFactory;
use App\Util\ErrorMessageHelper;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class SettingController extends AbstractPageController
{

    /**
     * Student Settings
     * @Route("/student/settings/{tabName}",name="student_settings")
     * @IsGranted("ROLE_ROUTE")
     * @param ContainerManager $manager
     * @param StudentNoteCategoryPagination $pagination
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function studentSettings(ContainerManager $manager, StudentNoteCategoryPagination $pagination, string $tabName = 'Categories')
    {
        $settingProvider = ProviderFactory::create(Setting::class);
        $request = $this->getRequest();
        $container = new Container();

        $panel = new Panel('Categories');
        $content = ProviderFactory::getRepository(StudentNoteCategory::class)->findBy([], ['name' => 'ASC']);
        $pagination->setContent($content)
            ->setAddElementRoute($this->generateUrl('student_note_category_add'));
        $panel->setPagination($pagination);
        $container->addPanel($panel);

        // System Settings
        $form = $this->createForm(StudentSettingsType::class, null, ['action' => $this->generateUrl('student_settings')]);

        if ($request->getContent() !== '') {
            $data = [];
            try {
                $data['errors'] = $settingProvider->handleSettingsForm($form, $request);
                if ('success' === $settingProvider->getStatus())
                    $form = $this->createForm(StudentSettingsType::class, null, ['action' => $this->generateUrl('student_settings')]);
            } catch (\Exception $e) {
                $data = ErrorMessageHelper::getDatabaseErrorMessage([], true);
            }

            $manager->singlePanel($form->createView());
            $data['form'] = $manager->getFormFromContainer();

            return new JsonResponse($data, 200);
        }
        $panel = new Panel('Notes');
        $container->addPanel($panel)->addForm('single', $form->createView());

        $panel = new Panel('Alerts');
        $container->addPanel($panel);

        $panel = new Panel('Miscellaneous');
        $container->addPanel($panel)->setSelectedPanel($tabName);

        $manager->addContainer($container);
        $manager->buildContainers();

        return $this->getPageManager()->createBreadcrumbs('Student Settings')
            ->render(['containers' => $manager->getBuiltContainers()]);
    }

}