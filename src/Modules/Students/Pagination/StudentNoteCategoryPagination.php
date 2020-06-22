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
 * Date: 2/12/2019
 * Time: 17:19
 */

namespace App\Modules\Students\Pagination;

use App\Manager\Hidden\PaginationAction;
use App\Manager\Hidden\PaginationColumn;
use App\Manager\Hidden\PaginationRow;
use App\Manager\PaginationInterface;
use App\Manager\AbstractPaginationManager;
use App\Util\TranslationHelper;

/**
 * Class StudentNoteCategoryPagination
 * @package App\Modules\Students\Pagination
 */
class StudentNoteCategoryPagination extends AbstractPaginationManager
{
    /**
     * execute
     * @return PaginationInterface
     */
    public function execute(): PaginationInterface
    {
        TranslationHelper::setDomain('Students');
        $row = new PaginationRow();

        $column = new PaginationColumn();
        $column->setLabel('Name')
            ->setContentKey('name')
            ->setClass('column relative pr-4 cursor-pointer w-3/5')
            ->setSort(true)
        ;
        $row->addColumn($column);

        $column = new PaginationColumn();
        $column->setLabel('Active')
            ->setContentKey('active')
            ->setContentType('yesno')
            ->setClass('column relative pr-4 cursor-pointer w-1/5 text-center')
            ->setSort(true)
        ;
        $row->addColumn($column);

        $action = new PaginationAction();
        $action->setTitle('Edit')
            ->setAClass('thickbox p-3 sm:p-0')
            ->setColumnClass('column p-2 sm:p-3')
            ->setSpanClass('fas fa-edit fa-fw fa-1-5x text-gray-800')
            ->setRoute('student_note_category_edit')
            ->setRouteParams(['category' => 'id']);
        $row->addAction($action);

        $action = new PaginationAction();
        $action->setTitle('Delete')
            ->setAClass('thickbox p-3 sm:p-0')
            ->setColumnClass('column p-2 sm:p-3')
            ->setSpanClass('far fa-trash-alt fa-fw fa-1-5x text-gray-800')
            ->setRoute('student_note_category_delete')
            ->setOnClick('areYouSure')
            ->setDisplayWhen('canDelete')
            ->setRouteParams(['category' => 'id']);
        $row->addAction($action);

        $this->setRow($row);
        return $this;
    }
}