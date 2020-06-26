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
 * Date: 26/03/2020
 * Time: 15:27
 */
namespace App\Modules\Comms\Pagination;

use App\Manager\AbstractPaginationManager;
use App\Manager\Hidden\PaginationAction;
use App\Manager\Hidden\PaginationColumn;
use App\Manager\Hidden\PaginationFilter;
use App\Manager\Hidden\PaginationRow;
use App\Manager\PaginationInterface;
use App\Util\TranslationHelper;

/**
 * Class NotificationPagination
 * @package App\Modules\System\Pagination
 */
class NotificationEventPagination extends AbstractPaginationManager
{
    /**
     * execute
     * @return PaginationInterface
     */
    public function execute(): PaginationInterface
    {
        TranslationHelper::setDomain('System');

        $row = new PaginationRow();
        $column = new PaginationColumn();
        $column->setLabel('Module')
            ->setContentKey('module')
            ->setSort(true)
            ->setSearch(true)
            ->setClass('column relative pr-4 cursor-pointer widthAuto');
        $row->addColumn($column);

        $column = new PaginationColumn();
        $column->setLabel('Name')
            ->setContentKey('name')
            ->setSort(true)
            ->setSearch(true)
            ->setClass('column relative pr-4 cursor-pointer widthAuto');
        $row->addColumn($column);

        $column = new PaginationColumn();
        $column->setLabel('Listening Count');
        $column->setContentKey('subscribers');
        $column->setSort(true);
        $column->setClass('column relative pr-4 cursor-pointer text-right widthAuto');
        $row->addColumn($column);

        $column = new PaginationColumn();
        $column->setLabel('Active');
        $column->setContentKey('active');
        $column->setSort(true);
        $column->setClass('column relative pr-4 cursor-pointer text-center widthAuto');
        $row->addColumn($column);

        $filter = new PaginationFilter();
        $filter->setName('Active: Yes')
            ->setGroup('Active')
            ->setContentKey('isActive')
            ->setValue(true);
        $row->addFilter($filter);

        $filter = new PaginationFilter();
        $filter->setName('Active: No')
            ->setGroup('Active')
            ->setContentKey('isActive')
            ->setValue(false);
        $row->addFilter($filter);

        $action = new PaginationAction();
        $action->setTitle('Edit')
            ->setAClass('thickbox p-3 sm:p-0')
            ->setColumnClass('p-2 sm:p-3')
            ->setSpanClass('fas fa-edit fa-fw fa-1-5x text-gray-800 hover:text-indigo-500')
            ->setRoute('notification_edit')
            ->setRouteParams(['event' => 'id']);
        $row->addAction($action);

        $this->setRow($row);
        return $this;
    }

}