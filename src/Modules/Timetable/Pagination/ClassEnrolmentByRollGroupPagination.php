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
 * Time: 08:47
 */
namespace App\Modules\Timetable\Pagination;

use App\Manager\AbstractPaginationManager;
use App\Manager\Hidden\PaginationColumn;
use App\Manager\Hidden\PaginationFilter;
use App\Manager\Hidden\PaginationRow;
use App\Manager\PaginationInterface;
use App\Modules\RollGroup\Entity\RollGroup;
use App\Provider\ProviderFactory;
use App\Util\TranslationHelper;

/**
 * Class ClassEnrolmentByRollGroupPagination
 * @package App\Modules\Timetable\Pagination
 * @author Craig Rayner <craig@craigrayner.com>
 */
class ClassEnrolmentByRollGroupPagination extends AbstractPaginationManager
{
    /**
     * execute
     *
     * 31/08/2020 09:46
     * @return $this|PaginationInterface
     */
    public function execute(): PaginationInterface
    {
        TranslationHelper::setDomain('RollGroup');
        $row = new PaginationRow();

        $column = new PaginationColumn();
        $column->setLabel('Roll Group')
            ->setContentKey('rollGroupName')
            ->setClass('column relative pr-4 cursor-pointer widthAuto')
        ;
        $row->addColumn($column);

        $column = new PaginationColumn();
        $column->setLabel('Student')
            ->setContentKey('studentName')
            ->setSort()
            ->setClass('column relative pr-4 cursor-pointer widthAuto')
        ;
        $row->addColumn($column);

        $column = new PaginationColumn();
        $column->setLabel('Class Count')
            ->setContentKey('classCount')
            ->setSort()
            ->setClass('column relative pr-4 cursor-pointer widthAuto')
        ;
        $row->addColumn($column);

        $p = null;
        foreach(ProviderFactory::getRepository(RollGroup::class)->findBy([],['name' => 'ASC']) as $roll)
        {
            if (is_null($p)) $p = $roll;
            $filter = new PaginationFilter();
            $filter->setName('Roll Group: ' . $roll->getName())
                ->setLabel(['Roll Group: {name}', ['{name}' => $roll->getName()], 'People'])
                ->setValue($roll->getName())
                ->setGroup('Roll Group')
                ->setContentKey('rollGroupName');
            $row->addFilter($filter);
        }

        $row->setDefaultFilter(['Roll Group' => 'Roll Group: ' .$p->getName()]);

        $this->setRow($row);
        return $this;
    }
}
