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
 * Date: 5/05/2020
 * Time: 13:28
 */
namespace App\Modules\People\Pagination;

use App\Manager\AbstractPagination;
use App\Manager\Hidden\PaginationAction;
use App\Manager\Hidden\PaginationColumn;
use App\Manager\Hidden\PaginationRow;
use App\Manager\PaginationInterface;
use App\Util\TranslationHelper;

/**
 * Class AddressPagination
 * @package App\Modules\People\Pagination
 */
class AddressPagination extends AbstractPagination
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
            ->setHelp('Flat / Unit Details')
            ->setContentKey(['streetNumber', 'flatUnitDetails'])
            ->setSort()
            ->setSearch()
            ->setClass('column relative pr-4 cursor-pointer widthAuto')
        ;
        $row->addColumn($column);

        $column = new PaginationColumn();
        $column->setLabel('Street Name')
            ->setContentKey('streetName')
            ->setSort()
            ->setSearch()
            ->setClass('column relative pr-4 cursor-pointer widthAuto')
        ;
        $row->addColumn($column);

        $column = new PaginationColumn();
        $column->setLabel('Property Name')
            ->setContentKey('propertyName')
            ->setSort()
            ->setSearch()
            ->setClass('column relative pr-4 cursor-pointer widthAuto')
        ;
        $row->addColumn($column);

        $column = new PaginationColumn();
        $column->setLabel('Locality')
            ->setContentKey('locality')
            ->setSort()
            ->setSearch()
            ->setClass('column relative pr-4 cursor-pointer widthAuto')
        ;
        $row->addColumn($column);

        $action = new PaginationAction();
        $action->setTitle('Edit')
            ->setAClass('p-3 sm:p-0')
            ->setColumnClass('column p-2 sm:p-3')
            ->setSpanClass('fas fa-edit fa-fw fa-1-5x text-gray-800 hover:text-indigo-500')
            ->setRoute(['url' => 'address_edit_popup', 'target' => 'Address_Details', 'options' => 'width=800,height=600'])
            ->setRouteParams(['address' => 'id']);
        $row->addAction($action);

        $action = new PaginationAction();
        $action->setTitle('Delete')
            ->setAClass('p-3 sm:p-0')
            ->setColumnClass('column p-2 sm:p-3')
            ->setSpanClass('far fa-trash-alt fa-fw fa-1-5x text-gray-800 hover:text-red-500')
            ->setRoute('address_delete')
            ->setOnClick('areYouSure')
            ->setDisplayWhen('canDelete')
            ->setRouteParams(['address' => 'id']);
        $row->addAction($action);

        $this->setRow($row);
        return $this;
    }
}
