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
 * Date: 3/08/2020
 * Time: 13:43
 */
namespace App\Modules\Timetable\Controller;

use App\Container\Container;
use App\Container\ContainerManager;
use App\Container\Panel;
use App\Container\Section;
use App\Controller\AbstractPageController;
use App\Modules\School\Util\AcademicYearHelper;
use App\Modules\Timetable\Entity\Timetable;
use App\Modules\Timetable\Entity\TimetableDay;
use App\Modules\Timetable\Entity\TimetablePeriod;
use App\Modules\Timetable\Form\TimetableType;
use App\Modules\Timetable\Pagination\TimetableDayPagination;
use App\Modules\Timetable\Pagination\TimetablePagination;
use App\Provider\ProviderFactory;
use App\Util\ErrorMessageHelper;
use App\Util\TranslationHelper;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class ListController
 * @package App\Modules\Timetable\Controller
 * @author Craig Rayner <craig@craigrayner.com>
 */
class ManageController extends AbstractPageController
{
    /**
     * list
     *
     * 25/08/2020 08:45
     * @param TimetablePagination $pagination
     * @Route("/timetable/list/",name="timetable_list")
     * @IsGranted("ROLE_ROUTE")
     * @return JsonResponse
     */
    public function list(TimetablePagination $pagination)
    {
        $currentYear = AcademicYearHelper::getCurrentAcademicYear();
        $pagination->setContent(ProviderFactory::getRepository(Timetable::class)->findBy(['academicYear' => $currentYear],['name' => 'ASC']))
            ->setAddElementRoute($this->generateUrl('timetable_add'));

        return $this->getPageManager()
            ->createBreadcrumbs(['Timetables in Academic Year {name}', ['{name}' => $currentYear->getName()], 'Timetable'])
            ->setMessages($this->getStatusManager()->getMessageArray())
            ->setUrl($this->generateUrl('timetable_list'))
            ->render(
                [
                    'pagination' => $pagination->toArray(),
                ]
            );
    }

    /**
     * edit
     *
     * 25/08/2020 08:45
     * @param TimetableDayPagination $pagination
     * @param Timetable|null $timetable
     * @param string $tabName
     * @Route("/timetable/{timetable}/edit/{tabName}",name="timetable_edit")
     * @Route("/timetable/add/",name="timetable_add")
     * @IsGranted("ROLE_ROUTE")
     * @return JsonResponse
     */
    public function edit(TimetableDayPagination $pagination, ?Timetable $timetable = null, string $tabName = 'Details')
    {
        if (null === $timetable || $this->getRequest()->get('_route') === 'timetable_add') {
            $action = $this->generateUrl('timetable_add');
            $timetable = new Timetable(AcademicYearHelper::getCurrentAcademicYear(true));
            $timetable->setAcademicYear(AcademicYearHelper::getCurrentAcademicYear(true));
        } else {
            $action = $this->generateUrl('timetable_edit', ['timetable' => $timetable->getId(), 'tabName' => $tabName]);
        }

        $form = $this->createForm(TimetableType::class, $timetable, ['action' => $action]);

        if ($this->getRequest()->getContent() !== '') {
            $content = json_decode($this->getRequest()->getContent(), true);
            $form->submit($content);
            if ($form->isValid()) {
                $id = $timetable->getId();
                ProviderFactory::create(Timetable::class)->persistFlush($timetable);
                if ($timetable->getId() !== $id) {
                    $action = $this->generateUrl('timetable_edit', ['timetable' => $timetable->getId()]);
                    $this->getStatusManager()->setReDirect($action, true);
                }
                if ($this->isStatusSuccess()) {
                    $form = $this->createForm(TimetableType::class, $timetable, ['action' => $action]);
                }
            } else {
                $this->getStatusManager()->invalidInputs();
            }

            return $this->singleForm($form);
        }

        if ($timetable->getId() === null) {
            $this->getContainerManager()->singlePanel($form->createView())
                ->setReturnRoute($this->generateUrl('timetable_list'));
        } else {
            $container = new Container($tabName);
            $panel = new Panel('Details', 'Timetable', new Section('form','Details'));
            $container->addForm('Details', $form->createView())
                ->addPanel($panel);

            $content = ProviderFactory::getRepository(TimetableDay::class)->findBy(['timetable' => $timetable],['rotateOrder' => 'ASC']);
            $pagination->setContent($content)
                ->setDraggableRoute('timetable_day_sort')
                ->setAddElementRoute($this->generateUrl('timetable_day_add', ['timetable' => $timetable->getId()]), TranslationHelper::translate('Add Timetable Day'))
            ;
            $panel = new Panel('Timetable Days', 'Timetable', new Section('pagination', $pagination));
            $container->addPanel($panel);

            $this->getContainerManager()->addContainer($container)
                ->setReturnRoute($this->generateUrl('timetable_list'))
                ->setAddElementRoute($this->generateUrl('timetable_add'), TranslationHelper::translate('Add Timetable'));

        }

        return $this->getPageManager()
            ->setUrl($action)
            ->createBreadcrumbs($timetable->getId() === null ? 'Add Timetable' : ['Edit Timetable {name}', ['{name}' => $timetable->getName()], 'Timetable'])
            ->render(['containers' => $this->getContainerManager()->getBuiltContainers()])
        ;
    }

    /**
     * delete
     *
     * 25/08/2020 08:46
     * @param TimetablePagination $pagination
     * @param Timetable $timetable
     * @Route("/timetable/{timetable}/delete/",name="timetable_delete")
     * @IsGranted("ROLE_ROUTE")
     * @return JsonResponse
     */
    public function delete(TimetablePagination $pagination, Timetable $timetable)
    {
        ProviderFactory::create(Timetable::class)
            ->delete($timetable);

        return $this->list($pagination);
    }

    /**
     * removeDayAllPeriods
     *
     * 25/08/2020 10:37
     * @param TimetableDay $timetableDay
     * @param TimetableDayPagination $pagination
     * @Route("/timetable/day/{timetableDay}/remove/all/periods/",name="timetable_day_period_remove_all")
     * @IsGranted("ROLE_ROUTE")
     * @return JsonResponse
     */
    public function removeDayAllPeriods(TimetableDay $timetableDay, TimetableDayPagination $pagination)
    {
        $timetable = $timetableDay->getTimetable();
        foreach ($timetableDay->getPeriods() as $period) {
            ProviderFactory::create(TimetablePeriod::class)->delete($period);
        }

        return $this->edit($pagination, $timetable, 'Timetable Days');
    }
}
