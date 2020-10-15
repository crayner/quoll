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
 * Date: 4/08/2020
 * Time: 12:08
 */
namespace App\Modules\Timetable\Controller;

use App\Container\Container;
use App\Container\Panel;
use App\Container\Section;
use App\Controller\AbstractPageController;
use App\Modules\School\Util\AcademicYearHelper;
use App\Modules\Timetable\Entity\TimetableDay;
use App\Modules\Timetable\Entity\TimetablePeriod;
use App\Modules\Timetable\Entity\TimetablePeriodClass;
use App\Modules\Timetable\Form\PeriodClassType;
use App\Modules\Timetable\Form\TimetablePeriodType;
use App\Modules\Timetable\Pagination\PeriodClassesPagination;
use App\Provider\ProviderFactory;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class TimetableColumnPeriodController
 * @package App\Modules\Timetable\Controller
 * @author Craig Rayner <craig@craigrayner.com>
 */
class TimetablePeriodController extends AbstractPageController
{
    /**
     * edit
     *
     * 13/10/2020 14:51
     * @param TimetableDay $timetableDay
     * @param PeriodClassesPagination $pagination
     * @param TimetablePeriod|null $timetablePeriod
     * @param string $tabName
     * @Route("/timetable/day/{timetableDay}/period/{timetablePeriod}/edit/{tabName}",name="timetable_day_period_edit")
     * @Route("/timetable/day/{timetableDay}/period/add/",name="timetable_day_period_add")
     * @IsGranted("ROLE_ROUTE")
     * @return JsonResponse
     */
    public function edit(TimetableDay $timetableDay, PeriodClassesPagination $pagination, ?TimetablePeriod $timetablePeriod = null, string $tabName = 'Details')
    {
        if (null === $timetablePeriod || $this->getRequest()->get('_route') === 'timetable_day_period_add') {
            $action = $this->generateUrl('timetable_day_period_add', ['timetableDay' => $timetableDay->getId()]);
            $timetablePeriod = new TimetablePeriod($timetableDay);
        } else {
            $action = $this->generateUrl('timetable_day_period_edit', ['timetableDay' => $timetableDay->getId(), 'timetablePeriod' => $timetablePeriod->getId(), 'tabName' => $tabName]);
        }

        $form = $this->createForm(TimetablePeriodType::class, $timetablePeriod, ['action' => $action]);

        if ($this->isPostContent()) {
            $this->submitForm($form);
            if ($form->isValid()) {
                $id = $timetablePeriod->getId();
                ProviderFactory::create(TimetablePeriod::class)->persistFlush($timetablePeriod);
                if ($timetablePeriod->getId() !== $id) {
                    $this->getStatusManager()
                        ->setReDirect($this->generateUrl('timetable_day_period_edit', ['timetableDay' => $timetableDay->getId(), 'timetablePeriod' => $timetablePeriod->getId(), 'tabName' => 'Details']), true);
                }
            }
            return $this->singleForm($form);
        }

        $container = new Container($tabName);
        $panel = new Panel('Details', 'Timetable', new Section('form', 'Details'));
        $container->addForm('Details', $form)
            ->addPanel(AcademicYearHelper::academicYearWarning($panel));
        $this->getContainerManager()
            ->setReturnRoute($this->generateUrl('timetable_day_edit', ['timetable' => $timetableDay->getTimetable()->getId(), 'timetableDay' => $timetableDay->getId(), 'tabName' => 'Periods']));

        if ($timetableDay->getId() !== null) $this->getContainerManager()->setAddElementRoute($this->generateUrl('timetable_day_period_add', ['timetableDay' => $timetableDay->getId()]));

        $timetable = $timetableDay->getTimetable();

        if ($timetablePeriod->getId()) {
            $panel = new Panel('Classes', 'Timetable', new Section('html', $this->renderView('timetable/period_details.html.twig', ['period' => $timetablePeriod])));
            $timetableDay = $timetablePeriod->getTimetableDay();
            $timetable = $timetableDay->getTimetable();
            if (in_array($timetablePeriod->getType(), ['Lesson','Sport'])) {
                $pagination->setPeriod($timetablePeriod)
                    ->setContent(ProviderFactory::getRepository(TimetablePeriodClass::class)->getPeriodClassesPagination($timetablePeriod))
                    ->setAddElementRoute($this->generateUrl('timetable_day_period_class_add', ['period' => $timetablePeriod->getId()]), 'Add Class');
                $panel->addSection(new Section('pagination', $pagination));
            }
            $container->addPanel(AcademicYearHelper::academicYearWarning($panel));
        }

        return $this->getPageManager()
            ->createBreadcrumbs($timetablePeriod->getId() === null ? ['Add Timetable Period in {day}', ['{day}' => $timetableDay->getName()]] : ['Edit Timetable Period in {day} - {name}', ['{name}' => $timetablePeriod->getName(),'{day}' => $timetableDay->getName()]],
                [
                    [
                        'uri' => 'timetable_edit',
                        'name' => 'Edit Timetable {name}',
                        'trans_params' => ['{name}' => $timetable->getName()],
                        'uri_params' => ['timetable' => $timetable->getId(), 'tabName' => 'Timetable Days']
                    ],
                    [
                        'name' => 'Edit Timetable Day ({name})',
                        'trans_params' => ['{name}' => $timetableDay->getName()],
                        'uri' => 'timetable_day_edit',
                        'uri_params' => ['timetable' => $timetable->getId(), 'timetableDay' => $timetableDay->getId(), 'tabName' => 'Periods']
                    ]
                ]
            )
            ->render(
                [
                    'containers' => $this->getContainerManager()
                        ->addContainer($container)
                        ->getBuiltContainers()
                ]
            )
        ;
    }

    /**
     * editPeriodClass
     *
     * 15/10/2020 10:38
     * @param TimetablePeriod $period
     * @param TimetablePeriodClass|null $periodClass
     * @Route("/timetable/period/{period}/class/{periodClass}/edit/",name="timetable_day_period_class_edit")
     * @Route("/timetable/period/{period}/class/add/",name="timetable_day_period_class_add")
     * @IsGranted("ROLE_ROUTE")
     * @return JsonResponse
     */
    public function editPeriodClass(TimetablePeriod $period, ?TimetablePeriodClass $periodClass = null)
    {
        $timetableDay = $period->getTimetableDay();
        $timetable = $timetableDay->getTimetable();

        if ($this->getRequest()->get('_route') === 'timetable_day_period_class_add' || $periodClass === null) {
            $periodClass = new TimetablePeriodClass($period);
        }

        $action = $periodClass->getId() ? $this->generateUrl('timetable_day_period_class_edit', ['period' => $period->getId(), 'periodClass' => $periodClass->getId()]) : $this->generateUrl('timetable_day_period_class_add', ['period' => $period->getId()]);

        $form = $this->createForm(PeriodClassType::class, $periodClass, ['action' => $action]);

        if ($this->isPostContent()) {
            $this->submitForm($form);
            if ($form->isValid()) {
                $id = $periodClass->getId();
                ProviderFactory::create(TimetablePeriodClass::class)->persistFlush($periodClass);
                if ($id !== $periodClass->getId()) {
                    $this->getStatusManager()
                        ->setReDirect($this->generateUrl('timetable_day_period_class_edit', ['period' => $period->getId(), 'periodClass' => $periodClass->getId()]));
                }
            }
            return $this->singleForm($form);
        }

        $container = new Container();
        $panel = new Panel('null', 'Timetable', new Section('form', 'single'));
        $container->addForm('single', $form)
            ->addPanel(AcademicYearHelper::academicYearWarning($panel));

        if ($periodClass->getId()) $this->getContainerManager()
            ->setAddElementRoute($this->generateUrl('timetable_day_period_class_add', ['period' => $period->getId()]));

        return $this->getPageManager()
            ->createBreadcrumbs($periodClass->getId() ? 'Edit Class in Period' : 'Add Class in Period',
                [
                    [
                        'uri' => 'timetable_edit',
                        'name' => 'Edit Timetable {name}',
                        'trans_params' => ['{name}' => $timetable->getName()],
                        'uri_params' => ['timetable' => $timetable->getId(), 'tabName' => 'Timetable Days']
                    ],
                    [
                        'name' => 'Edit Timetable Day ({name})',
                        'trans_params' => ['{name}' => $timetableDay->getName()],
                        'uri' => 'timetable_day_edit',
                        'uri_params' => ['timetable' => $timetable->getId(), 'timetableDay' => $timetableDay->getId(), 'tabName' => 'Periods']
                    ],
                    [
                        'name' => 'Manage Classes in Period',
                        'uri' => 'timetable_day_period_edit',
                        'uri_params' => ['timetablePeriod' => $period->getId(), 'timetableDay' => $period->getTimetableDay()->getId(), 'tabName' => 'Periods']
                    ]
                ]
            )
            ->render(
                [
                    'containers' => $this->getContainerManager()
                        ->addContainer($container)
                        ->setReturnRoute($this->generateUrl('timetable_day_period_edit', ['tabName' => 'Classes', 'timetablePeriod' => $period->getId(), 'timetableDay' => $period->getTimetableDay()->getId()]))
                        ->getBuiltContainers()
                ]
            )
            ;
    }
}
