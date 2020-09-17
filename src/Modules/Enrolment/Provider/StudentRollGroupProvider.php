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
 * Date: 26/05/2020
 * Time: 10:33
 */
namespace App\Modules\Enrolment\Provider;

use App\Modules\Enrolment\Entity\StudentRollGroup;
use App\Provider\AbstractProvider;

/**
 * Class StudentEnrolmentProvider
 * @package App\Modules\Enrolment\Provider
 */
class StudentRollGroupProvider extends AbstractProvider
{
    /**
     * @var string
     */
    protected string $entityName = StudentRollGroup::class;

    /**
     * canDelete
     *
     * 10/09/2020 09:36
     * @return bool
     */
    public function canDelete(): bool
    {
        return false;
    }
}
