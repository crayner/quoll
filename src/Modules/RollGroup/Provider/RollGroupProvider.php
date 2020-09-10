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
 * Date: 27/07/2019
 * Time: 11:04
 */
namespace App\Modules\RollGroup\Provider;

use App\Modules\School\Util\AcademicYearHelper;
use App\Provider\AbstractProvider;
use App\Modules\RollGroup\Entity\RollGroup;

/**
 * Class RollGroupProvider
 * @package App\Modules\RollGroup\Provider
 * @author Craig Rayner <craig@craigrayner.com>
 */
class RollGroupProvider extends AbstractProvider
{
    /**
     * @var string
     */
    protected $entityName = RollGroup::class;

    /**
     * canDelete
     * @param RollGroup $roll
     * @return bool
     */
    public function canDelete(RollGroup $roll): bool
    {
        return $roll->getStudentEnrolments()->count() === 0;
    }
}