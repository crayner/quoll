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
 * Date: 28/10/2020
 * Time: 10:07
 */
namespace App\Modules\Attendance\Pagination;

use App\Manager\AbstractPagination;
use App\Manager\Hidden\PaginationColumn;
use App\Manager\Hidden\PaginationRow;
use App\Manager\PaginationInterface;
use App\Util\TranslationHelper;

/**
 * Class AttendanceRecorderLogPagination
 *
 * 28/10/2020 10:08
 * @package App\Modules\Attendance\Pagination
 * @author Craig Rayner <craig@craigrayner.com>
 */
class AttendanceRecorderLogPagination extends AbstractPagination
{
    /**
     * execute
     *
     * 28/10/2020 10:08
     * @return $this|PaginationInterface
     */
    public function execute(): PaginationInterface
    {
        TranslationHelper::setDomain('Attendance');
        $row = new PaginationRow();

        $column = new PaginationColumn();
        $column->setLabel('Recorded On')
            ->setContentKey('recorded_on')
            ->setClass('column relative pr-4 cursor-pointer widthAuto')
        ;
        $row->addColumn($column);

        $column = new PaginationColumn();
        $column->setLabel('Attendance')
            ->setContentKey(['direction_translated','details'])
            ->setClass('column relative pr-4 cursor-pointer widthAuto')
        ;
        $row->addColumn($column);

        $column = new PaginationColumn();
        $column->setLabel('Context')
            ->setContentKey('context')
            ->setClass('column relative pr-4 cursor-pointer widthAuto');
        $row->addColumn($column);

        $column = new PaginationColumn();
        $column->setLabel('Recorded By')
            ->setContentKey('recorder')
            ->setClass('column relative pr-4 cursor-pointer widthAuto');
        $row->addColumn($column)
            ->addHighlight(['className' => 'error', 'columnKey' => 'direction', 'columnValue' => 'Out'])
            ->addHighlight(['className' => 'success', 'columnKey' => 'direction', 'columnValue' => 'In'])

        ;

        $this->setRow($row);
        return $this;
    }
}
