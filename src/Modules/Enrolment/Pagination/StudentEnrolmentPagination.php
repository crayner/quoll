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
 * Date: 8/09/2020
 * Time: 09:18
 */
namespace App\Modules\Enrolment\Pagination;

use App\Manager\AbstractPagination;
use App\Manager\Hidden\PaginationAction;
use App\Manager\Hidden\PaginationColumn;
use App\Manager\Hidden\PaginationFilter;
use App\Manager\Hidden\PaginationRow;
use App\Manager\PaginationInterface;
use App\Modules\RollGroup\Entity\RollGroup;
use App\Modules\School\Entity\YearGroup;
use App\Modules\School\Util\AcademicYearHelper;
use App\Provider\ProviderFactory;
use App\Util\TranslationHelper;

/**
 * Class StudentEnrolmentPagination
 * @package App\Modules\Enrolment\Pagination
 * @author Craig Rayner <craig@craigrayner.com>
 */
class StudentEnrolmentPagination extends AbstractPagination
{
    public function execute(): PaginationInterface
    {
        TranslationHelper::setDomain('Enrolment');
        $row = new PaginationRow();

        $column = new PaginationColumn();
        $column->setLabel('Student')
            ->setContentKey('student')
            ->setSearch()
            ->setSort()
            ->setClass('column relative pr-4 cursor-pointer widthAuto');
        $row->addColumn($column);

        $column = new PaginationColumn();
        $column->setLabel('Year Group')
            ->setSort()
            ->setContentKey('yearGroup')
            ->setClass('column relative pr-4 cursor-pointer widthAuto');
        $row->addColumn($column);

        $column = new PaginationColumn();
        $column->setLabel('Roll Group')
            ->setHelp('Roll Order')
            ->setSort()
            ->setContentKey(['rollGroup', 'rollOrder'])
            ->setClass('column relative pr-4 cursor-pointer widthAuto');
        $row->addColumn($column);

        $column = new PaginationColumn();
        $column->setLabel('Status')
            ->setContentKey('status')
            ->setClass('column relative pr-4 cursor-pointer widthAuto');
        $row->addColumn($column);

        $action = new PaginationAction();
        $row->addAction($action->setTitle('Edit')
            ->setAClass('p-3 sm:p-0')
            ->setColumnClass('column p-2 sm:p-3')
            ->setSpanClass('fas fa-edit fa-fw fa-1-5x text-gray-800 hover:text-indigo-500')
            ->setRoute('student_enrolment_edit')
            ->setRouteParams(['student' => 'id'])
        );

        $action = new PaginationAction();
        $row->addAction($action->setTitle('Remove')
            ->setAClass('p-3 sm:p-0')
            ->setColumnClass('column p-2 sm:p-3')
            ->setSpanClass('fas fa-eraser fa-fw fa-1-5x text-gray-800 hover:text-red-500')
            ->setRoute('student_enrolment_delete')
            ->setDisplayWhen('canDelete')
            ->setOnClick('areYouSure')
            ->setRouteParams(['enrolment' => 'enrolment'])
        );

        foreach (ProviderFactory::getRepository(YearGroup::class)->findBy([],['sortOrder' => 'ASC']) as $yg)
        {
            $filter = new PaginationFilter();
            $row->addFilter($filter->setName($yg->getName())
                ->setLabel(['{name}', ['{name}' => $yg->getName()], 'messages'])
                ->setValue($yg->getName())
                ->setGroup('Year Group')
                ->setContentKey('yearGroup')
            );
        }

        foreach (ProviderFactory::getRepository(RollGroup::class)->findBy(['academicYear' => AcademicYearHelper::getCurrentAcademicYear()],['name' => 'ASC']) as $rg)
        {
            $filter = new PaginationFilter();
            $row->addFilter($filter->setName($rg->getName())
                ->setLabel(['{name}', ['{name}' => $rg->getName()], 'messages'])
                ->setValue($rg->getName())
                ->setGroup('Roll Group')
                ->setContentKey('rollGroup')
            );
        }

        $this->setRow($row);

        return $this;
    }
}