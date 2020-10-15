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
 * Date: 6/12/2019
 * Time: 10:06
 */

namespace App\Modules\People\Pagination;

use App\Manager\Hidden\PaginationAction;
use App\Manager\Hidden\PaginationColumn;
use App\Manager\Hidden\PaginationRow;
use App\Manager\PaginationInterface;
use App\Manager\AbstractPaginationManager;
use App\Util\TranslationHelper;

/**
 * Class FamilyChildrenPagination
 * @package App\Modules\People\Pagination
 */
class FamilyStudentsPagination extends AbstractPaginationManager
{
    public function execute(): PaginationInterface
    {
        TranslationHelper::setDomain('People');
        $row = new PaginationRow();

        $column = new PaginationColumn();
        $column->setLabel('Photo')
            ->setContentKey('photo')
            ->setContentType('image')
            ->setClass('column relative pr-4 cursor-pointer widthAuto text-center')
            ->setOptions(['class' => 'max75 user'])
        ;
        $row->addColumn($column);

        $column = new PaginationColumn();
        $column->setLabel('Name')
            ->setContentKey(['fullName'])
            ->setContentType('link')
            ->setOptions(['route' => 'person_edit', 'route_options' => ['person' => 'person_id'], 'title' => TranslationHelper::translate('Edit Personal Details')])
            ->setSort(true)
            ->setClass('column relative pr-4 cursor-pointer widthAuto');
        $row->addColumn($column);

        $column = new PaginationColumn();
        $column->setLabel('Status')
            ->setContentKey(['status'])
            ->setSort(true)
            ->setClass('column relative pr-4 cursor-pointer widthAuto');
        $row->addColumn($column);

        $column = new PaginationColumn();
        $column->setLabel('Roll Group')
            ->setContentKey(['roll'])
            ->setSort(true)
            ->setClass('column relative pr-4 cursor-pointer widthAuto');
        $row->addColumn($column);

        $column = new PaginationColumn();
        $column->setLabel('Comment')
            ->setContentKey(['comment'])
            ->setSort(true)
            ->setClass('column relative pr-4 cursor-pointer widthAuto');
        $row->addColumn($column);

        $action = new PaginationAction();
        $action->setTitle('Edit Student in Family')
            ->setAClass('p-3 sm:p-0')
            ->setColumnClass('column p-2 sm:p-3')
            ->setSpanClass('fas fa-edit fa-fw fa-1-5x text-gray-800')
            ->setRoute('family_student_edit')
            ->setRouteParams(['family' => 'family_id', 'student' => 'student_id']);
        $row->addAction($action);

        $action = new PaginationAction();
        $action->setTitle('Remove Student from Family')
            ->setAClass('p-3 sm:p-0')
            ->setColumnClass('column p-2 sm:p-3')
            ->setSpanClass('fas fa-eraser fa-fw fa-1-5x text-gray-800')
            ->setRoute('family_student_remove')
            ->setOnClick('areYouSure')
            ->setRouteParams(['family' => 'family_id', 'student' => 'student_id']);
        $row->addAction($action);

        $action = new PaginationAction();
        $action->setTitle('Reset Password')
            ->setAClass('p-3 sm:p-0')
            ->setColumnClass('column p-2 sm:p-3')
            ->setSpanClass('fas fa-user-lock fa-fw fa-1-5x text-gray-800')
            ->setRoute('person_reset_password')
            ->setRouteParams(['person' => 'person_id']);
        $row->addAction($action);

        $this->setRow($row);
        return $this;

    }

}