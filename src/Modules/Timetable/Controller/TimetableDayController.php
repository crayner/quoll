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
     * @param ContainerManager $manager
     * @param Timetable $timetable
     * @param TimetableDay|null $timetableDay
     * @Route("/timetable/{timetable}/day/{timetableDay}/edit/",name="timetable_day_edit")
     * @Route("/timetable/{timetable}/day/add/",name="timetable_day_add")
     * @Route("/timetable/day/{timetableDay}/delete}/",name="timetable_day_delete")
     * @IsGranted("ROLE_ROUTE")
     * @return JsonResponse
     * 4/08/2020 08:20
     */
    public function edit(ContainerManager $manager, Timetable $timetable, ?TimetableDay $timetableDay = null)
    {
        if (null === $timetableDay) {
            $action = $this->generateUrl('timetable_day_add', ['timetable' => $timetable->getId()]);
            $timetableDay = new TimetableDay($timetable);
        } else {
            $action = $this->generateUrl('timetable_day_edit', ['timetable' => $timetable->getId(), 'timetableDay' => $timetableDay->getId()]);
        }

        $form = $this->createForm(TimetableDayType::class, $timetableDay, ['action' => $action]);

        $manager->singlePanel($form->createView());

        return $this->getPageManager()
            ->createBreadcrumbs($timetableDay->getId() === null ? 'Add Timetable Day' : ['Edit Timetable Day {name}', ['{name}' => $timetableDay->getName()], 'Timetable'])
            ->render(['containers' => $manager->getBuiltContainers()])
            ;
    }
}
