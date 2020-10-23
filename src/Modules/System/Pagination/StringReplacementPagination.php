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
 * Date: 14/09/2019
 * Time: 11:43
 */

namespace App\Modules\System\Pagination;

use App\Manager\AbstractPagination;
use App\Manager\Hidden\PaginationAction;
use App\Manager\Hidden\PaginationColumn;
use App\Manager\Hidden\PaginationRow;
use App\Manager\PaginationInterface;
use App\Util\TranslationHelper;

/**
 * Class StringReplacementPagination
 * @package App\Modules\System\Pagination
 */
class StringReplacementPagination extends AbstractPagination
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
        $column->setLabel('Original String')
            ->setContentKey('original')
            ->setSort(true)
            ->setSearch(true)
            ->setClass('column relative pr-4 cursor-pointer widthAuto');
        $row->addColumn($column);

        $column = new PaginationColumn();
        $column->setLabel('Replacement String')
            ->setContentKey('replacement')
            ->setSort(true)
            ->setSearch(true)
            ->setClass('column relative pr-4 cursor-pointer widthAuto');
        $row->addColumn($column);

        $column = new PaginationColumn();
        $column->setLabel('Mode')
            ->setContentKey('mode')
            ->setClass('column hidden md:table-cell relative pr-4 cursor-pointer widthAuto');
        $row->addColumn($column);

        $column = new PaginationColumn();
        $column->setLabel('Case Sensitive')
            ->setContentKey('caseSensitive')
            ->setClass('column hidden md:table-cell relative pr-4 cursor-pointer widthAuto');
        $row->addColumn($column);

        $column = new PaginationColumn();
        $column->setLabel('Priority')
            ->setContentKey('priority')
            ->setSort(true)
            ->setClass('column hidden md:table-cell relative pr-4 cursor-pointer widthAuto');
        $row->addColumn($column);

        $action = new PaginationAction();
        $action->setTitle('Edit')
            ->setAClass('p-3 sm:p-0')
            ->setSpanClass('far fa-edit fa-fw fa-1-5x text-gray-800 hover:text-indigo-500')
            ->setRoute('string_edit')
            ->setRouteParams(['stringReplacement' => 'id']);
        $row->addAction($action);

        $action = new PaginationAction();
        $action->setTitle('Delete')
            ->setAClass('p-3 sm:p-0')
            ->setColumnClass('p-2 sm:p-3')
            ->setSpanClass('fas fa-trash-alt fa-fw fa-1-5x text-gray-800 hover:text-red-500')
            ->setRoute('string_delete')
            ->setRouteParams(['stringReplacement' => 'id']);
        $row->addAction($action);

        $this->setRow($row);
        return $this;
    }
}