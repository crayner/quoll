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
 * Date: 31/08/2020
 * Time: 09:45
 */
namespace App\Modules\Curriculum\Pagination;

use App\Manager\AbstractPaginationManager;
use App\Manager\Hidden\PaginationAction;
use App\Manager\Hidden\PaginationColumn;
use App\Manager\Hidden\PaginationFilter;
use App\Manager\Hidden\PaginationRow;
use App\Manager\PaginationInterface;
use App\Modules\School\Entity\YearGroup;
use App\Provider\ProviderFactory;
use App\Util\TranslationHelper;

/**
 * Class CoursePagination
 * @package App\Modules\Curriculum\Pagination
 * @author Craig Rayner <craig@craigrayner.com>
 */
class CoursePagination extends AbstractPaginationManager
{
    /**
     * execute
     *
     * 31/08/2020 16:15
     * @return $this|PaginationInterface
     */
    public function execute(): PaginationInterface
    {
        TranslationHelper::setDomain('Curriculum');
        $row = new PaginationRow();

        $column = new PaginationColumn();
        $column->setLabel('Abbreviation')
            ->setSearch()
            ->setContentKey('abbreviation')
            ->setClass('column relative pr-4 cursor-pointer widthAuto')
        ;
        $row->addColumn($column);

        $column = new PaginationColumn();
        $column->setLabel('Name')
            ->setContentKey('name')
            ->setSearch()
            ->setSort()
            ->setClass('column relative pr-4 cursor-pointer widthAuto')
        ;
        $row->addColumn($column);

        $column = new PaginationColumn();
        $column->setLabel('Learning Area')
            ->setContentKey('area')
            ->setSearch()
            ->setSort()
            ->setClass('column relative pr-4 cursor-pointer widthAuto')
        ;
        $row->addColumn($column);

        $column = new PaginationColumn();
        $column->setLabel('Classes')
            ->setContentKey('classCount')
            ->setClass('column relative pr-4 cursor-pointer widthAuto text-center')
        ;
        $row->addColumn($column);

        $column = new PaginationColumn();
        $column->setContentKey('yearGroups')
            ->setDataOnly()
        ;
        $row->addColumn($column);

        foreach (ProviderFactory::getRepository(YearGroup::class)->findBy([],['sortOrder' => 'ASC']) as $yg) {
            $filter = new PaginationFilter();
            $row->addFilter(
                $filter->setName($yg->getName())
                    ->setLabel(['{name}', ['{name}' => $yg->getName()], 'School'])
                    ->setContentKey('yearGroups')
                    ->setValue($yg->getAbbreviation())
                    ->setGroup('Year Group')
            );
        }

        $action = new PaginationAction();
        $row->addAction($action->setTitle('Edit')
                ->setAClass('thickbox p-3 sm:p-0')
                ->setColumnClass('column p-2 sm:p-3')
                ->setSpanClass('fas fa-edit fa-fw fa-1-5x text-gray-800 hover:text-indigo-500')
                ->setRoute('course_edit')
                ->setRouteParams(['course' => 'id'])
            )
        ;
        $action = new PaginationAction();
        $row->addAction($action->setTitle('Delete')
                ->setAClass('thickbox p-3 sm:p-0')
                ->setColumnClass('column p-2 sm:p-3')
                ->setSpanClass('fas fa-trash-alt fa-fw fa-1-5x text-gray-800 hover:text-red-500')
                ->setRoute('course_delete')
                ->setDisplayWhen('canDelete')
                ->setRouteParams(['course' => 'id'])
            )
        ;
        $this->setRow($row);
        return $this;
    }
}
