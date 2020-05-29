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
 * Date: 28/05/2020
 * Time: 14:50
 */
namespace App\Modules\Staff\Controller;

use App\Container\Container;
use App\Container\ContainerManager;
use App\Container\Panel;
use App\Controller\AbstractPageController;
use App\Modules\Staff\Entity\StaffAbsenceType;
use App\Modules\Staff\Form\StaffSettingsType;
use App\Modules\Staff\Pagination\StaffAbsenceTypePagination;
use App\Modules\System\Entity\Setting;
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
     * @IsGranted("ROLE_ROUTE")
     * @param ContainerManager $manager
     * @param StaffAbsenceTypePagination $pagination
     * @return Response
     */
    public function staffSettings(ContainerManager $manager, StaffAbsenceTypePagination $pagination, string $tabName = 'List')
    {
        // System Settings
        $form = $this->createForm(StaffSettingsType::class, null, ['action' => $this->generateUrl('staff_settings')]);
        $settingProvider = ProviderFactory::create(Setting::class);

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

        $container = new Container();
        $panel = new Panel('List');
        $content =  ProviderFactory::getRepository(StaffAbsenceType::class)->findBy([], ['sequenceNumber' => 'ASC']);
        $pagination->setContent($content)->setDraggableRoute('staff_absence_type_sort')
            ->setPreContent('<h3>'.TranslationHelper::translate('Staff Absence Types').'</h3>')
            ->setAddElementRoute($this->generateUrl('staff_absence_type_add'))
        ;
        $panel->setPagination($pagination);
        $container->addPanel($panel);
        $panel = new Panel('Settings');
        $container->addForm('Settings', $form->createView())->addPanel($panel);
        $manager->addContainer($container);

        return $this->getPageManager()->createBreadcrumbs('Staff Settings')
            ->render([
                'containers' => $manager->getBuiltContainers(),
            ]);
    }
}