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
use App\Modules\Timetable\Entity\TimetableColumn;
use App\Modules\Timetable\Entity\TimetableColumnPeriod;
use App\Modules\Timetable\Form\TimetableColumnPeriodType;
use App\Modules\Timetable\Pagination\TimetableColumnPeriodPagination;
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
class TimetableColumnPeriodController extends AbstractPageController
{
    /**
     * edit
     * @param TimetableColumnPeriodPagination $pagination
     * @param TimetableColumn $column
     * @param TimetableColumnPeriod|null $row
     * @param string $tabName
     * @Route("/timetable/column/{column}/row/add/",name="timetable_column_row_add")
     * @Route("/timetable/column/{column}/row/{row}/edit/",name="timetable_column_row_edit")
     * @IsGranted("ROLE_ROUTE")
     * @return JsonResponse
     * 4/08/2020 12:08
     */
    public function edit(TimetableColumnPeriodPagination $pagination, TimetableColumn $column, ?TimetableColumnPeriod $row)
    {
        if (null === $row) {
            $action = $this->generateUrl('timetable_column_row_add', ['column' => $column->getId()]);
            $row = new TimetableColumnPeriod($column);
        } else {
            $action = $this->generateUrl('timetable_column_row_edit', ['column' => $column->getId(), 'row' => $row->getId()]);
        }

        $form = $this->createForm(TimetableColumnPeriodType::class, $row, ['action' => $action]);

        if ($this->getRequest()->getContent() !== '') {
            $content = json_decode($this->getRequest()->getContent(), true);
            $form->submit($content);
            if ($form->isValid()) {
                $id = $row->getId();
                $data = ProviderFactory::create(TimetableColumnPeriod::class)->persistFlush($row,[]);
                if ($row->getId() !== $id) {
                    $data['status'] = 'redirect';
                    $data['redirect'] = $this->generateUrl('timetable_column_row_edit', ['column' => $column->getId(), 'row' => $row->getId()]);
                }
            } else {
                $data = ErrorMessageHelper::getInvalidInputsMessage([], true);
            }
            $this->getContainerManager()->singlePanel($form->createView());
            $data['form'] = $this->getContainerManager()->getFormFromContainer();
            return new JsonResponse($data);
        }

        $this->getContainerManager()->singlePanel($form->createView())
            ->setReturnRoute($this->generateUrl('timetable_column_edit', ['column' => $column->getId(), 'tabName' => 'Column Rows']));

        if ($column->getId() !== null) $this->getContainerManager()->setAddElementRoute($this->generateUrl('timetable_column_row_add', ['column' => $column->getId()]));

        return $this->getPageManager()
            ->createBreadcrumbs($row->getId() === null ? ['Add Timetable Column Row {column}', ['{column}' => $column->getName()]] : ['Edit Timetable Column Row {column} - {name}', ['{name}' => $row->getName(),'{column}' => $column->getName()], 'Timetable'])
            ->render(['containers' => $this->getContainerManager()->getBuiltContainers()])
            ;
    }

    /**
     * delete
     * @param TimetableColumnPeriodPagination $pagination
     * @param TimetableColumnPeriod $row
     * @Route("/timetable/column/row/{row}/delete/",name="timetable_column_row_delete")
     * @IsGranted("ROLE_ROUTE")
     * @return Response
     * 5/08/2020 07:47
     */
    public function delete(TimetableColumnPeriodPagination $pagination, TimetableColumnPeriod $row)
    {
        $provider = ProviderFactory::create(TimetableColumnPeriod::class);
        $column = $row->getTimetableColumn();
        $provider->delete($row);
        $data = $provider->getMessageManager()->pushToJsonData();

        return $this->forward(TimetableColumnController::class.'::edit',['pagination' => $pagination, 'column' => $column, 'messages' => $data['errors'] ?? [], 'tabName' => 'Column Rows']);
    }
}
