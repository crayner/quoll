<?php
/**
 * Created by PhpStorm.
 *
 * kookaburra
 * (c) 2020 Craig Rayner <craig@craigrayner.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * User: craig
 * Date: 3/01/2020
 * Time: 16:31
 */
namespace App\Modules\RollGroup\Pagination;

use App\Manager\Hidden\PaginationAction;
use App\Manager\Hidden\PaginationColumn;
use App\Manager\Hidden\PaginationRow;
use App\Manager\PaginationInterface;
use App\Manager\AbstractPaginationManager;
use App\Modules\People\Util\UserHelper;
use App\Modules\Security\Manager\SecurityUser;
use App\Util\TranslationHelper;

/**
 * Class RollGroupListPagination
 * @package App\Modules\RollGroup\Pagination
 * @author Craig Rayner <craig@craigrayner.com>
 */
class RollGroupListPagination  extends AbstractPaginationManager
{
    /**
     * execute
     * @return $this|PaginationInterface
     * 17/06/2020 12:26
     */
    public function execute(): PaginationInterface
    {
        TranslationHelper::setDomain('RollGroup');
        $row = new PaginationRow();

        $column = new PaginationColumn();
        $column->setLabel('Name')
            ->setContentKey(['name'])
            ->setClass('column relative pr-4 cursor-pointer widthAuto');
        $row->addColumn($column);

        $column = new PaginationColumn();
        $column->setLabel('Tutors')
            ->setContentKey('tutors')
            ->setClass('column relative pr-4 cursor-pointer widthAuto');
        $row->addColumn($column);

        $column = new PaginationColumn();
        $column->setLabel('Room')
            ->setContentKey('location')
            ->setClass('column relative pr-4 cursor-pointer widthAuto');
        $row->addColumn($column);

        if ($this->getCurrentUser()->getPerson()->isStaff()) {
            $column = new PaginationColumn();
            $column->setLabel('Students')
                ->setContentKey('students')
                ->setClass('column relative pr-4 cursor-pointer widthAuto text-center');
            $row->addColumn($column);
        }

        $column = new PaginationColumn();
        $column->setLabel('Website')
            ->setContentKey('website')
            ->setClass('column relative pr-4 cursor-pointer widthAuto');
        $row->addColumn($column);

        $action = new PaginationAction();
        $action->setTitle('View')
            ->setAClass('thickbox p-3 sm:p-0')
            ->setColumnClass('column p-2 sm:p-3')
            ->setSpanClass('fas fa-search-plus fa-fw fa-1-5x text-gray-800 hover:text-orange-500')
            ->setRoute('roll_group_detail')
            ->setRouteParams(['rollGroup' => 'id']);
        $row->addAction($action);

        $action = new PaginationAction();
        $action->setTitle('Edit')
            ->setAClass('thickbox p-3 sm:p-0')
            ->setColumnClass('column p-2 sm:p-3')
            ->setSpanClass('fas fa-edit fa-fw fa-1-5x text-gray-800 hover:text-indigo-500')
            ->setRoute('roll_group_edit')
            ->setRouteParams(['rollGroup' => 'id']);
        $row->addAction($action);

        $action = new PaginationAction();
        $action->setTitle('Delete')
            ->setAClass('thickbox p-3 sm:p-0')
            ->setColumnClass('column p-2 sm:p-3')
            ->setSpanClass('fas fa-eraser fa-fw fa-1-5x text-gray-800 hover:text-red-500')
            ->setRoute('roll_group_delete')
            ->setDisplayWhen('canDelete')
            ->setRouteParams(['rollGroup' => 'id']);
        $row->addAction($action);

        $this->setRow($row);

        return $this;
    }

    /**
     * @var SecurityUser
     */
    private $currentUser;

    /**
     * @return SecurityUser
     */
    public function getCurrentUser(): SecurityUser
    {
        return $this->currentUser;
    }

    /**
     * setCurrentUser
     * @param SecurityUser $currentUser
     * @return RollGroupListPagination
     */
    public function setCurrentUser(SecurityUser $currentUser): RollGroupListPagination
    {
        $this->currentUser = $currentUser;
        return $this;
    }
}
