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
 * Date: 17/05/2020
 * Time: 09:50
 */
namespace App\Modules\People\Pagination;

use App\Manager\AbstractPaginationManager;
use App\Manager\Hidden\PaginationAction;
use App\Manager\Hidden\PaginationColumn;
use App\Manager\Hidden\PaginationRow;
use App\Manager\PaginationInterface;
use App\Util\TranslationHelper;

/**
 * Class LocalityPagination
 * @package App\Modules\People\Pagination
 */
class LocalityPagination extends AbstractPaginationManager
{
    /**
     * execute
     * @return PaginationInterface
     */
    public function execute(): PaginationInterface
    {
        TranslationHelper::setDomain('People');
        $row = new PaginationRow();

        $column = new PaginationColumn();
        $column->setLabel('Street Number')
            ->setContentKey(['name'])
            ->setSort()
            ->setSearch()
            ->setClass('column relative pr-4 cursor-pointer widthAuto')
        ;
        $row->addColumn($column);

        $column = new PaginationColumn();
        $column->setLabel('Territory')
            ->setContentKey('territory')
            ->setSort()
            ->setSearch()
            ->setClass('column relative pr-4 cursor-pointer widthAuto')
        ;
        $row->addColumn($column);

        $column = new PaginationColumn();
        $column->setLabel('Post Code')
            ->setContentKey('postCode')
            ->setSort()
            ->setSearch()
            ->setClass('column relative pr-4 cursor-pointer widthAuto')
        ;
        $row->addColumn($column);

        $column = new PaginationColumn();
        $column->setLabel('Country')
            ->setContentKey('country')
            ->setSort()
            ->setSearch()
            ->setClass('column relative pr-4 cursor-pointer widthAuto')
        ;
        $row->addColumn($column);

        $action = new PaginationAction();
        $action->setTitle('Edit')
            ->setAClass('thickbox p-3 sm:p-0')
            ->setColumnClass('column p-2 sm:p-3')
            ->setSpanClass('fas fa-edit fa-fw fa-1-5x text-gray-800 hover:text-indigo-500')
            ->setRoute(['url' => 'locality_edit_popup', 'target' => 'Locality_Details', 'options' => 'width=800,height=450'])
            ->setRouteParams(['locality' => 'id']);
        $row->addAction($action);

        $action = new PaginationAction();
        $action->setTitle('Delete')
            ->setAClass('thickbox p-3 sm:p-0')
            ->setColumnClass('column p-2 sm:p-3')
            ->setSpanClass('far fa-trash-alt fa-fw fa-1-5x text-gray-800 hover:text-red-500')
            ->setRoute('locality_delete')
            ->setOnClick('areYouSure')
            ->setDisplayWhen('canDelete')
            ->setRouteParams(['locality' => 'id']);
        $row->addAction($action);

        $this->setRow($row);
        return $this;

    }
}
