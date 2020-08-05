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

use App\Container\ContainerManager;
use App\Controller\AbstractPageController;
use App\Modules\Timetable\Entity\Timetable;
use App\Modules\Timetable\Entity\TimetableDay;
use App\Modules\Timetable\Form\TimetableDayType;
use App\Modules\Timetable\Pagination\TimetableDayPagination;
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
     * @param TimetableDay|null $timetableDay
     * @Route("/timetable/{timetable}/day/{timetableDay}/edit/",name="timetable_day_edit")
     * @Route("/timetable/{timetable}/day/add/",name="timetable_day_add")
     * @IsGranted("ROLE_ROUTE")
     * @return JsonResponse
     * 4/08/2020 08:20
     */
    public function edit(Timetable $timetable, ?TimetableDay $timetableDay = null)
    {
        if ($this->getRequest()->get('_route') === 'timetable_day_add') {
            $action = $this->generateUrl('timetable_day_add', ['timetable' => $timetable->getId()]);
            $timetableDay = new TimetableDay($timetable);
        } else {
            $action = $this->generateUrl('timetable_day_edit', ['timetable' => $timetable->getId(), 'timetableDay' => $timetableDay->getId()]);
        }
        $manager = $this->getContainerManager();
        dump($timetableDay);

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

        $manager->singlePanel($form->createView())
            ->setReturnRoute($this->generateUrl('timetable_edit', ['timetable' => $timetable->getId(), 'tabName' => 'Timetable Days']));

        if ($timetableDay->getId() !== null) {
            $manager->setAddElementRoute($this->generateUrl('timetable_day_add', ['timetable' => $timetable->getId()]));
        }
        return $this->getPageManager()
            ->createBreadcrumbs($timetableDay->getId() === null ? 'Add Timetable Day' : ['Edit Timetable Day {name}', ['{name}' => $timetableDay->getName()], 'Timetable'])
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
}
