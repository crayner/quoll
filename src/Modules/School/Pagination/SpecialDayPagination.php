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
 * Date: 23/12/2019
 * Time: 12:38
 */
namespace App\Modules\School\Pagination;

use App\Manager\Hidden\PaginationAction;
use App\Manager\Hidden\PaginationColumn;
use App\Manager\Hidden\PaginationFilter;
use App\Manager\Hidden\PaginationRow;
use App\Manager\PaginationInterface;
use App\Manager\AbstractPaginationManager;
use App\Modules\School\Entity\AcademicYear;
use App\Modules\School\Util\AcademicYearHelper;
use App\Provider\ProviderFactory;
use App\Util\TranslationHelper;

/**
 * Class SpecialDayPagination
 * @package App\Modules\School\Pagination
 * @author Craig Rayner <craig@craigrayner.com>
 */
class SpecialDayPagination extends AbstractPaginationManager
{
    public function execute(): PaginationInterface
    {
        TranslationHelper::setDomain('School');
        $row = new PaginationRow();

        $column = new PaginationColumn();
        $column->setLabel('Academic Year')
            ->setContentKey('year')
            ->setClass('column relative pr-4 cursor-pointer widthAuto text-centre')
        ;
        $row->addColumn($column);

        $column = new PaginationColumn();
        $column->setLabel('Date')
            ->setContentKey('date')
            ->setSort(true)
            ->setClass('column relative pr-4 cursor-pointer widthAuto text-centre')
        ;
        $row->addColumn($column);

        $column = new PaginationColumn();
        $column->setLabel('Name')
            ->setContentKey(['name','description'])
            ->setSort(true)
            ->setSearch()
            ->setClass('column relative pr-4 cursor-pointer widthAuto text-centre')
        ;
        $row->addColumn($column);

        $column = new PaginationColumn();
        $column->setLabel('Type')
            ->setContentKey('type')
            ->setClass('column relative pr-4 cursor-pointer widthAuto text-centre')
        ;
        $row->addColumn($column);

        $action = new PaginationAction();
        $action->setTitle('Edit')
            ->setAClass('thickbox p-3 sm:p-0')
            ->setColumnClass('column p-2 sm:p-3')
            ->setSpanClass('fas fa-edit fa-fw fa-1-5x text-gray-800 hover:text-purple-600')
            ->setRoute('special_day_edit')
            ->setRouteParams(['day' => 'id']);
        $row->addAction($action);

        $action = new PaginationAction();
        $action->setTitle('Delete')
            ->setAClass('thickbox p-3 sm:p-0')
            ->setColumnClass('column p-2 sm:p-3')
            ->setSpanClass('far fa-trash-alt fa-fw fa-1-5x text-gray-800 hover:text-red-600')
            ->setRoute('special_day_delete')
            ->setDisplayWhen('canDelete')
            ->setOnClick('areYouSure')
            ->setRouteParams(['day' => 'id']);
        $row->addAction($action);

        $action = new PaginationAction();
        $action->setTitle('Duplicate')
            ->setAClass('thickbox p-3 sm:p-0')
            ->setColumnClass('column p-2 sm:p-3')
            ->setSpanClass('far fa-copy fa-fw fa-1-5x text-gray-800 hover:text-green-600')
            ->setRoute('special_day_duplicate')
            ->setDisplayWhen('canDuplicate')
            ->setRouteParams(['day' => 'id']);
        $row->addAction($action);

        foreach(ProviderFactory::getRepository(AcademicYear::class)->findBy([], ['firstDay' => 'ASC']) as $year) {
            $filter = new PaginationFilter();
            $filter->setName('Academic Year: ' . $year->getName())
                ->setValue($year->getName())
                ->setLabel(['Academic Year: {value}', ['{value}' => $year->getName()], 'School'])
                ->setGroup('Academic Year')
                ->setContentKey('year');
            $row->addFilter($filter);
        }

        $year = AcademicYearHelper::getCurrentAcademicYear();
        $row->addDefaultFilter('Academic Year: ' . $year->getName());
        $this->setRow($row);

        return $this;
    }
}