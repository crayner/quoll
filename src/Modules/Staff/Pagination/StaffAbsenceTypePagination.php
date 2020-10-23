<?php
/**
 * Created by PhpStorm.
 *
 * Project: Kookaburra
 * Build: Quoll
 *
 * (c) 2019 Craig Rayner <craig@craigrayner.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * User: craig
 * Date: 3/12/2019
 * Time: 11:56
 */

namespace App\Modules\Staff\Pagination;

use App\Manager\Hidden\PaginationAction;
use App\Manager\Hidden\PaginationColumn;
use App\Manager\Hidden\PaginationRow;
use App\Manager\PaginationInterface;
use App\Manager\AbstractPagination;
use App\Util\TranslationHelper;

/**
 * Class StaffAbsenceTypePagination
 * @package Kookaburra\UserAdmin\Pagination
 */
class StaffAbsenceTypePagination extends AbstractPagination
{
    public function execute(): PaginationInterface
    {
        TranslationHelper::setDomain('Staff');
        $row = new PaginationRow();

        $column = new PaginationColumn();
        $column->setLabel('Abbreviation')
            ->setContentKey('abbreviation')
            ->setSort()
            ->setClass('column relative pr-4 cursor-pointer widthAuto')
        ;
        $row->addColumn($column);

        $column = new PaginationColumn();
        $column->setLabel('Name')
            ->setContentKey('name')
            ->setClass('column relative pr-4 cursor-pointer widthAuto')
            ->setSort()
        ;
        $row->addColumn($column);

        $column = new PaginationColumn();
        $column->setLabel('Reasons')
            ->setContentKey('reasons')
            ->setClass('column relative pr-4 cursor-pointer widthAuto')
        ;
        $row->addColumn($column);

        $column = new PaginationColumn();
        $column->setLabel('Requires Approval')
            ->setContentKey('requiresApproval')
            ->setClass('column relative pr-4 cursor-pointer widthAuto text-center')
        ;
        $row->addColumn($column);

        $column = new PaginationColumn();
        $column->setLabel('Active')
            ->setContentKey('active')
            ->setClass('column relative pr-4 cursor-pointer widthAuto text-center')
        ;
        $row->addColumn($column);

        $action = new PaginationAction();
        $action->setTitle('Edit')
            ->setAClass('p-3 sm:p-0')
            ->setColumnClass('column p-2 sm:p-3')
            ->setSpanClass('fas fa-edit fa-fw fa-1-5x text-gray-800 hover:text-indigo-500')
            ->setRoute('staff_absence_type_edit')
            ->setRouteParams(['absenceType' => 'id']);
        $row->addAction($action);

        $action = new PaginationAction();
        $action->setTitle('Delete')
            ->setAClass('p-3 sm:p-0')
            ->setColumnClass('column p-2 sm:p-3')
            ->setSpanClass('far fa-trash-alt fa-fw fa-1-5x text-gray-800 hover:text-red-500')
            ->setRoute('staff_absence_type_delete')
            ->setOnClick('areYouSure')
            ->setRouteParams(['absenceType' => 'id']);
        $row->addAction($action);

        $this->setRow($row);
        return $this;
    }
}