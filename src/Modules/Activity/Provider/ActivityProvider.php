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
 * Date: 11/02/2020
 * Time: 14:24
 */
namespace App\Modules\Activity\Provider;

use App\Provider\AbstractProvider;
use App\Modules\Activity\Entity\Activity;

/**
 * Class ActivityProvider
 * @package App\Modules\Activity\Provider
 * @author Craig Rayner <craig@craigrayner.com>
 */
class ActivityProvider extends AbstractProvider
{
    /**
     * @var string
     */
    protected $entityName = Activity::class;
}