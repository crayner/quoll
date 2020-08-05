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
 * Time: 11:19
 */

namespace App\Modules\Timetable\Controller;


use App\Container\Container;
use App\Container\ContainerManager;
use App\Container\Panel;
use App\Container\Section;
use App\Controller\AbstractPageController;
use App\Modules\Timetable\Entity\TimetableColumn;
use App\Modules\Timetable\Entity\TimetableColumnPeriod;
use App\Modules\Timetable\Form\TimetableColumnDuplicatePeriodsType;
use App\Modules\Timetable\Form\TimetableColumnType;
use App\Modules\Timetable\Manager\TimetableColumnManager;
use App\Modules\Timetable\Pagination\TimetableColumnPagination;
use App\Modules\Timetable\Pagination\TimetableColumnPeriodPagination;
use App\Provider\ProviderFactory;
use App\Util\ErrorMessageHelper;
use App\Util\TranslationHelper;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class TimetableColumnController extends AbstractPageController
{
    /**
     * list
     * @param TimetableColumnPagination $pagination
     * @param array $messages
     * @return JsonResponse
     * @Route("/timetable/column/list/",name="timetable_column_list")
     * @Route("/timetable/column/{column}/delete/",name="timetable_column_delete")
     * @IsGranted("ROLE_ROUTE")
     * 4/08/2020 11:19
     */
    public function list(TimetableColumnPagination $pagination, array $messages = [])
    {
        $pagination->setContent(ProviderFactory::getRepository(TimetableColumn::class)->findBy([],['name' => 'ASC']))
            ->setAddElementRoute($this->generateUrl('timetable_column_add'));

        return $this->getPageManager()
            ->createBreadcrumbs('Timetable Columns')
            ->setMessages($messages)
            ->render([
                'pagination' => $pagination->toArray(),
                'url' => $this->generateUrl('timetable_column_list')
            ]);
    }

    /**
     * edit
     * @param ContainerManager $manager
     * @param TimetableColumnPeriodPagination $pagination
     * @param TimetableColumn|null $column
     * @param string $tabName
     * @param array $messages
     * @return JsonResponse
     * 4/08/2020 11:29
     * @Route("/timetable/column/add/",name="timetable_column_add")
     * @Route("/timetable/column/{column}/edit/{tabName}",name="timetable_column_edit")
     * @IsGranted("ROLE_ROUTE")
     */
    public function edit(ContainerManager $manager, TimetableColumnPeriodPagination $pagination, ?TimetableColumn $column = null, string $tabName = 'Details', array $messages = [])
    {
        if (null === $column) {
            $action = $this->generateUrl('timetable_column_add');
            $column = new TimetableColumn();
        } else {
            $action = $this->generateUrl('timetable_column_edit', ['column' => $column->getId(), 'tabName' => $tabName]);
        }

        $form = $this->createForm(TimetableColumnType::class, $column, ['action' => $action]);

        if ($this->getRequest()->getContent() !== '') {
            $content = json_decode($this->getRequest()->getContent(), true);
            $form->submit($content);
            if ($form->isValid()) {
                $id = $column->getId();
                $data = ProviderFactory::create(TimetableColumn::class)->persistFlush($column,[]);
                if ($column->getId() !== $id) {
                    $data['status'] = 'redirect';
                    $data['redirect'] = $this->generateUrl('timetable_column_edit', ['column' => $column->getId()]);
                }
            } else {
                $data = ErrorMessageHelper::getDatabaseErrorMessage([], true);
            }
            $manager->singlePanel($form->createView());
            $data['form'] = $manager->getFormFromContainer();
            return new JsonResponse($data);
        }

        if ($column->getId() !== null) {
            $container = new Container($tabName);
            $panel = new Panel('Details', 'Timetable', new Section('form', 'Details'));
            $container->addForm('Details', $form->createView())
                ->addPanel($panel);
            $content = ProviderFactory::getRepository(TimetableColumnPeriod::class)->findBy(['timetableColumn' => $column->getId()],['timeStart' => 'ASC']);
            $pagination->setContent($content)
                ->setAddElementRoute($this->generateUrl('timetable_column_row_add', ['column' => $column->getId()]));
            $panel = new Panel('Column Rows', 'Timetable', new Section('pagination', $pagination));
            $container->addPanel($panel);

            $manager->addContainer($container)
                ->setReturnRoute($this->generateUrl('timetable_column_list'))
                ->setAddElementRoute($this->generateUrl('timetable_column_add'), TranslationHelper::translate('Add Timetable Column'));
        } else {
            $manager->singlePanel($form->createView())
                ->setReturnRoute($this->generateUrl('timetable_column_list'));
        }
        return $this->getPageManager()
            ->setUrl($action)
            ->setMessages($messages)
            ->createBreadcrumbs($column->getId() === null ? 'Add Timetable Column' : ['Edit Timetable Column {name}', ['{name}' => $column->getName()], 'Timetable'])
            ->render(['containers' => $manager->getBuiltContainers()])
            ;
    }

    /**
     * duplicatePeriods
     * @param TimetableColumn $column
     * @param TimetableColumnManager $tcm
     * @return JsonResponse
     * @Route("/timetable/column/{column}/duplicate/periods/",name="timetable_column_duplicate_periods")
     * 5/08/2020 08:13
     */
    public function duplicatePeriods(TimetableColumn $column, TimetableColumnManager $tcm)
    {
        $form = $this->createForm(TimetableColumnDuplicatePeriodsType::class, $column, ['action' => $this->generateUrl('timetable_column_duplicate_periods', ['column' => $column->getId()])]);
        if ($this->getRequest()->getContent() !== '') {
            $content = json_decode($this->getRequest()->getContent(),true);
            $data = $tcm->duplicateColumnPeriods($column, $content['timetableColumn']);
            $this->getContainerManager()->singlePanel($form->createView());
            $data['form'] = $this->getContainerManager()->getFormFromContainer();
            return new JsonResponse($data);
        }

        $this->getContainerManager()->singlePanel($form->createView())
            ->setReturnRoute($this->generateUrl('timetable_column_list'));
        return $this->getPageManager()
            ->createBreadcrumbs('Duplicate Column Periods',
                [
                    ['uri' => 'timetable_column_list', 'name' => 'Timetable Columns' ]
                ]
            )
            ->render(['containers' => $this->getContainerManager()->getBuiltContainers()]);
    }
}
