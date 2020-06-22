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
 * Date: 12/01/2020
 * Time: 16:34
 */
namespace App\Modules\School\Pagination;

use App\Manager\Hidden\PaginationAction;
use App\Manager\Hidden\PaginationColumn;
use App\Manager\Hidden\PaginationRow;
use App\Manager\PaginationInterface;
use App\Manager\AbstractPaginationManager;
use App\Util\TranslationHelper;

/**
 * Class ScaleGradePagination
 * @package App\Modules\School\Pagination
 * @author Craig Rayner <craig@craigrayner.com>
 */
class ScaleGradePagination extends AbstractPaginationManager
{
    /**
     * execute
     * @return PaginationInterface
     */
    public function execute(): PaginationInterface
    {
        TranslationHelper::setDomain('School');
        $row = new PaginationRow();
        $this->setTargetElement('scaleGradePaginationContent');

        $column = new PaginationColumn();
        $column->setLabel('Value')
            ->setSort(true)
            ->setContentKey('value')
            ->setClass('column relative pr-4 cursor-pointer widthAuto')
        ;
        $row->addColumn($column);

        $column = new PaginationColumn();
        $column->setLabel('Descriptor')
            ->setContentKey('descriptor')
            ->setClass('column relative pr-4 cursor-pointer widthAuto')
        ;
        $row->addColumn($column);

        $column = new PaginationColumn();
        $column->setLabel('Is Default?')
            ->setContentKey('default')
            ->setClass('column relative pr-4 cursor-pointer widthAuto text-center');
        $row->addColumn($column);

        $action = new PaginationAction();
        $action->setTitle('Edit')
            ->setAClass('thickbox p-3 sm:p-0')
            ->setColumnClass('column p-2 sm:p-3')
            ->setSpanClass('fas fa-edit fa-fw fa-1-5x text-gray-800 hover:text-indigo-500')
            ->setRoute('scale_grade_edit')
            ->setRouteParams(['grade' => 'id', 'scale' => 'scaleId']);
        $row->addAction($action);

        $action = new PaginationAction();
        $action->setTitle('Delete')
            ->setAClass('thickbox p-3 sm:p-0')
            ->setColumnClass('column p-2 sm:p-3')
            ->setSpanClass('far fa-trash-alt fa-fw fa-1-5x text-gray-800 hover:text-red-500')
            ->setRoute('scale_grade_delete')
            ->setDisplayWhen('canDelete')
            ->setOnClick('areYouSure')
            ->setRouteParams(['grade' => 'id', 'scale' => 'scaleId']);
        $row->addAction($action);

        $this
            ->setRow($row)
            ->setDraggableSort(true)
            ->setDraggableRoute('scale_grade_sort');

        $row->setAddElement(['Add Scale Grade', [], 'School']);
        return $this;
    }
}