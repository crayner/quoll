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
 * Date: 2/01/2020
 * Time: 14:38
 */
namespace App\Modules\Department\Pagination;

use App\Manager\Hidden\PaginationAction;
use App\Manager\Hidden\PaginationColumn;
use App\Manager\Hidden\PaginationRow;
use App\Manager\PaginationInterface;
use App\Manager\AbstractPagination;
use App\Util\TranslationHelper;

/**
 * Class DepartmentStaffPagination
 * @package Kookaburra\Departments\Pagination
 * @author Craig Rayner <craig@craigrayner.com>
 */
class DepartmentStaffPagination extends AbstractPagination
{
    /**
     * execute
     * @return $this|PaginationInterface
     * 4/06/2020 16:15
     */
    public function execute(): PaginationInterface
    {
        TranslationHelper::setDomain('Department');
        $row = $this->getRow();

        $column = new PaginationColumn();
        $column->setLabel('Name')
            ->setContentKey('name')
            ->setSort(true)
            ->setClass('column relative pr-4 cursor-pointer widthAuto')
        ;
        $row->addColumn($column);

        $column = new PaginationColumn();
        $column->setLabel('Role')
            ->setContentKey('role')
            ->setSort(true)
            ->setClass('column relative pr-4 cursor-pointer widthAuto')
        ;
        $row->addColumn($column);

        $column = new PaginationColumn();
        $row->addColumn($column->setLabel('Head Teacher')
            ->setContentKey('head_teacher')
            ->setSort()
            ->setClass('column relative pr-4 cursor-pointer widthAuto text-centre'));

        $action = new PaginationAction();
        $action->setTitle('Change Role')
            ->setAClass('p-3 sm:p-0')
            ->setColumnClass('column p-2 sm:p-3')
            ->setSpanClass('fas fa-edit fa-fw fa-1-5x text-gray-800 hover:text-indigo-500')
            ->setRoute(['url' => 'department_staff_edit_popup', 'target' => 'Department_Staff', 'options' => 'width=650,height=350'])
            ->setRouteParams(['staff' => 'id', 'department' => 'departmentId']);
        $row->addAction($action);

        $action = new PaginationAction();
        $action->setTitle('Delete')
            ->setAClass('p-3 sm:p-0')
            ->setColumnClass('column p-2 sm:p-3')
            ->setSpanClass('far fa-trash-alt fa-fw fa-1-5x text-gray-800 hover:text-red-500')
            ->setRoute('department_staff_delete')
            ->setRouteParams(['staff' => 'id']);
        $row->addAction($action);

        $this->setRow($row);
        return $this;
    }

}