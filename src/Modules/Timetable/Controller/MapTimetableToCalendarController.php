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
 * Date: 5/08/2020
 * Time: 16:14
 */
namespace App\Modules\Timetable\Controller;

use App\Container\Container;
use App\Container\Panel;
use App\Container\Section;
use App\Controller\AbstractPageController;
use App\Modules\Timetable\Entity\Timetable;
use App\Modules\Timetable\Entity\TimetableDay;
use App\Modules\Timetable\Entity\TimetableDate;
use App\Modules\Timetable\Manager\MappingManager;
use App\Provider\ProviderFactory;
use DateTimeImmutable;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class MapTimetableToCalendarController
 * @package App\Modules\Timetable\Controller
 * @author Craig Rayner <craig@craigrayner.com>
 */
class MapTimetableToCalendarController extends AbstractPageController
{
    /**
     * map
     * @param MappingManager $manager
     * @param Timetable|null $timetable
     * @param string|null $tabName
     * @return JsonResponse
     * 5/08/2020 16:16
     * @throws \Exception
     * @Route("/timetable/calendar/map/{timetable}/{tabName}",name="timetable_calendar_map")
     * @IsGranted("ROLE_ROUTE")
     */
    public function map(MappingManager $manager, ?Timetable $timetable = null, ?string $tabName = null)
    {
        $manager->execute($timetable);
        if ($tabName === null) $tabName = $manager->getTerms()->first()->getName();

        $container = new Container($tabName);
        foreach ($manager->getTerms() as $term) {
            $panel = new Panel($term->getName(), 'Timetable', new Section('special', $term->toArray()));
            $panel->addSection(new Section('html', $this->renderView('timetable/timetable_mapping_help.html.twig')));
            $container->addPanel($panel);
        }

        $this->getContainerManager()->addContainer($container);

        return $this->getPageManager()
            ->createBreadcrumbs('Timetable Calendar Map')
            ->render(['containers' => $this->getContainerManager()->getBuiltContainers()]);
    }

    /**
     * shuffleTermDays
     * @param Timetable $timetable
     * @param DateTimeImmutable $date
     * @param MappingManager $manager
     * @return JsonResponse
     * @Route("/timetable/{timetable}/calendar/ripple/{date}/columns/",name="timetable_calendar_ripple_map")
     * 9/08/2020 09:45
     */
    public function rippleTermDayColumns(Timetable $timetable, DateTimeImmutable $date, MappingManager $manager)
    {
        $term = $manager->setTimetable($timetable)
            ->execute()
            ->getTermByDate($date);

        $data = $manager->rippleTermColumns($term,$date);
        $data['weeks'] = $term->getWeeksArray();

        return new JsonResponse($data);
    }

    /**
     * moveToNextColumn
     * @param Timetable $timetable
     * @param DateTimeImmutable $date
     * @param MappingManager $manager
     * @return JsonResponse
     * @Route("/timetable/{timetable}/calendar/next/{date}/column/",name="timetable_calendar_single_day_map")
     * 9/08/2020 09:46
     */
    public function moveToNextColumn(Timetable $timetable, DateTimeImmutable $date, MappingManager $manager)
    {
        $tDayDate = ProviderFactory::getRepository(TimetableDate::class)->findOneByTimetableDate($timetable, $date);
        $tDays = ProviderFactory::getRepository(TimetableDay::class)->findBy([], ['rotateOrder' => 'ASC']);
        $grab = false;
        foreach ($tDays as $q=>$w) {
            if ($grab) {
                $tDayDate->setTimetableDay($w);
                $grab = false;
                break;
            }
            if ($tDayDate->getTimetableDay() === $w) {
                $grab = true;
            }
        }
        if ($grab) { // because the last day coould have been it.
            $tDayDate->setTimetableDay($tDays[0]);
        }
        $data = ProviderFactory::create(TimetableDate::class)->persistFlush($tDayDate);

        $manager->execute($timetable);

        $term = $manager->getTermByDate($date);

        $data['weeks'] = $term->getWeeksArray();
        return new JsonResponse($data);
    }
}
