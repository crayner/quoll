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
 * Date: 3/01/2020
 * Time: 12:02
 */
namespace App\Modules\School\Pagination;

use App\Manager\AbstractPaginationManager;
use App\Manager\Hidden\PaginationAction;
use App\Manager\Hidden\PaginationColumn;
use App\Manager\Hidden\PaginationRow;
use App\Manager\PaginationInterface;
use App\Util\TranslationHelper;

/**
 * Class YearGroupPagination
 * @package App\Modules\School\Pagination
 * @author Craig Rayner <craig@craigrayner.com>
 */
class YearGroupPagination extends AbstractPaginationManager
{
    public function execute(): PaginationInterface
    {
        TranslationHelper::setDomain('School');
        $row = new PaginationRow();

        $column = new PaginationColumn();
        $column->setLabel('Name')
            ->setContentKey('name')
            ->setSort()
            ->setClass('column relative pr-4 cursor-pointer widthAuto text-centre')
        ;
        $row->addColumn($column);

        $column = new PaginationColumn();
        $column->setLabel('Abbreviation')
            ->setContentKey('abbr')
            ->setSort()
            ->setClass('column relative pr-4 cursor-pointer widthAuto text-centre')
        ;
        $row->addColumn($column);

        $column = new PaginationColumn();
        $column->setLabel('Head of Year')
            ->setContentKey('head')
            ->setClass('column relative pr-4 cursor-pointer widthAuto text-centre')
        ;
        $row->addColumn($column);

        $action = new PaginationAction();
        $action->setTitle('Edit')
            ->setAClass('thickbox p-3 sm:p-0')
            ->setColumnClass('column p-2 sm:p-3')
            ->setSpanClass('fas fa-edit fa-fw fa-1-5x text-gray-800 hover:text-indigo-500')
            ->setRoute('year_group_edit')
            ->setRouteParams(['year' => 'id']);
        $row->addAction($action);

        $action = new PaginationAction();
        $action->setTitle('Delete')
            ->setAClass('thickbox p-3 sm:p-0')
            ->setColumnClass('column p-2 sm:p-3')
            ->setSpanClass('far fa-trash-alt fa-fw fa-1-5x text-gray-800 hover:text-red-500')
            ->setRoute('year_group_delete')
            ->setDisplayWhen('canDelete')
            ->setRouteParams(['year' => 'id']);
        $row->addAction($action);
        $this->setSortList(true)->setRow($row);

        return $this;
    }
}