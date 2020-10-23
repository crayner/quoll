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
 * Date: 21/12/2019
 * Time: 10:46
 */
namespace App\Modules\School\Pagination;

use App\Manager\AbstractPagination;
use App\Manager\Hidden\PaginationAction;
use App\Manager\Hidden\PaginationColumn;
use App\Manager\Hidden\PaginationRow;
use App\Manager\PaginationInterface;
use App\Util\TranslationHelper;

/**
 * Class AcademicYearPagination
 * @package App\Modules\School\Pagination
 * @author Craig Rayner <craig@craigrayner.com>
 */
class AcademicYearPagination extends AbstractPagination
{
    public function execute(): PaginationInterface
    {
        TranslationHelper::setDomain('School');
        $row = new PaginationRow();

        $column = new PaginationColumn();
        $column->setLabel('Name')
            ->setContentKey('name')
            ->setSort(true)
            ->setClass('p-2 sm:p-3')
        ;
        $row->addColumn($column);

        $column = new PaginationColumn();
        $column->setLabel('Dates')
            ->setContentKey(['dates'])
            ->setClass('p-2 sm:p-3 hidden sm:table-cell');
        $row->addColumn($column);

        $column = new PaginationColumn();
        $column->setLabel('Status')
            ->setContentKey(['status'])
            ->setClass('p-2 sm:p-3 hidden md:table-cell');
        $row->addColumn($column);

        $action = new PaginationAction();
        $action->setTitle('Edit')
            ->setAClass('p-3 sm:p-0')
            ->setColumnClass('column p-2 sm:p-3')
            ->setSpanClass('fas fa-edit fa-fw fa-1-5x text-gray-800 hover:text-indigo-500')
            ->setRoute('academic_year_edit')
            ->setRouteParams(['year' => 'id']);
        $row->addAction($action);

        $action = new PaginationAction();
        $action->setTitle('Delete')
            ->setAClass('p-3 sm:p-0')
            ->setColumnClass('column p-2 sm:p-3')
            ->setSpanClass('far fa-trash-alt fa-fw fa-1-5x text-gray-800 hover:text-red-500')
            ->setRoute('academic_year_delete')
            ->setDisplayWhen('canDelete')
            ->setOnClick('areYouSure')
            ->setRouteParams(['year' => 'id']);
        $row->addAction($action);

        $action = new PaginationAction();
        $action->setTitle('Display')
            ->setAClass('p-3 sm:p-0')
            ->setColumnClass('column p-2 sm:p-3')
            ->setSpanClass('far fa-calendar-alt fa-fw fa-1-5x text-gray-800 hover:text-indigo-500')
            ->setRoute(['url' => 'academic_year_display_popup_raw', 'target' => 'Calendar_Display', 'options' => 'width=1400,height=800'])
            ->setOnClick('loadNewPage')
            ->setOptions('Calender Display')
            ->setRouteParams(['year' => 'id']);
        $row->addAction($action);

        $this->setRow($row);
        return $this;
    }
}