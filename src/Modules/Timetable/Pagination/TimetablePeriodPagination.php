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
 * Time: 11:43
 */
namespace App\Modules\Timetable\Pagination;

use App\Manager\AbstractPaginationManager;
use App\Manager\Hidden\PaginationAction;
use App\Manager\Hidden\PaginationColumn;
use App\Manager\Hidden\PaginationRow;
use App\Manager\PaginationInterface;
use App\Util\TranslationHelper;

/**
 * Class TimetableColumnPeriodPagination
 * @package App\Modules\Timetable\Pagination
 * @author Craig Rayner <craig@craigrayner.com>
 */
class TimetablePeriodPagination extends AbstractPaginationManager
{
    public function execute(): PaginationInterface
    {
        TranslationHelper::setDomain('Timetable');
        $row = new PaginationRow();

        $column = new PaginationColumn();
        $column->setLabel('Name')
            ->setContentKey('name')
            ->setClass('column relative pr-4 cursor-pointer widthAuto')
        ;
        $row->addColumn($column);

        $column = new PaginationColumn();
        $column->setLabel('Abbreviation')
            ->setContentKey('abbreviation')
            ->setClass('column relative pr-4 cursor-pointer widthAuto')
        ;
        $row->addColumn($column);

        $column = new PaginationColumn();
        $column->setLabel('Time')
            ->setContentKey('time')
            ->setClass('column relative pr-4 cursor-pointer widthAuto')
        ;
        $row->addColumn($column);

        $column = new PaginationColumn();
        $column->setLabel('Type')
            ->setContentKey('type')
            ->setClass('column relative pr-4 cursor-pointer widthAuto')
        ;
        $row->addColumn($column);

        $column = new PaginationColumn();
        $column->setLabel('Classes')
            ->setContentKey('classes')
            ->setClass('column relative pr-4 cursor-pointer widthAuto text-centre')
        ;
        $row->addColumn($column);

        $action = new PaginationAction();
        $action->setTitle('Edit')
            ->setAClass('thickbox p-3 sm:p-0')
            ->setColumnClass('column p-2 sm:p-3')
            ->setSpanClass('fas fa-edit fa-fw fa-1-5x text-gray-800 hover:text-indigo-500')
            ->setRoute('timetable_day_period_edit')
            ->setRouteParams(['timetableDay' => 'timetableDay', 'timetablePeriod' => 'id']);
        $row->addAction($action);

        $action = new PaginationAction();
        $action->setTitle('Manage Classes in Period')
            ->setAClass('thickbox p-3 sm:p-0')
            ->setColumnClass('column p-2 sm:p-3')
            ->setSpanClass('fas fa-pen-fancy fa-fw fa-1-5x text-gray-800 hover:text-indigo-500')
            ->setRoute('timetable_day_period_classes_manage')
            ->setRouteParams(['period' => 'id']);
        $row->addAction($action);

        $action = new PaginationAction();
        $action->setTitle('Delete')
            ->setAClass('thickbox p-3 sm:p-0')
            ->setColumnClass('column p-2 sm:p-3')
            ->setSpanClass('far fa-trash-alt fa-fw fa-1-5x text-gray-800 hover:text-red-500')
            ->setRoute('timetable_day_period_delete')
            ->setOnClick('areYouSure')
            ->setDisplayWhen('canDelete')
            ->setRouteParams(['period' => 'id']);
        $row->addAction($action);

        $this->setRow($row);
        return $this;
    }
}
