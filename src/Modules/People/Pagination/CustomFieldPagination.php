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
 * Date: 18/05/2020
 * Time: 10:32
 */
namespace App\Modules\People\Pagination;

use App\Manager\AbstractPaginationManager;
use App\Manager\Hidden\PaginationAction;
use App\Manager\Hidden\PaginationColumn;
use App\Manager\Hidden\PaginationFilter;
use App\Manager\Hidden\PaginationRow;
use App\Manager\PaginationInterface;
use App\Modules\People\Entity\CustomField;
use App\Util\TranslationHelper;

/**
 * Class CustomFieldPagination
 * @package App\Modules\People\Pagination
 */
class CustomFieldPagination extends AbstractPaginationManager
{
    public function execute(): PaginationInterface
    {
        TranslationHelper::setDomain('People');
        $row = new PaginationRow();

        $column = new PaginationColumn();
        $column->setLabel('Name')
            ->setContentKey(['name', 'description'])
            ->setHelp('Description')
            ->setSort()
            ->setSearch()
            ->setClass('column relative pr-4 cursor-pointer widthAuto');
        $row->addColumn($column);

        $column = new PaginationColumn();
        $column->setLabel('Field Type')
            ->setContentKey(['fieldType'])
            ->setSort()
            ->setClass('column relative pr-4 cursor-pointer widthAuto');
        $row->addColumn($column);

        $column = new PaginationColumn();
        $column->setLabel('Active')
            ->setContentKey(['active'])
            ->setClass('column relative pr-4 cursor-pointer widthAuto');
        $row->addColumn($column);

        $column = new PaginationColumn();
        $column->setLabel('Applies to Categories')
            ->setContentKey(['categories'])
            ->setSort()
            ->setClass('column relative pr-4 cursor-pointer widthAuto');
        $row->addColumn($column);

        $action = new PaginationAction();
        $action->setTitle('Edit')
            ->setAClass('thickbox p-3 sm:p-0')
            ->setColumnClass('column p-2 sm:p-3')
            ->setSpanClass('fas fa-edit fa-fw fa-1-5x text-gray-800 hover:text-green-500')
            ->setRoute('custom_field_edit')
            ->setRouteParams(['customField' => 'id']);
        $row->addAction($action);

        $action = new PaginationAction();
        $action->setTitle('Delete')
            ->setAClass('thickbox p-3 sm:p-0')
            ->setColumnClass('column p-2 sm:p-3')
            ->setSpanClass('far fa-trash-alt fa-fw fa-1-5x text-gray-800 hover:text-red-500')
            ->setRoute('custom_field_delete')
            ->setOnClick('areYouSure')
            ->setDisplayWhen('canDelete')
            ->setRouteParams(['customField' => 'id']);
        $row->addAction($action);

        $filter = new PaginationFilter();
        $filter->setName('Active: Yes')
            ->setGroup('Active')
            ->setContentKey('isActive')
            ->setExactMatch()
            ->setValue(true);
        $row->addFilter($filter);

        $filter = new PaginationFilter();
        $filter->setName('Active: No')
            ->setGroup('Active')
            ->setContentKey('isActive')
            ->setExactMatch()
            ->setValue(false);
        $row->addFilter($filter);

        foreach(CustomField::getCategoriesList() as $role) {
            $filter = new PaginationFilter();
            $filter->setName('Category: '. ucfirst($role))
                ->setLabel(['Category: {name}', ['{name}' => TranslationHelper::translate('customfield.categories.'.strtolower($role), [], 'People')], 'People'])
                ->setGroup('Category')
                ->setContentKey('isCategory' . str_replace(' ','', ucfirst($role)))
                ->setExactMatch()
                ->setValue(true);
            $row->addFilter($filter);
        }

        $this->setRow($row);
        return $this;
    }
}