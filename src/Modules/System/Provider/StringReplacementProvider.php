<?php
/**
 * Created by PhpStorm.
 *
 * kookaburra
 * (c) 2019 Craig Rayner <craig@craigrayner.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * User: craig
 * Date: 9/08/2019
 * Time: 13:21
 */

namespace App\Modules\System\Provider;

use App\Manager\Traits\EntityTrait;
use App\Modules\System\Entity\StringReplacement;
use App\Provider\EntityProviderInterface;

/**
 * Class StringReplacementProvider
 * @package App\Modules\System\Provider
 */
class StringReplacementProvider implements EntityProviderInterface
{
    use EntityTrait;

    /**
     * @var string
     */
    private $entityName = StringReplacement::class;

    /**
     * getPaginationResults
     * @return array
     */
    public function getPaginationResults()
    {
        return $this->getRepository()->getPaginationSearch();
;    }
}