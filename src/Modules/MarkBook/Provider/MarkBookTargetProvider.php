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
 * Date: 1/06/2020
 * Time: 11:46
 */
namespace App\Modules\MarkBook\Provider;

use App\Modules\MarkBook\Entity\MarkBookTarget;
use App\Provider\AbstractProvider;

/**
 * Class MarkBookTargetProvider
 * @package App\Modules\MarkBook\Provider
 * @author Craig Rayner <craig@craigrayner.com>
 */
class MarkBookTargetProvider extends AbstractProvider
{
    /**
     * @var string
     */
    protected $entityName = MarkBookTarget::class;
}