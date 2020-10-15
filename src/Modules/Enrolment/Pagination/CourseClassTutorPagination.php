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
 * Date: 19/09/2020
 * Time: 10:09
 */
namespace App\Modules\Enrolment\Pagination;

use App\Manager\AbstractPaginationManager;
use App\Manager\Hidden\PaginationAction;
use App\Manager\Hidden\PaginationColumn;
use App\Manager\Hidden\PaginationRow;
use App\Manager\PaginationInterface;
use App\Util\TranslationHelper;

/**
 * Class CourseClassTutorPagination
 * @package App\Modules\Enrolment\Pagination
 * @author Craig Rayner <craig@craigrayner.com>
 */
class CourseClassTutorPagination extends AbstractPaginationManager
{
    public function execute(): PaginationInterface
    {
        TranslationHelper::setDomain('Enrolment');
        $row = new PaginationRow();

        $column = new PaginationColumn();
        $column->setLabel('Tutor')
            ->setSearch()
            ->setContentKey('tutor')
            ->setClass('column relative pr-4 cursor-pointer widthAuto')
        ;
        $row->addColumn($column);

        $column = new PaginationColumn();
        $column->setLabel('Role')
            ->setContentKey('type')
            ->setSearch()
            ->setSort()
            ->setClass('column relative pr-4 cursor-pointer widthAuto')
        ;
        $row->addColumn($column);

        $column = new PaginationColumn();
        $column->setLabel('Sort Order')
            ->setContentKey('sortOrder')
            ->setClass('column relative pr-4 cursor-pointer width-1/8 text-centre')
        ;
        $row->addColumn($column);

        $action = new PaginationAction();
        $row->addAction($action->setTitle('Edit')
            ->setAClass('p-3 sm:p-0')
            ->setColumnClass('column p-2 sm:p-3')
            ->setSpanClass('fas fa-edit fa-fw fa-1-5x text-gray-800 hover:text-indigo-500')
            ->setRoute('course_class_tutor_edit')
            ->setRouteParams(['tutor' => 'id', 'class' => 'course_class_id'])
        );

        $action = new PaginationAction();
        $row->addAction($action->setTitle('Delete')
            ->setAClass('p-3 sm:p-0')
            ->setColumnClass('column p-2 sm:p-3')
            ->setSpanClass('fas fa-eraser fa-fw fa-1-5x text-gray-800 hover:text-red-500')
            ->setRoute('course_class_tutor_remove')
            ->setDisplayWhen('canDelete')
            ->setRouteParams(['tutor' => 'id', 'class' => 'course_class_id'])
        );

        $this->setRow($row)
            ->setDraggableRoute('course_class_tutor_sort')
        ;
        return $this;
    }
}
