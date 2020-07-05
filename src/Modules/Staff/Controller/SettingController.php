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
 * Date: 28/05/2020
 * Time: 14:50
 */
namespace App\Modules\Staff\Controller;

use App\Container\Container;
use App\Container\ContainerManager;
use App\Container\Panel;
use App\Container\Section;
use App\Controller\AbstractPageController;
use App\Modules\Staff\Entity\StaffAbsenceType;
use App\Modules\Staff\Form\StaffSettingsType;
use App\Modules\Staff\Pagination\StaffAbsenceTypePagination;
use App\Modules\System\Manager\SettingFactory;
use App\Provider\ProviderFactory;
use App\Util\ErrorMessageHelper;
use App\Util\TranslationHelper;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class SettingController
 * @package App\Modules\Staff\Controller
 * @author Craig Rayner <craig@craigrayner.com>
 */
class SettingController extends AbstractPageController
{
    /**
     * Staff Settings
     * @Route("/staff/settings/{tabName}",name="staff_settings")
     * @Route("/staff/settings/{tabName}",name="staff_settings_people")
     * @IsGranted("ROLE_ROUTE")
     * @param ContainerManager $manager
     * @param StaffAbsenceTypePagination $pagination
     * @return Response
     */
    public function staffSettings(ContainerManager $manager, StaffAbsenceTypePagination $pagination, string $tabName = 'List')
    {
        // System Settings
        $form = $this->createForm(StaffSettingsType::class, null, ['action' => $this->generateUrl('staff_settings')]);
        $settingProvider = SettingFactory::getSettingManager();

        if ($this->getRequest()->getContent() !== '') {
            $data = [];
            try {
                $settingProvider->handleSettingsForm($form, $this->getRequest());
                $data['errors'] = $settingProvider->getErrors();
                if ('success' === $settingProvider->getStatus()) {
                    $form = $this->createForm(StaffSettingsType::class, null, ['action' => $this->generateUrl('staff_settings', ['tabName' => 'Settings'])]);
                }
            } catch (\Exception $e) {
                $data = ErrorMessageHelper::getDatabaseErrorMessage([], true);
            }

            $manager->singlePanel($form->createView());
            $data['form'] = $manager->getFormFromContainer();

            return new JsonResponse($data, 200);

        }

        $container = new Container($tabName);
        $content =  ProviderFactory::getRepository(StaffAbsenceType::class)->findBy([], ['sequenceNumber' => 'ASC']);
        $pagination->setContent($content)->setDraggableRoute('staff_absence_type_sort')
            ->setAddElementRoute($this->generateUrl('staff_absence_type_add'))
        ;
        $section = new Section('html','<h3>'.TranslationHelper::translate('Staff Absence Types').'</h3>');

        $panel = new Panel('List', 'Staff', $section);
        $section = new Section('pagination', $pagination);
        $panel->addSection($section);
        $container->addPanel($panel);
        $section = new Section('form','single');
        $panel = new Panel('Absence Settings', 'Staff', $section);
        $container->addForm('single', $form->createView())
            ->addPanel($panel);
        $panel = new Panel('Field Values', 'Staff', $section);
        $container->addPanel($panel);
        $manager->addContainer($container);

        return $this->getPageManager()->createBreadcrumbs('Staff Settings')
            ->render([
                'containers' => $manager->getBuiltContainers(),
            ]);
    }
}