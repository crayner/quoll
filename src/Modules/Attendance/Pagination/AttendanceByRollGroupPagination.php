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
 * Date: 18/10/2020
 * Time: 09:52
 */
namespace App\Modules\Attendance\Pagination;

use App\Manager\AbstractPagination;
use App\Manager\Hidden\PaginationColumn;
use App\Manager\Hidden\PaginationRow;
use App\Manager\PaginationInterface;
use App\Modules\RollGroup\Entity\RollGroup;
use App\Util\TranslationHelper;

/**
 * Class AttendanceByRollGroupPagination
 *
 * 18/10/2020 09:52
 * @package App\Modules\Attendance\Pagination
 * @author Craig Rayner <craig@craigrayner.com>
 */
class AttendanceByRollGroupPagination extends AbstractPagination
{
    /**
     * execute
     * @return $this|PaginationInterface
     * 2/08/2020 15:06
     */
    public function execute(): PaginationInterface
    {
        TranslationHelper::setDomain('RollGroup');
        TranslationHelper::addTranslation('Students', [], 'Student');
        TranslationHelper::addTranslation('Sort By',[],'messages');
        TranslationHelper::addTranslation('rollOrder');
        TranslationHelper::addTranslation('Surname',[],'Student');
        TranslationHelper::addTranslation('Preferred Name',[],'Student');
        TranslationHelper::addTranslation('Take Attendance {name}', ['{name}' => $this->getContext('Roll Group Name')], 'RollGroup');
        $row = new PaginationRow();
        $row->setSpecial('Roll Group Attendance');

        $column = new PaginationColumn();
        $row->addColumn($column->setLabel('Photo')
            ->setContentKey('photo')
            ->setContentType('image')
            ->setDefaultValue(['/build/static/DefaultPerson.png']));

        $column->setLabel('Name')
            ->setContentKey('full_name')
        ;
        $row->addColumn($column);

        $this->setRow($row);

        return $this;
    }
}
