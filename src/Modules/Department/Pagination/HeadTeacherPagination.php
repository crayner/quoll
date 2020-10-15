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
 * Date: 3/10/2020
 * Time: 14:27
 */
namespace App\Modules\Department\Pagination;

use App\Manager\AbstractPaginationManager;
use App\Manager\Hidden\PaginationAction;
use App\Manager\Hidden\PaginationColumn;
use App\Manager\Hidden\PaginationRow;
use App\Manager\PaginationInterface;
use App\Util\TranslationHelper;

/**
 * Class HeadTeacherPagination
 * @package App\Modules\Department\Pagination
 * @author Craig Rayner <craig@craigrayner.com>
 */
class HeadTeacherPagination extends AbstractPaginationManager
{
    /**
     * execute
     *
     * 3/10/2020 14:29
     * @return $this|PaginationInterface
     */
    public function execute(): PaginationInterface
    {
        TranslationHelper::setDomain('Department');
        $row = new PaginationRow();

        $column = new PaginationColumn();
        $column->setLabel('Name')
            ->setContentKey('name')
            ->setSort()
            ->setClass('column relative pr-4 cursor-pointer widthAuto')
        ;
        $row->addColumn($column);

        $column = new PaginationColumn();
        $column->setLabel('Title')
            ->setContentKey('title')
            ->setSort()
            ->setClass('column relative pr-4 cursor-pointer widthAuto')
        ;
        $row->addColumn($column);

        $column = new PaginationColumn();
        $column->setLabel('Class Count')
            ->setContentKey('classes')
            ->setClass('column relative pr-4 cursor-pointer w-1/6 text-right')
        ;
        $row->addColumn($column);

        $action = new PaginationAction();
        $action->setTitle('Edit')
            ->setAClass('p-3 sm:p-0')
            ->setColumnClass('column p-2 sm:p-3')
            ->setSpanClass('fas fa-edit fa-fw fa-1-5x text-gray-800 hover:text-indigo-500')
            ->setRoute('head_teacher_edit')
            ->setRouteParams(['headTeacher' => 'id']);
        $row->addAction($action);

        $action = new PaginationAction();
        $action->setTitle('Delete')
            ->setAClass('p-3 sm:p-0')
            ->setColumnClass('column p-2 sm:p-3')
            ->setSpanClass('far fa-trash-alt fa-fw fa-1-5x text-gray-800 hover:text-red-500')
            ->setRoute('head_teacher_delete')
            ->setRouteParams(['headTeacher' => 'id'])
            ->setOnClick('areYouSure');
        $row->addAction($action);

        return $this->setRow($row);
    }
}
