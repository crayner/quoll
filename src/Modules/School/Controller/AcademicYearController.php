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
 * Date: 30/05/2020
 * Time: 08:08
 */
namespace App\Modules\School\Controller;

use App\Container\ContainerManager;
use App\Controller\AbstractPageController;
use App\Modules\School\Entity\AcademicYear;
use App\Modules\School\Form\AcademicYearType;
use App\Modules\School\Manager\Hidden\CalendarDisplayManager;
use App\Modules\School\Pagination\AcademicYearPagination;
use App\Modules\System\Manager\SettingFactory;
use App\Provider\ProviderFactory;
use App\Util\ErrorMessageHelper;
use App\Util\TranslationHelper;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class AcademicYearController
 * @package App\Modules\School\Controller
 * @author Craig Rayner <craig@craigrayner.com>
 */
class AcademicYearController extends AbstractPageController
{
    /**
     * list
     * @Route("/academic/year/list/",name="academic_year_list")
     * @Route("/academic/year/list/",name="academic_year_delete")
     * @IsGranted("ROLE_ROUTE")
     * @param AcademicYearPagination $pagination
     * @param array $data
     * @return mixed
     */
    public function list(AcademicYearPagination $pagination, array $data = [])
    {
        $content = ProviderFactory::getRepository(AcademicYear::class)->findBy([], ['firstDay' => 'ASC']);
        $pagination->setContent($content)
            ->setAddElementRoute($this->generateUrl('academic_year_add'));

        return $this->getPageManager()->setMessages(isset($data['errors']) ? $data['errors'] : [])->createBreadcrumbs('Academic Years', [])
            ->render(
                [
                    'pagination' => $pagination->toArray(),
                    'url' => $this->generateUrl('academic_year_list'),
                    'title' => TranslationHelper::translate('Academic Years'),
                ]
            );
    }

    /**
     * display
     * @param AcademicYear $year
     * @return Response
     * @Route("/academic/year/{year}/display/", name="academic_year_display_popup_raw")
     * @IsGranted("ROLE_USER")
     */
    public function display(AcademicYear $year)
    {
        $calendar = new CalendarDisplayManager($this->getRequest()->getLocale());
        $calendar->createYear($year);
        $this->getPageManager()->addPageStyle('css/core');
        return $this->render('school/calendar.html.twig', [
            'calendar' => $calendar,
            'organisationName' => SettingFactory::getSettingManager()->getSettingByScopeAsString('System', 'organisationName', 'Kookaburra'),
            'page' => $this->getPageManager(),
        ]);
    }

    /**
     * edit
     * @Route("/academic/year/{year}/edit/", name="academic_year_edit")
     * @Route("/academic/year/add/", name="academic_year_add")
     * @IsGranted("ROLE_ROUTE")
     * @param ContainerManager $manager
     * @param AcademicYear|null $year
     * @return JsonResponse|Response
     */
    public function edit(ContainerManager $manager,?AcademicYear $year = null)
    {
        $request = $this->getRequest();

        if (!$year instanceof AcademicYear) {
            $year = new AcademicYear();
            $action = $this->generateUrl('academic_year_add');
        } else {
            $action = $this->generateUrl('academic_year_edit', ['year' => $year->getId()]);
        }

        $form = $this->createForm(AcademicYearType::class, $year, ['action' => $action]);

        if ($request->getContent() !== '') {
            $content = json_decode($request->getContent(), true);
            $form->submit($content);
            if ($form->isValid()) {
                $id = $year->getId();
                $provider = ProviderFactory::create(AcademicYear::class);
                $data = $provider->persistFlush($year, []);
                if ($id !== $year->getId() && $data['status'] === 'success') {
                    $data['redirect'] = $this->generateUrl('academic_year_edit', ['year' => $year->getId()]);
                    $data['status'] = 'redirect';
                } else if ($data['status'] !== 'success') {
                    $data = ErrorMessageHelper::getDatabaseErrorMessage([],true);
                }
            } else {
                $data = ErrorMessageHelper::getInvalidInputsMessage([], true);
            }

            $manager->singlePanel($form->createView());
            $data['form'] = $manager->getFormFromContainer();
            return new JsonResponse($data, 200);
        }

        $manager->setReturnRoute($this->generateUrl('academic_year_list'));
        if ($year->getId() !== null) {
            $manager->setAddElementRoute($this->generateUrl('academic_year_add'));
        }
        $manager->singlePanel($form->createView());
        return $this->getPageManager()->createBreadcrumbs($year->getId() > 0 ? 'Edit Academic Year' : 'Add Academic Year')
            ->render(['containers' => $manager->getBuiltContainers()]);
    }

    /**
     * delete
     * @param AcademicYear $year
     * @param AcademicYearPagination $pagination
     * @return mixed
     * 31/05/2020 11:52
     * @Route("/academic/year/{year}/delete/", name="academic_year_delete")
     * @IsGranted("ROLE_ROUTE")

     */
    public function delete(AcademicYear $year, AcademicYearPagination $pagination)
    {
        ProviderFactory::create(AcademicYear::class)->delete($year);

        $data = ProviderFactory::create(AcademicYear::class)->getMessageManager()->pushToJsonData();

        return $this->list($pagination, $data);
    }

}