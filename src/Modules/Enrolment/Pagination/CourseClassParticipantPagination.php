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
 * Date: 3/09/2020
 * Time: 11:57
 */
namespace App\Modules\Enrolment\Pagination;

use App\Manager\AbstractPaginationManager;
use App\Manager\Hidden\PaginationAction;
use App\Manager\Hidden\PaginationColumn;
use App\Manager\Hidden\PaginationRow;
use App\Manager\PaginationInterface;
use App\Util\TranslationHelper;

/**
 * Class CourseClassParticipantPagination
 * @package App\Modules\Enrolment\Pagination
 * @author Craig Rayner <craig@craigrayner.com>
 */
class CourseClassParticipantPagination extends AbstractPaginationManager
{
    /**
     * execute
     *
     * 3/09/2020 11:58
     * @return $this|PaginationInterface
     */
    public function execute(): PaginationInterface
    {
        TranslationHelper::setDomain('Enrolment');
        $row = new PaginationRow();

        $column = new PaginationColumn();
        $column->setLabel('Name')
            ->setContentKey('name')
            ->setClass('column relative pr-4 cursor-pointer widthAuto');
        $row->addColumn($column);

        $column = new PaginationColumn();
        $column->setLabel('Email')
            ->setContentKey('email')
            ->setClass('column relative pr-4 cursor-pointer widthAuto');
        $row->addColumn($column);

        $column = new PaginationColumn();
        $column->setLabel('Class Role')
            ->setContentKey('role')
            ->setClass('column relative pr-4 cursor-pointer widthAuto text-centre');
        $row->addColumn($column);

        $column = new PaginationColumn();
        $column->setLabel('Reportable')
            ->setContentKey('reportable')
            ->setClass('column relative pr-4 cursor-pointer widthAuto text-centre');
        $row->addColumn($column);

        $action = new PaginationAction();
        $row->addAction($action->setTitle('Edit')
            ->setAClass('thickbox p-3 sm:p-0')
            ->setColumnClass('column p-2 sm:p-3')
            ->setSpanClass('fas fa-edit fa-fw fa-1-5x text-gray-800 hover:text-indigo-500')
            ->setRoute('course_class_enrolment_edit')
            ->setRouteParams(['class' => 'course_class_id', 'person' => 'id'])
        );

        $action = new PaginationAction();
        $row->addAction($action->setTitle('Remove')
            ->setAClass('thickbox p-3 sm:p-0')
            ->setColumnClass('column p-2 sm:p-3')
            ->setSpanClass('fas fa-eraser fa-fw fa-1-5x text-gray-800 hover:text-red-500')
            ->setRoute('course_class_enrolment_delete')
            ->setOnClick('areYouSure')
            ->setRouteParams(['class' => 'course_class_id', 'person' => 'id'])
        );
        $this->setRow($row);

        return $this;
    }
}
