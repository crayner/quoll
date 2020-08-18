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
 * Time: 15:29
 */

namespace App\Modules\People\Pagination;

use App\Manager\AbstractPaginationManager;
use App\Manager\Hidden\PaginationAction;
use App\Manager\Hidden\PaginationColumn;
use App\Manager\Hidden\PaginationRow;
use App\Manager\PaginationInterface;
use App\Util\TranslationHelper;

/**
 * Class FamilyCareGiversPagination
 * @package App\Modules\People\Pagination
 * @author Craig Rayner <craig@craigrayner.com>
 */
class FamilyCareGiversPagination extends AbstractPaginationManager
{
    public function execute(): PaginationInterface
    {
        TranslationHelper::setDomain('People');
        $row = new PaginationRow();

        $column = new PaginationColumn();
        $column->setLabel('Name')
            ->setContentKey(['fullName'])
            ->setContentType('link')
            ->setOptions(['route' => 'person_edit', 'route_options' => ['person' => 'person_id'], 'title' => TranslationHelper::translate('Edit Personal Details')])
            ->setClass('column relative pr-4 cursor-pointer');
        $row->addColumn($column);

        $column = new PaginationColumn();
        $column->setLabel('Status')
            ->setContentKey(['status'])
            ->setClass('column relative pr-4 cursor-pointer widthAuto');
        $row->addColumn($column);

        $column = new PaginationColumn();
        $column->setLabel('Comment')
            ->setContentKey(['comment'])
            ->setClass('column relative pr-4 cursor-pointer widthAuto');
        $row->addColumn($column);

        $column = new PaginationColumn();
        $column->setLabel('Data Access')
            ->setContentKey(['childDataAccess'])
            ->setClass('column relative pr-4 cursor-pointer maxWidth50 text-center');
        $row->addColumn($column);

        $column = new PaginationColumn();
        $column->setLabel('Contact Priority')
            ->setContentKey(['contactPriority'])
            ->setClass('column relative pr-4 cursor-pointer maxWidth50 text-center');
        $row->addColumn($column);

        $column = new PaginationColumn();
        $column->setLabel('Contact by Phone')
            ->setContentKey(['phone'])
            ->setClass('column relative pr-4 cursor-pointer maxWidth50 text-center');
        $row->addColumn($column);

        $column = new PaginationColumn();
        $column->setLabel('Contact by SMS')
            ->setContentKey(['sms'])
            ->setClass('column relative pr-4 cursor-pointer maxWidth50 text-center');
        $row->addColumn($column);

        $column = new PaginationColumn();
        $column->setLabel('Contact by Email')
            ->setContentKey(['email'])
            ->setClass('column relative pr-4 cursor-pointer maxWidth50 text-center');
        $row->addColumn($column);

        $column = new PaginationColumn();
        $column->setLabel('Contact by Mail')
            ->setContentKey(['mail'])
            ->setClass('column relative pr-4 cursor-pointer maxWidth50 text-center');
        $row->addColumn($column);

        $action = new PaginationAction();
        $action->setTitle('Edit Care Givers in Family')
            ->setAClass('thickbox p-3 sm:p-0')
            ->setColumnClass('column p-2 sm:p-3')
            ->setSpanClass('fas fa-edit fa-fw fa-1-5x text-gray-800 hover:text-green-500')
            ->setRoute('family_care_giver_edit')
            ->setRouteParams(['family' => 'family_id', 'careGiver' => 'care_giver_id']);
        $row->addAction($action);

        $action = new PaginationAction();
        $action->setTitle('Remove Care Giver from Family')
            ->setAClass('thickbox p-3 sm:p-0')
            ->setColumnClass('column p-2 sm:p-3')
            ->setSpanClass('fas fa-eraser fa-fw fa-1-5x text-gray-800 hover:text-orange-500')
            ->setRoute('family_care_giver_remove')
            ->setOnClick('areYouSure')
            ->setRouteParams(['family' => 'family_id', 'careGiver' => 'care_giver_id']);
        $row->addAction($action);

        $action = new PaginationAction();
        $action->setTitle('Reset Password')
            ->setAClass('thickbox p-3 sm:p-0')
            ->setColumnClass('column p-2 sm:p-3')
            ->setSpanClass('fas fa-user-lock fa-fw fa-1-5x text-gray-800 hover:text-indigo-500')
            ->setRoute('person_reset_password')
            ->setRouteParams(['person' => 'person_id']);
        $row->addAction($action);

        $this->setRow($row);
        return $this;
    }
}