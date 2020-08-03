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
 * Date: 10/01/2020
 * Time: 08:26
 */
namespace App\Modules\Assess\Pagination;

use App\Manager\AbstractPaginationManager;
use App\Manager\Hidden\PaginationAction;
use App\Manager\Hidden\PaginationColumn;
use App\Manager\Hidden\PaginationFilter;
use App\Manager\Hidden\PaginationRow;
use App\Manager\PaginationInterface;
use App\Util\TranslationHelper;

/**
 * Class ScalePagination
 * @package App\Modules\Assess\Pagination
 * @author Craig Rayner <craig@craigrayner.com>
 */
class ScalePagination extends AbstractPaginationManager
{
    /**
     * execute
     * @return PaginationInterface
     */
    public function execute(): PaginationInterface
    {
        TranslationHelper::setDomain('Assess');
        $row = new PaginationRow();

        $column = new PaginationColumn();
        $column->setLabel('Name')
            ->setHelp('Abbreviation')
            ->setSort(true)
            ->setContentKey(['name','abbr'])
            ->setClass('column relative pr-4 cursor-pointer widthAuto')
        ;
        $row->addColumn($column);

        $column = new PaginationColumn();
        $column->setLabel('Usage')
            ->setContentKey('usage')
            ->setClass('column relative pr-4 cursor-pointer widthAuto')
        ;
        $row->addColumn($column);

        $column = new PaginationColumn();
        $column->setLabel('Active')
            ->setContentKey('active')
            ->setClass('column relative pr-4 cursor-pointer widthAuto');
        $row->addColumn($column);

        $column = new PaginationColumn();
        $column->setLabel('Numeric')
            ->setContentKey('numeric')
            ->setClass('column relative pr-4 cursor-pointer widthAuto');
        $row->addColumn($column);

        $action = new PaginationAction();
        $action->setTitle('Edit')
            ->setAClass('thickbox p-3 sm:p-0')
            ->setColumnClass('column p-2 sm:p-3')
            ->setSpanClass('fas fa-edit fa-fw fa-1-5x text-gray-800 hover:text-indigo-500')
            ->setRoute('scale_edit')
            ->setRouteParams(['scale' => 'id']);
        $row->addAction($action);

        $action = new PaginationAction();
        $action->setTitle('Delete')
            ->setAClass('thickbox p-3 sm:p-0')
            ->setColumnClass('column p-2 sm:p-3')
            ->setSpanClass('far fa-trash-alt fa-fw fa-1-5x text-gray-800 hover:text-red-500')
            ->setRoute('scale_delete')
            ->setDisplayWhen('canDelete')
            ->setOnClick('areYouSure')
            ->setRouteParams(['scale' => 'id']);
        $row->addAction($action);

        $filter = new PaginationFilter();
        $filter->setName('Active: Yes')
            ->setContentKey('isActive')
            ->setGroup('Active')
            ->setExactMatch()
            ->setValue(true);
        $row->addFilter($filter);

        $filter = new PaginationFilter();
        $filter->setName('Active: No')
            ->setGroup('Active')
            ->setContentKey('isActive')
            ->setExactMatch()
            ->setValue(false);
        $row->addFilter($filter);

        $row->addHighlight(['className' => 'warning', 'columnKey' => 'isActive', 'columnValue' => false]);

        $this->setRow($row);
        return $this;
    }
}