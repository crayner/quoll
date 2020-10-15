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
 * Date: 3/09/2020
 * Time: 08:26
 */
namespace App\Modules\Enrolment\Pagination;

use App\Manager\AbstractPaginationManager;
use App\Manager\Hidden\PaginationAction;
use App\Manager\Hidden\PaginationColumn;
use App\Manager\Hidden\PaginationFilter;
use App\Manager\Hidden\PaginationGroup;
use App\Manager\Hidden\PaginationRow;
use App\Manager\PaginationInterface;
use App\Modules\School\Entity\YearGroup;
use App\Provider\ProviderFactory;
use App\Util\TranslationHelper;

/**
 * Class CourseClassEnrolmentPagination
 * @package App\Modules\Enrolment\Pagination
 * @author Craig Rayner <craig@craigrayner.com>
 */
class CourseClassEnrolmentPagination extends AbstractPaginationManager
{
    /**
     * execute
     *
     * 3/09/2020 08:28
     * @return $this|PaginationInterface
     */
    public function execute(): PaginationInterface
    {
        TranslationHelper::setDomain('Enrolment');
        $row = new PaginationRow();

        $column = new PaginationColumn();
        $column->setLabel('Name')
            ->setContentKey('name')
            ->setClass('column relative pr-4 cursor-pointer widthAuto')
        ;
        $row->addColumn($column);

        $column = new PaginationColumn();
        $column->setLabel('Abbreviation')
            ->setContentKey('abbreviation')
            ->setClass('column relative pr-4 cursor-pointer widthAuto')
        ;
        $row->addColumn($column);

        $column = new PaginationColumn();
        $column->setLabel('Participants')
            ->setHelp('Active')
            ->setContentKey('activeParticipants')
            ->setClass('column relative pr-4 cursor-pointer widthAuto text-centre')
        ;
        $row->addColumn($column);

        $column = new PaginationColumn();
        $column->setLabel('Participants')
            ->setHelp('Expected')
            ->setContentKey('expectedParticipants')
            ->setClass('column relative pr-4 cursor-pointer widthAuto text-centre')
        ;
        $row->addColumn($column);

        $column = new PaginationColumn();
        $column->setLabel('Participants')
            ->setHelp('Total')
            ->setContentKey('totalParticipants')
            ->setClass('column relative pr-4 cursor-pointer widthAuto text-centre')
        ;
        $row->addColumn($column);

        $column = new PaginationColumn();
        $column->setContentKey('courseName')
            ->setDataOnly()
            ->setSearch()
        ;
        $row->addColumn($column);

        $column = new PaginationColumn();
        $column->setContentKey('yearGroup')
            ->setDataOnly()
        ;
        $row->addColumn($column);

        $action = new PaginationAction();
        $row->addAction($action->setTitle('Edit')
            ->setAClass('p-3 sm:p-0')
            ->setColumnClass('column p-2 sm:p-3')
            ->setSpanClass('fas fa-edit fa-fw fa-1-5x text-gray-800 hover:text-indigo-500')
            ->setRoute('course_class_enrolment_manage')
            ->setRouteParams(['class' => 'id'])
        );

        foreach (ProviderFactory::getRepository(YearGroup::class)->findBy([],['sortOrder' => 'ASC']) as $yg) {
            $filter = new PaginationFilter();
            $row->addFilter($filter->setGroup('Year Group')
                ->setName($yg->getName())
                ->setLabel(['{name}', ['{name}' => $yg->getName()], 'messages'])
                ->setContentKey('yearGroup')
                ->setValue($yg->getName())
            );
        }

        $this->setRow($row)
            ->setGroup(new PaginationGroup('Course', 'courseName'));

        return $this;
    }
}
