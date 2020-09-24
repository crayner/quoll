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
 * Date: 11/09/2020
 * Time: 07:46
 */
namespace App\Modules\Enrolment\Pagination;

use App\Manager\AbstractPaginationManager;
use App\Manager\Hidden\PaginationAction;
use App\Manager\Hidden\PaginationColumn;
use App\Manager\Hidden\PaginationRow;
use App\Manager\Hidden\PaginationSelectAction;
use App\Manager\PaginationInterface;
use App\Modules\People\Entity\Person;
use App\Util\TranslationHelper;

/**
 * Class IndividualClassEnrolmentPagination
 * @package App\Modules\Enrolment\Pagination
 * @author Craig Rayner <craig@craigrayner.com>
 */
class IndividualClassEnrolmentPagination extends AbstractPaginationManager
{
    /**
     * @var Person
     */
    private Person $person;

    /**
     * execute
     *
     * 14/09/2020 09:51
     * @return PaginationInterface
     */
    public function execute(): PaginationInterface
    {
        TranslationHelper::setDomain('Enrolment');
        $row = new PaginationRow();

        $column = new PaginationColumn();
        $row->addColumn($column->setLabel('Class Code')
            ->setContentKey('classCode')
            ->setClass('column relative pr-4 cursor-pointer widthAuto'));

        $column = new PaginationColumn();
        $row->addColumn($column->setLabel('Course')
            ->setContentKey('course')
            ->setClass('column relative pr-4 cursor-pointer widthAuto'));

        $column = new PaginationColumn();
        $row->addColumn($column->setLabel('Class Role')
            ->setContentKey('role')
            ->setClass('column relative pr-4 cursor-pointer widthAuto'));

        if ($this->getPerson()->isStudent()) {
            $column = new PaginationColumn();
            $row->addColumn($column->setLabel('Reportable')
                ->setContentKey('reportable')
                ->setClass('column relative pr-4 cursor-pointer widthAuto text-centre'));
        }

        $action = new PaginationAction();
        $row->addAction($action->setTitle('Edit')
            ->setAClass('thickbox p-3 sm:p-0')
            ->setColumnClass('column p-2 sm:p-3')
            ->setSpanClass('fas fa-edit fa-fw fa-1-5x text-gray-800 hover:text-indigo-500')
            ->setRoute('individual_enrolment_edit')
            ->setRouteParams(['class' => 'course_class_id', 'person' => 'person_id'])
        );

        $action = new PaginationAction();
        $row->addAction($action->setTitle('Delete')
            ->setAClass('thickbox p-3 sm:p-0')
            ->setColumnClass('column p-2 sm:p-3')
            ->setSpanClass('fas fa-eraser fa-fw fa-1-5x text-gray-800 hover:text-red-500')
            ->setRoute('individual_enrolment_remove')
            ->setRouteParams(['class' => 'course_class_id','person' => 'person_id'])
        );


        $action = new PaginationAction();
        $select = new PaginationSelectAction();
        $action->addSectionAction($select->setRoute('individual_enrolment_remove_selected')
            ->setPrompt('Remove from class')
        );

        $row->addAction($action
            ->setTitle('Select Row')
            ->setRoute('home')
            ->setSelectRow()
        );

        $this->setRow($row);
        return $this;
    }

    /**
     * @return Person
     */
    public function getPerson(): Person
    {
        return $this->person;
    }

    /**
     * @param Person $person
     * @return IndividualClassEnrolmentPagination
     */
    public function setPerson(Person $person): IndividualClassEnrolmentPagination
    {
        $this->person = $person;
        return $this;
    }
}
