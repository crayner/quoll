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
 * Date: 22/11/2019
 * Time: 12:16
 */

namespace App\Modules\People\Pagination;

use App\Manager\Hidden\PaginationAction;
use App\Manager\Hidden\PaginationColumn;
use App\Manager\Hidden\PaginationFilter;
use App\Manager\Hidden\PaginationRow;
use App\Manager\PaginationInterface;
use App\Manager\AbstractPaginationManager;
use App\Modules\People\Entity\Person;
use App\Util\TranslationHelper;
use Symfony\Component\Security\Core\Role\RoleHierarchyInterface;

/**
 * Class ManagePagination
 * @package App\Modules\People\Pagination
 */
class PeoplePagination extends AbstractPaginationManager
{
    public function execute(): PaginationInterface
    {
        TranslationHelper::setDomain('People');
        TranslationHelper::setTranslation('Search in', 'Preferred, surname, username, role, student ID, email, phone number, vehicle registration', [], 'People');
        foreach(Person::getStatusList() as $name) {
            TranslationHelper::setTranslation($name, $name, [], 'People');
        }
        foreach($this->getHierarchy()->getReachableRoleNames(['ROLE_SYSTEM_ADMIN']) as $name) {
            TranslationHelper::setTranslation($name, $name, [], 'Security');
        }
        $row = new PaginationRow();

        $column = new PaginationColumn();
        $column->setLabel('Photo')
            ->setContentKey('photo')
            ->setContentType('image')
            ->setDefaultValue(['/build/static/DefaultPerson.png'])
            ->setClass('column relative pr-4 cursor-pointer widthAuto text-centre')
            ->setOptions(['class' => 'max75 user'])
        ;
        $row->addColumn($column);

        $column = new PaginationColumn();
        $column->setLabel('Name')
            ->setContentKey(['fullName'])
            ->setSort(true)
            ->setSearch(true)
            ->setClass('column relative pr-4 cursor-pointer widthAuto');
        $row->addColumn($column);

        $column = new PaginationColumn();
        $column->setLabel('Role')
            ->setContentKey(['role'])
            ->setSort(false)
            ->setSearch(true)
            ->setTranslate()
            ->setClass('column relative pr-4 cursor-pointer widthAuto');
        $row->addColumn($column);

        $column = new PaginationColumn();
        $column->setLabel('Status')
            ->setContentKey(['status'])
            ->setSearch(true)
            ->setSort(false)
            ->setTranslate()
            ->setClass('column relative pr-4 cursor-pointer widthAuto');
        $row->addColumn($column);

        $column = new PaginationColumn();
        $column->setLabel('Family')
            ->setContentKey(['family'])
            ->setContentType('link')
            ->setSort(true)
            ->setOptions(['route' => 'family_edit', 'route_options' => ['family' => 'family_id']])
            ->setSearch(true)
            ->setClass('column hidden sm:table-cell relative pr-4 cursor-pointer widthAuto');
        $row->addColumn($column);

        $column = new PaginationColumn();
        $column->setLabel('Username')
            ->setContentKey(['username'])
            ->setSort(false)
            ->setSearch(true)
            ->setClass('column hidden sm:table-cell relative pr-4 cursor-pointer widthAuto');
        $row->addColumn($column);

        $column = new PaginationColumn();
        $column->setContentKey(['email'])
            ->setDataOnly(true)
            ->setSearch(true);
        $row->addColumn($column);

        $column = new PaginationColumn();
        $column->setContentKey(['studentID'])
            ->setDataOnly(true)
            ->setSearch(true);
        $row->addColumn($column);

        $column = new PaginationColumn();
        $column->setContentKey(['phone'])
            ->setDataOnly(true)
            ->setSearch(true);
        $row->addColumn($column);

        $column = new PaginationColumn();
        $column->setContentKey(['rego'])
            ->setDataOnly(true)
            ->setSearch(true);
        $row->addColumn($column);

        $column = new PaginationColumn();
        $column->setContentKey(['name'])
            ->setDataOnly(true)
            ->setSearch(true);
        $row->addColumn($column);

        $column = new PaginationColumn();
        $column->setContentKey(['id'])
            ->setDataOnly(true)
            ->setSearch(true);
        $row->addColumn($column);

        $action = new PaginationAction();
        $action->setTitle('Edit Person')
            ->setAClass('thickbox p-3 sm:p-0')
            ->setColumnClass('column p-2 sm:p-3')
            ->setSpanClass('fas fa-edit fa-fw fa-1-5x text-gray-800 hover:text-indigo-500')
            ->setRoute('person_edit')
            ->setRouteParams(['person' => 'id']);
        $row->addAction($action);

        $action = new PaginationAction();
        $action->setTitle('Impersonation')
            ->setAClass('thickbox p-3 sm:p-0')
            ->setColumnClass('column p-2 sm:p-3')
            ->setSpanClass('fas fa-people-arrows fa-fw fa-1-5x text-gray-800 hover:text-blue-500')
            ->setRoute('personal_page')
            ->setDisplayWhen('canLogin')
            ->setOnClick(false)
            ->setRouteParams(['_switch_user' => 'username']);
        $row->addAction($action);

        $action = new PaginationAction();
        $action->setTitle('Reset Password')
            ->setAClass('thickbox p-3 sm:p-0')
            ->setColumnClass('column p-2 sm:p-3')
            ->setSpanClass('fas fa-user-lock fa-fw fa-1-5x text-gray-800 hover:text-orange-500')
            ->setRoute('person_reset_password')
            ->setRouteParams(['person' => 'id']);
        $row->addAction($action);

        $action = new PaginationAction();
        $action->setTitle('Delete Person')
            ->setAClass('thickbox p-3 sm:p-0')
            ->setColumnClass('column p-2 sm:p-3')
            ->setSpanClass('far fa-trash-alt fa-fw fa-1-5x text-gray-800 hover:text-red-500')
            ->setRoute('person_delete')
            ->setDisplayWhen('canDelete')
            ->setOnClick('areYouSure')
            ->setRouteParams(['person' => 'id']);
        $row->addAction($action);

        $filter = new PaginationFilter();
        $filter->setName('Role: Student')
            ->setValue('Student')
            ->setGroup('Role')
            ->setContentKey('role');
        $row->addFilter($filter);

        $filter = new PaginationFilter();
        $filter->setName('Role: Parent')
            ->setValue('Parent')
            ->setGroup('Role')
            ->setContentKey('role');
        $row->addFilter($filter);

        $filter = new PaginationFilter();
        $filter->setName('Role: Staff')
            ->setValue('Staff')
            ->setGroup('Role')
            ->setContentKey('role');
        $row->addFilter($filter);

        $filter = new PaginationFilter();
        $filter->setName('Role: Other')
            ->setValue('Other')
            ->setGroup('Role')
            ->setContentKey('role');
        $row->addFilter($filter);

        $filter = new PaginationFilter();
        $filter->setName('Status: Full')
            ->setValue('Full')
            ->setGroup('Status')
            ->setContentKey('status');
        $row->addFilter($filter);

        $filter = new PaginationFilter();
        $filter->setName('Status: Left')
            ->setValue('Left')
            ->setGroup('Status')
            ->setContentKey('status');
        $row->addFilter($filter);

        $filter = new PaginationFilter();
        $filter->setName('Status: Expected')
            ->setValue('Expected')
            ->setGroup('Status')
            ->setContentKey('status');
        $row->addFilter($filter);

        $filter = new PaginationFilter();
        $filter->setName('Before Start Date')
            ->setValue(true)
            ->setGroup('Date')
            ->setContentKey('start_date');
        $row->addFilter($filter);

        $filter = new PaginationFilter();
        $filter->setName('After End Date')
            ->setValue(true)
            ->setGroup('Date')
            ->setContentKey('end_date');
        $row->addFilter($filter);

        $this->setRow($row);
        return $this;
    }

    /**
     * @var RoleHierarchyInterface
     */
    private $hierarchy;

    /**
     * @return RoleHierarchyInterface
     */
    public function getHierarchy(): RoleHierarchyInterface
    {
        return $this->hierarchy;
    }

    /**
     * Hierarchy.
     *
     * @param RoleHierarchyInterface $hierarchy
     * @return PeoplePagination
     */
    public function setHierarchy(RoleHierarchyInterface $hierarchy): PeoplePagination
    {
        $this->hierarchy = $hierarchy;
        return $this;
    }
}