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
use App\Util\ErrorMessageHelper;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
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
     * @param Timetable $timetable
     * @param TimetablePeriodPagination $pagination
     * @param TimetableDay|null $timetableDay
     * @param string $tabName
     * @return JsonResponse
     * 4/08/2020 08:20
     * @Route("/timetable/{timetable}/day/{timetableDay}/edit/{tabName}",name="timetable_day_edit")
     * @Route("/timetable/{timetable}/day/add/",name="timetable_day_add")
     * @IsGranted("ROLE_ROUTE")
     */
    public function edit(Timetable $timetable, TimetablePeriodPagination $pagination, ?TimetableDay $timetableDay = null, string $tabName = 'Details')
    {
        if ($this->getRequest()->get('_route') === 'timetable_day_add') {
            $action = $this->generateUrl('timetable_day_add', ['timetable' => $timetable->getId()]);
            $timetableDay = new TimetableDay($timetable);
        } else {
            $action = $this->generateUrl('timetable_day_edit', ['timetable' => $timetable->getId(), 'timetableDay' => $timetableDay->getId(), 'tabName' => $tabName]);
        }
        $manager = $this->getContainerManager();

        $form = $this->createForm(TimetableDayType::class, $timetableDay, ['action' => $action]);

        if ($this->getRequest()->getContent() !== '') {
            $content = json_decode($this->getRequest()->getContent(),true);
            $form->submit($content);
            if ($form->isValid()) {
                $id = $timetableDay->getId();
                $data = ProviderFactory::create(TimetableDay::class)->persistFlush($timetableDay);
                if ($id !== $timetableDay->getId()) {
                    $data['status'] = 'redirect';
                    $data['redirect'] = $this->generateUrl('timetable_day_edit',['timetableDay' => $timetableDay->getId(),'timetable' => $timetable->getId()]);
               }
            } else {
                $data = ErrorMessageHelper::getInvalidInputsMessage([], true);
            }

            $this->getContainerManager()->singlePanel($form->createView());
            $data['form'] = $this->getContainerManager()->getFormFromContainer();
            return new JsonResponse($data);
        }

        if ($timetableDay->getId() !== null) {
            $manager->setReturnRoute($this->generateUrl('timetable_edit', ['timetable' => $timetable->getId(), 'tabName' => 'Timetable Days']))
                ->setAddElementRoute($this->generateUrl('timetable_day_add', ['timetable' => $timetable->getId()]));
            $container = new Container($tabName);
            $panel = new Panel('Details', 'Timetable', new Section('form', 'Details'));
            $container->addForm('Details', $form->createView())->addPanel($panel);

            $pagination->setContent(ProviderFactory::getRepository(TimetablePeriod::class)->findBy(['timetableDay' => $timetableDay], ['timeStart' => 'ASC']))
                ->setAddElementRoute($this->generateUrl('timetable_day_period_add', ['timetableDay' => $timetableDay->getId()]), 'Add Period')
            ;
            $panel = new Panel('Periods', 'Timetable', new Section('pagination', $pagination));
            $container->addPanel($panel);

            $manager->addContainer($container);
        } else {
            $manager->singlePanel($form->createView())
                ->setReturnRoute($this->generateUrl('timetable_edit', ['timetable' => $timetable->getId(), 'tabName' => 'Timetable Days']));
        }
        return $this->getPageManager()
            ->createBreadcrumbs($timetableDay->getId() === null ? 'Add Timetable Day' : ['Edit Timetable Day ({name})', ['{name}' => $timetableDay->getName()], 'Timetable'])
            ->render(['containers' => $manager->getBuiltContainers()])
            ;
    }

    /**
     * delete
     * @param TimetableDayPagination $pagination
     * @param TimetableDay $timetableDay
     * @Route("/timetable/day/{timetableDay}/delete}/",name="timetable_day_delete")
     * @IsGranted("ROLE_ROUTE")
     * 5/08/2020 15:11
     */
    public function delete(TimetableDayPagination $pagination, TimetableDay $timetableDay)
    {
        $provider = ProviderFactory::create(TimetableDay::class);
        $timetable = $timetableDay->getTimetable();
        $provider->delete($timetableDay);
        $data = $provider->getMessageManager()->pushToJsonData();

        return $this->forward(ManageController::class.'::edit',['pagination' => $pagination, 'timetable' => $timetable, 'messages' => $data['errors'] ?? [], 'tabName' => 'Timetable Days']);
    }

    /**
     * sort
     * @param TimetableDay $target
     * @param TimetableDay $source
     * @param TimetableDayPagination $pagination
     * @return JsonResponse
     * @Route("/timetable/day/{source}/{target}/sort/",name="timetable_day_sort")
     * @IsGranted("ROLE_ROUTE")
     * 13/06/2020 10:49
     */
    public function sort(TimetableDay $target, TimetableDay $source, TimetableDayPagination $pagination)
    {
        $manager = new EntitySortManager();
        $manager->setIndexName('rotate_order')
            ->setSortField('rotateOrder')
            ->execute($source, $target, $pagination);

        return new JsonResponse($manager->getDetails());
    }

    /**
     * duplicatePeriods
     * @param TimetableDay $timetableDay
     * @param TimetableDayManager $tdm
     * @return JsonResponse
     * @Route("/timetable/day/{timetableDay}/duplicate/periods/",name="timetable_day_period_duplicate")
     * 5/08/2020 08:13
     */
    public function duplicatePeriods(TimetableDay $timetableDay, TimetableDayManager $tdm)
    {
        $form = $this->createForm(TimetableDuplicatePeriodsType::class, $timetableDay, ['action' => $this->generateUrl('timetable_day_period_duplicate', ['timetableDay' => $timetableDay->getId()])]);

        if ($this->getRequest()->getContent() !== '') {
            $content = json_decode($this->getRequest()->getContent(),true);
            $data = $tdm->duplicateDayPeriods($timetableDay, $content['timetableDay']);
            $this->getContainerManager()->singlePanel($form->createView());
            $data['form'] = $this->getContainerManager()->getFormFromContainer();
            return new JsonResponse($data);
        }

        $this->getContainerManager()->singlePanel($form->createView())
            ->setReturnRoute($this->generateUrl('timetable_edit', ['timetable' => $timetableDay->getTimetable()->getId(), 'tabName' => 'Timetable Days']));
        return $this->getPageManager()
            ->createBreadcrumbs('Duplicate Day Periods',
                [
                    ['uri' => 'timetable_edit', 'uri_params' => ['timetable' => $timetableDay->getTimetable()->getId(), 'tabName' => 'Timetable Days'], 'name' => 'Timetable Days' ]
                ]
            )
            ->render(['containers' => $this->getContainerManager()->getBuiltContainers()]);
    }
}
