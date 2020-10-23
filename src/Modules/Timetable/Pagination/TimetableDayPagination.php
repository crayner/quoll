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
 * Time: 11:22
 */
namespace App\Modules\Timetable\Pagination;

use App\Manager\AbstractPagination;
use App\Manager\Hidden\PaginationAction;
use App\Manager\Hidden\PaginationColumn;
use App\Manager\Hidden\PaginationRow;
use App\Manager\PaginationInterface;
use App\Util\TranslationHelper;

/**
 * Class TimetableDayPagination
 * @package App\Modules\Timetable\Pagination
 * @author Craig Rayner <craig@craigrayner.com>
 */
class TimetableDayPagination extends AbstractPagination
{
    /**
     * execute
     * @return PaginationInterface
     * 4/08/2020 11:23
     */
    public function execute(): PaginationInterface
    {
        TranslationHelper::setDomain('Timetable');
        $row = new PaginationRow();

        $column = new PaginationColumn();
        $column->setLabel('Name')
            ->setContentKey('name')
            ->setSort()
            ->setClass('column relative pr-4 cursor-pointer widthAuto')
        ;
        $row->addColumn($column);

        $column = new PaginationColumn();
        $column->setLabel('Abbreviation')
            ->setContentKey('abbreviation')
            ->setSort()
            ->setClass('column relative pr-4 cursor-pointer widthAuto')
        ;
        $row->addColumn($column);

        $column = new PaginationColumn();
        $column->setLabel('Periods')
            ->setContentKey('periodCount')
            ->setClass('column relative pr-4 cursor-pointer widthAuto text-center')
        ;
        $row->addColumn($column);

        $column = new PaginationColumn();
        $column->setLabel('Link to Week Days')
            ->setContentKey('weekDays')
            ->setClass('column relative pr-4 cursor-pointer widthAuto')
        ;
        $row->addColumn($column);

        $column = new PaginationColumn();
        $column->setLabel('Fixed')
            ->setContentKey('fixed')
            ->setClass('column relative pr-4 cursor-pointer widthAuto text-center')
        ;
        $row->addColumn($column);

        $action = new PaginationAction('Timetable');
        $action->setTitle('Edit')
            ->setAClass('p-3 sm:p-0')
            ->setColumnClass('column p-2 sm:p-3')
            ->setSpanClass('fas fa-edit fa-fw fa-1-5x text-gray-800 hover:text-indigo-500')
            ->setRoute('timetable_day_edit')
            ->setRouteParams(['timetableDay' => 'id','timetable' => 'timetable']);
        $row->addAction($action);

        $action = new PaginationAction('Timetable');
        $action->setTitle('Copy Periods')
            ->setAClass('p-3 sm:p-0')
            ->setColumnClass('column p-2 sm:p-3')
            ->setSpanClass('far fa-clone fa-fw fa-1-5x text-gray-800 hover:text-orange-500')
            ->setRoute('timetable_day_period_duplicate')
            ->setDisplayWhen('hasPeriods')
            ->setRouteParams(['timetableDay' => 'id']);
        $row->addAction($action);

        $action = new PaginationAction();
        $action->setTitle('Remove All Periods')
            ->setAClass('p-3 sm:p-0')
            ->setColumnClass('column p-2 sm:p-3')
            ->setSpanClass('fas fa-eraser fa-fw fa-1-5x text-gray-800 hover:text-red-500')
            ->setRoute('timetable_day_period_remove_all')
            ->setDisplayWhen('hasPeriods')
            ->setRouteParams(['timetableDay' => 'id']);
        $row->addAction($action);

        $action = new PaginationAction();
        $action->setTitle('Delete')
            ->setAClass('p-3 sm:p-0')
            ->setColumnClass('column p-2 sm:p-3')
            ->setSpanClass('far fa-trash-alt fa-fw fa-1-5x text-gray-800 hover:text-red-500')
            ->setRoute('timetable_day_delete')
            ->setOnClick('areYouSure')
            ->setDisplayWhen('canDelete')
            ->setRouteParams(['timetableDay' => 'id']);
        $row->addAction($action);

        $row->addHighlight([
            'className' => 'warning',
            'columnKey' => 'isFixed',
            'columnValue' => true,
        ]);
        $this->setRow($row);
        return $this;
    }
}
