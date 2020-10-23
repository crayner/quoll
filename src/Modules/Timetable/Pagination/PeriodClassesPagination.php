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
 * Date: 13/10/2020
 * Time: 14:58
 */
namespace App\Modules\Timetable\Pagination;

use App\Manager\AbstractPagination;
use App\Manager\Hidden\PaginationAction;
use App\Manager\Hidden\PaginationColumn;
use App\Manager\Hidden\PaginationRow;
use App\Manager\PaginationInterface;
use App\Modules\Timetable\Entity\TimetablePeriod;
use App\Util\TranslationHelper;

/**
 * Class PeriodClassesPagination
 *
 * 13/10/2020 15:01
 * @package App\Modules\Timetable\Pagination
 * @author Craig Rayner <craig@craigrayner.com>
 */
class PeriodClassesPagination extends AbstractPagination
{
    private TimetablePeriod $period;

    /**
     * execute
     *
     * 13/10/2020 14:59
     * @return $this|PaginationInterface
     */
    public function execute(): PaginationInterface
    {
        TranslationHelper::setDomain('Timetable');
        $row = new PaginationRow();

        $column = new PaginationColumn();
        $row->addColumn($column->setLabel('Class')
            ->setContentKey(['name','abbreviation'])
            ->setSort()
            ->setSearch()
            ->setClass('column relative pr-4 cursor-pointer widthAuto'));

        $column = new PaginationColumn();
        $row->addColumn($column->setLabel('Location')
            ->setContentKey('location')
            ->setSort()
            ->setClass('column relative pr-4 cursor-pointer widthAuto'));

        $column = new PaginationColumn();
        $row->addColumn($column->setLabel('Tutors')
            ->setContentKey('tutors')
            ->setSort()
            ->setClass('column relative pr-4 cursor-pointer widthAuto'));

        $action = new PaginationAction();
        $row->addAction($action->setTitle('Edit')
            ->setAClass('p-3 sm:p-0')
            ->setColumnClass('column p-2 sm:p-3')
            ->setSpanClass('fas fa-edit fa-fw fa-1-5x text-gray-800 hover:text-indigo-500')
            ->setRoute('timetable_day_period_class_edit')
            ->setRouteParams(['period' => 'period', 'periodClass' => 'id']));

        /**
        $action = new PaginationAction();
        $action->setTitle('Delete')
            ->setAClass('p-3 sm:p-0')
            ->setColumnClass('column p-2 sm:p-3')
            ->setSpanClass('far fa-trash-alt fa-fw fa-1-5x text-gray-800 hover:text-red-500')
            ->setRoute('timetable_day_delete')
            ->setOnClick('areYouSure')
            ->setDisplayWhen('canDelete')
            ->setRouteParams(['timetableDay' => 'id']);
        $row->addAction($action);

        $row->addHighlight([
            'className' => 'warning',
            'columnKey' => 'isFixed',
            'columnValue' => true,
        ]);
         * */
        $this->setRow($row);
        return $this;
    }

    /**
     * getPeriod
     *
     * 13/10/2020 15:05
     * @return TimetablePeriod
     */
    public function getPeriod(): TimetablePeriod
    {
        return $this->period;
    }

    /**
     * setPeriod
     *
     * 13/10/2020 15:05
     * @param TimetablePeriod $period
     * @return $this
     */
    public function setPeriod(TimetablePeriod $period): PeriodClassesPagination
    {
        $this->period = $period;
        return $this;
    }
}
