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
use App\Modules\Timetable\Entity\TimetableDay;
use App\Modules\Timetable\Entity\TimetablePeriod;
use App\Modules\Timetable\Form\TimetablePeriodType;
use App\Modules\Timetable\Pagination\TimetablePeriodPagination;
use App\Provider\ProviderFactory;
use App\Util\ErrorMessageHelper;
use App\Util\TranslationHelper;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
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
     * @param TimetablePeriodPagination $pagination
     * @param TimetableDay $timetableDay
     * @param TimetablePeriod|null $timetablePeriod
     * @Route("/timetable/day/{timetableDay}/period/{timetablePeriod}/edit/",name="timetable_day_period_edit")
     * @Route("/timetable/day/{timetableDay}/period/add/",name="timetable_day_period_add")
     * @IsGranted("ROLE_ROUTE")
     * @return JsonResponse
     * 4/08/2020 12:08
     */
    public function edit(TimetablePeriodPagination $pagination, TimetableDay $timetableDay, ?TimetablePeriod $timetablePeriod = null)
    {
        if (null === $timetablePeriod || $this->getRequest()->get('_route') === 'timetable_day_period_add') {
            $action = $this->generateUrl('timetable_day_period_add', ['timetableDay' => $timetableDay->getId()]);
            $timetablePeriod = new TimetablePeriod($timetableDay);
        } else {
            $action = $this->generateUrl('timetable_day_period_edit', ['timetableDay' => $timetableDay->getId(), 'timetablePeriod' => $timetablePeriod->getId()]);
        }
dump($action,$timetablePeriod,$timetableDay);
        $form = $this->createForm(TimetablePeriodType::class, $timetablePeriod, ['action' => $action]);

        if ($this->getRequest()->getContent() !== '') {
            $content = json_decode($this->getRequest()->getContent(), true);
            $form->submit($content);
            if ($form->isValid()) {
                $id = $timetablePeriod->getId();
                $data = ProviderFactory::create(TimetablePeriod::class)->persistFlush($timetablePeriod,[]);
                if ($timetablePeriod->getId() !== $id) {
                    $data['status'] = 'redirect';
                    $data['redirect'] = $this->generateUrl('timetable_day_period_edit', ['timetableDay' => $timetableDay->getId(), 'timetablePeriod' => $timetablePeriod->getId()]);
                }
            } else {
                $data = ErrorMessageHelper::getInvalidInputsMessage([], true);
            }
            $this->getContainerManager()->singlePanel($form->createView());
            $data['form'] = $this->getContainerManager()->getFormFromContainer();
            return new JsonResponse($data);
        }

        $this->getContainerManager()->singlePanel($form->createView())
            ->setReturnRoute($this->generateUrl('timetable_day_edit', ['timetable' => $timetableDay->getTimetable()->getId(), 'timetableDay' => $timetableDay->getId(), 'tabName' => 'Periods']));

        if ($timetableDay->getId() !== null) $this->getContainerManager()->setAddElementRoute($this->generateUrl('timetable_day_period_add', ['timetableDay' => $timetableDay->getId()]));

        return $this->getPageManager()
            ->createBreadcrumbs($timetablePeriod->getId() === null ? ['Add Timetable Period in {day}', ['{day}' => $timetableDay->getName()]] : ['Edit Timetable Period in {day} - {name}', ['{name}' => $timetablePeriod->getName(),'{day}' => $timetableDay->getName()]])
            ->render(['containers' => $this->getContainerManager()->getBuiltContainers()])
            ;
    }

    /**
     * delete
     * @param TimetablePeriodPagination $pagination
     * @param TimetablePeriod $timetablePeriod
     * @Route("/timetable/day/period/{period}/delete/",name="timetable_day_period_delete")
     * @IsGranted("ROLE_ROUTE")
     * @return Response
     * 5/08/2020 07:47
     */
    public function delete(TimetablePeriodPagination $pagination, TimetablePeriod $timetablePeriod)
    {
        $provider = ProviderFactory::create(TimetablePeriod::class);
        $timetableDay = $timetablePeriod->getTimetableDay();
        $provider->delete($timetablePeriod);
        $data = $provider->getMessageManager()->pushToJsonData();

        return $this->forward(TimetableDayController::class.'::edit',['pagination' => $pagination, 'timetableDay' => $timetableDay, 'messages' => $data['errors'] ?? [], 'tabName' => 'Periods']);
    }
}
