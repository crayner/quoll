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
 * Date: 29/06/2020
 * Time: 10:11
 */
namespace App\Modules\Security\Pagination;

use App\Manager\AbstractPaginationManager;
use App\Manager\Hidden\PaginationAction;
use App\Manager\Hidden\PaginationColumn;
use App\Manager\Hidden\PaginationRow;
use App\Manager\PaginationInterface;
use App\Util\TranslationHelper;

/**
 * Class SecurityRolePagination
 * @package App\Modules\Security\Pagination
 * @author Craig Rayner <craig@craigrayner.com>
 */
class SecurityRolePagination extends AbstractPaginationManager
{
    /**
     * execute
     * @return $this|PaginationInterface
     * 17/06/2020 12:26
     */
    public function execute(): PaginationInterface
    {
        TranslationHelper::setDomain('Security');
        $row = new PaginationRow();

        $column = new PaginationColumn();
        $column->setLabel('Role')
            ->setContentKey(['role'])
            ->setSort()
            ->setClass('column relative pr-4 cursor-pointer widthAuto');
        $row->addColumn($column);

        $column = new PaginationColumn();
        $column->setLabel('Label')
            ->setContentKey('label')
            ->setSort()
            ->setClass('column relative pr-4 cursor-pointer widthAuto');
        $row->addColumn($column);

        $column = new PaginationColumn();
        $column->setLabel('Category')
            ->setContentKey('category')
            ->setSort()
            ->setClass('column relative pr-4 cursor-pointer widthAuto');
        $row->addColumn($column);

        $column = new PaginationColumn();
        $column->setLabel('Reachable Roles')
            ->setContentKey('children')
            ->setClass('column relative pr-4 cursor-pointer widthAuto');
        $row->addColumn($column);

        $column = new PaginationColumn();
        $column->setLabel('Allow Login')
            ->setContentKey('login')
            ->setSort()
            ->setClass('column relative pr-4 cursor-pointer widthAuto');
        $row->addColumn($column);

        $column = new PaginationColumn();
        $column->setLabel('Future Years')
            ->setContentKey('future_years')
            ->setClass('column relative pr-4 cursor-pointer widthAuto');
        $row->addColumn($column);

        $column = new PaginationColumn();
        $column->setLabel('Past Years')
            ->setContentKey('past_years')
            ->setClass('column relative pr-4 cursor-pointer widthAuto');
        $row->addColumn($column);

        $action = new PaginationAction();
        $action->setTitle('Edit')
            ->setAClass('thickbox p-3 sm:p-0')
            ->setColumnClass('column p-2 sm:p-3')
            ->setSpanClass('fas fa-edit fa-fw fa-1-5x text-gray-800 hover:text-indigo-500')
            ->setRoute('security_role_edit')
            ->setRouteParams(['role' => 'id']);
        $row->addAction($action);

        $action = new PaginationAction();
        $action->setTitle('Delete')
            ->setAClass('thickbox p-3 sm:p-0')
            ->setColumnClass('column p-2 sm:p-3')
            ->setSpanClass('fas fa-trash-alt fa-fw fa-1-5x text-gray-800 hover:text-red-500')
            ->setRoute('security_role_delete')
            ->setDisplayWhen('canDelete')
            ->setRouteParams(['role' => 'id']);
        $row->addAction($action);

        $this->setRow($row);

        return $this;
    }
}
