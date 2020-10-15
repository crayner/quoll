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
 * Date: 30/06/2020
 * Time: 08:48
 */
namespace App\Modules\Security\Pagination;

use App\Manager\AbstractPaginationManager;
use App\Manager\Hidden\PaginationAction;
use App\Manager\Hidden\PaginationColumn;
use App\Manager\Hidden\PaginationRow;
use App\Manager\PaginationInterface;
use App\Util\TranslationHelper;

/**
 * Class ActionPermissionPagination
 * @package App\Modules\Security\Pagination
 * @author Craig Rayner <craig@craigrayner.com>
 */
class ActionPermissionPagination extends AbstractPaginationManager
{
    /**
     * execute
     * @return PaginationInterface
     * 17/06/2020 12:26
     */
    public function execute(): PaginationInterface
    {
        TranslationHelper::setDomain('Security');
        $row = new PaginationRow();

        $column = new PaginationColumn();
        $column->setLabel('Action')
            ->setContentKey(['name'])
            ->setSearch()
            ->setSort()
            ->setClass('column relative pr-4 cursor-pointer widthAuto');
        $row->addColumn($column);

        $column = new PaginationColumn();
        $column->setLabel('Restriction')
            ->setContentKey(['restriction'])
            ->setClass('column relative pr-4 cursor-pointer widthAuto');
        $row->addColumn($column);

        $column = new PaginationColumn();
        $column->setLabel('Description')
            ->setContentKey('description')
            ->setSearch()
            ->setSort()
            ->setClass('column relative pr-4 cursor-pointer widthAuto');
        $row->addColumn($column);

        $column = new PaginationColumn();
        $column->setLabel('Category')
            ->setContentKey('category')
            ->setSearch()
            ->setSort()
            ->setClass('column relative pr-4 cursor-pointer widthAuto');
        $row->addColumn($column);

        $column = new PaginationColumn();
        $column->setLabel('Assigned Roles')
            ->setContentKey('roles')
            ->setSearch()
            ->setClass('column relative pr-4 cursor-pointer widthAuto');
        $row->addColumn($column);

        $action = new PaginationAction();
        $action->setTitle('Edit')
            ->setAClass('p-3 sm:p-0')
            ->setColumnClass('column p-2 sm:p-3')
            ->setSpanClass('fas fa-edit fa-fw fa-1-5x text-gray-800 hover:text-indigo-500')
            ->setRoute('action_permission_edit')
            ->setRouteParams(['item' => 'id']);
        $row->addAction($action);

        $this->setRow($row);

        return $this;
    }
}
