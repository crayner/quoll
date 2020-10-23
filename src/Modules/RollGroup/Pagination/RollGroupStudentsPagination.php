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
 * Date: 2/08/2020
 * Time: 14:43
 */
namespace App\Modules\RollGroup\Pagination;

use App\Manager\AbstractPagination;
use App\Manager\Hidden\PaginationColumn;
use App\Manager\Hidden\PaginationRow;
use App\Manager\PaginationInterface;
use App\Util\TranslationHelper;

/**
 * Class RollGroupStudentsPagination
 * @package App\Modules\RollGroup\Pagination
 * @author Craig Rayner <craig@craigrayner.com>
 */
class RollGroupStudentsPagination extends AbstractPagination
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
        $row = new PaginationRow();
        $row->setSpecial('Roll Group Students');

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
