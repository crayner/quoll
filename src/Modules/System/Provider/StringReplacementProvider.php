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
 * Date: 9/08/2019
 * Time: 13:21
 */

namespace App\Modules\System\Provider;

use App\Modules\System\Entity\StringReplacement;
use App\Provider\AbstractProvider;

/**
 * Class StringReplacementProvider
 * @package App\Modules\System\Provider
 */
class StringReplacementProvider extends AbstractProvider
{

    /**
     * @var string
     */
    protected $entityName = StringReplacement::class;

    /**
     * getPaginationResults
     * @return array
     */
    public function getPaginationResults()
    {
        return $this->getRepository()->getPaginationSearch();
;    }
}