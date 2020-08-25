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
 * Time: 08:19
 */
namespace App\Modules\Timetable\Controller;

use App\Container\Container;
use App\Container\Panel;
use App\Container\Section;
use App\Controller\AbstractPageController;
use App\Manager\EntitySortManager;
use App\Modules\Timetable\Entity\Timetable;
use App\Modules\Timetable\Entity\TimetableDay;
use App\Modules\Timetable\Entity\TimetablePeriod;
use App\Modules\Timetable\Form\TimetableDayType;
use App\Modules\Timetable\Form\TimetableDuplicatePeriodsType;
use App\Modules\Timetable\Manager\TimetableDayManager;
use App\Modules\Timetable\Pagination\TimetableDayPagination;
use App\Modules\Timetable\Pagination\TimetablePeriodPagination;
use App\Provider\ProviderFactory;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class TimetableDayController
 * @package App\Modules\Timetable\Controller
 * @author Craig Rayner <craig@craigrayner.com>
 */
class TimetableDayController extends AbstractPageController
{
    /**
     * edit
     *
     * 25/08/2020 09:47
     * @param Timetable $timetable
     * @param TimetablePeriodPagination $pagination
     * @param TimetableDay|null $timetableDay
     * @param string $tabName
     * @Route("/timetable/{timetable}/day/{timetableDay}/edit/{tabName}",name="timetable_day_edit")
     * @Route("/timetable/{timetable}/day/add/",name="timetable_day_add")
     * @IsGranted("ROLE_ROUTE")
     * @return JsonResponse
     */
    public function edit(Timetable $timetable, TimetablePeriodPagination $pagination, ?TimetableDay $timetableDay = null, string $tabName = 'Details')
    {
        if ($this->getRequest()->get('_route') === 'timetable_day_add') {
            $action = $this->generateUrl('timetable_day_add', ['timetable' => $timetable->getId()]);
            $timetableDay = new TimetableDay($timetable);
        } else {
            $action = $this->generateUrl('timetable_day_edit', ['timetable' => $timetable->getId(), 'timetableDay' => $timetableDay->getId(), 'tabName' => $tabName]);
        }

        $form = $this->createForm(TimetableDayType::class, $timetableDay, ['action' => $action]);

        if ($this->getRequest()->getContent() !== '') {
            $content = json_decode($this->getRequest()->getContent(),true);
            $form->submit($content);
            if ($form->isValid()) {
                $id = $timetableDay->getId();
                ProviderFactory::create(TimetableDay::class)->persistFlush($timetableDay);
                if ($id !== $timetableDay->getId()) {
                    $this->getStatusManager()->setReDirect($this->generateUrl('timetable_day_edit',['timetableDay' => $timetableDay->getId(),'timetable' => $timetable->getId()]), true);
               }
            } else {
                $this->getStatusManager()->invalidInputs();
            }

            return $this->singleForm($form);
        }

        if ($timetableDay->getId() !== null) {
            $this->getContainerManager()
                ->setReturnRoute($this->generateUrl('timetable_edit', ['timetable' => $timetable->getId(), 'tabName' => 'Timetable Days']))
                ->setAddElementRoute($this->generateUrl('timetable_day_add', ['timetable' => $timetable->getId()]));
            $container = new Container($tabName);
            $panel = new Panel('Details', 'Timetable', new Section('form', 'Details'));
            $container->addForm('Details', $form->createView())->addPanel($panel);

            $pagination->setContent(ProviderFactory::getRepository(TimetablePeriod::class)->findBy(['timetableDay' => $timetableDay], ['timeStart' => 'ASC']))
                ->setAddElementRoute($this->generateUrl('timetable_day_period_add', ['timetableDay' => $timetableDay->getId()]), 'Add Period')
            ;
            $panel = new Panel('Periods', 'Timetable', new Section('pagination', $pagination));
            $container->addPanel($panel);

            $this->getContainerManager()
                ->addContainer($container);
        } else {
            $this->getContainerManager()
                ->singlePanel($form->createView())
                ->setReturnRoute($this->generateUrl('timetable_edit', ['timetable' => $timetable->getId(), 'tabName' => 'Timetable Days']));
        }

        return $this->getPageManager()
            ->createBreadcrumbs($timetableDay->getId() === null ? 'Add Timetable Day' : ['Edit Timetable Day ({name})', ['{name}' => $timetableDay->getName()], 'Timetable'])
            ->render(
                [
                    'containers' => $this->getContainerManager()
                        ->getBuiltContainers()
                ]
            )
        ;
    }

    /**
     * delete
     *
     * 25/08/2020 09:24
     * @param TimetableDayPagination $pagination
     * @param TimetableDay $timetableDay
     * @Route("/timetable/day/{timetableDay}/delete}/",name="timetable_day_delete")
     * @IsGranted("ROLE_ROUTE")
     * @return Response
     */
    public function delete(TimetableDayPagination $pagination, TimetableDay $timetableDay)
    {
        $timetable = $timetableDay->getTimetable();
        ProviderFactory::create(TimetableDay::class)
            ->delete($timetableDay);

        return $this->forward(ManageController::class.'::edit',['pagination' => $pagination, 'timetable' => $timetable, 'tabName' => 'Timetable Days']);
    }

    /**
     * sort
     *
     * 25/08/2020 09:26
     * @param TimetableDay $target
     * @param TimetableDay $source
     * @param TimetableDayPagination $pagination
     * @param EntitySortManager $manager
     * @Route("/timetable/day/{source}/{target}/sort/",name="timetable_day_sort")
     * @IsGranted("ROLE_ROUTE")
     * @return JsonResponse
     */
    public function sort(TimetableDay $target, TimetableDay $source, TimetableDayPagination $pagination, EntitySortManager $manager)
    {
        $manager->setIndexName('rotate_order')
            ->setSortField('rotateOrder')
            ->execute($source, $target, $pagination);

        return $this->generateJsonResponse(['content' => $manager->getPaginationContent()]);
    }

    /**
     * duplicatePeriods
     * @param TimetableDay $timetableDay
     * @param TimetableDayManager $tdm
     * @return JsonResponse
     * @Route("/timetable/day/{timetableDay}/duplicate/periods/",name="timetable_day_period_duplicate")
     * @IsGranted("ROLE_ROUTE")
     * 5/08/2020 08:13
     */
    public function duplicatePeriods(TimetableDay $timetableDay, TimetableDayManager $tdm)
    {
        $form = $this->createForm(TimetableDuplicatePeriodsType::class, $timetableDay, ['action' => $this->generateUrl('timetable_day_period_duplicate', ['timetableDay' => $timetableDay->getId()])]);

        if ($this->getRequest()->getContent() !== '') {
            $content = json_decode($this->getRequest()->getContent(),true);
            $tdm->duplicateDayPeriods($timetableDay, $content['timetableDay']);

            return $this->singleForm($form);
        }

        $this->getContainerManager()->singlePanel($form->createView())
            ->setReturnRoute($this->generateUrl('timetable_edit', ['timetable' => $timetableDay->getTimetable()->getId(), 'tabName' => 'Timetable Days']));

        return $this->getPageManager()
            ->createBreadcrumbs('Duplicate Day Periods',
                [
                    ['uri' => 'timetable_edit', 'uri_params' => ['timetable' => $timetableDay->getTimetable()->getId(), 'tabName' => 'Timetable Days'], 'name' => 'Timetable Days' ]
                ]
            )
            ->render(
                [
                    'containers' => $this->getContainerManager()->getBuiltContainers()
                ]
            )
        ;
    }

    /**
     * delete
     *
     * 25/08/2020 09:41
     * @param TimetablePeriod $period
     * @param TimetablePeriodPagination $pagination
     * @return Response
     * @Route("/timetable/day/period/{period}/delete/",name="timetable_day_period_delete")
     * @IsGranted("ROLE_ROUTE")
     */
    public function deletePeriod(TimetablePeriod $period, TimetablePeriodPagination $pagination)
    {
        ProviderFactory::create(TimetablePeriod::class)
            ->delete($period);

        return $this->edit($period->getTimetableDay()->getTimetable(), $pagination, $period->getTimetableDay(), 'Periods');
    }
}
