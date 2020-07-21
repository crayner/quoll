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
 * Date: 21/07/2020
 * Time: 10:49
 */
namespace App\Modules\People\Provider;

use App\Modules\People\Entity\ParentContact;
use App\Provider\AbstractProvider;

/**
 * Class ParentContactProvider
 * @package App\Modules\People\Provider
 * @author Craig Rayner <craig@craigrayner.com>
 */
class ParentContactProvider extends AbstractProvider
{
    /**
     * @var string
     */
    protected $entityName = ParentContact::class;
}
