<?php
/**
 * Created by PhpStorm.
 *
* Quoll
 * (c) 2020 Craig Rayner <craig@craigrayner.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * User: craig
 * Date: 10/01/2020
 * Time: 07:58
 */

namespace App\Modules\School\Provider;

use App\Manager\Traits\EntityTrait;
use App\Modules\School\Entity\Scale;
use App\Modules\School\Entity\ScaleGrade;
use App\Provider\EntityProviderInterface;

/**
 * Class ScaleProvider
 * @package App\Modules\School\Provider
 */
class ScaleProvider implements EntityProviderInterface
{
    use EntityTrait;

    private $entityName = Scale::class;

    /**
     * canDelete
     * @param Scale $scale
     * @return bool
     */
    public function canDelete(Scale $scale)
    {
        if ($this->getRepository(ScaleGrade::class)->countScaleUse($scale) === 0)
            return true;
        return false;
    }
}