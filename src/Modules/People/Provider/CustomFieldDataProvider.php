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
 * Date: 29/07/2020
 * Time: 13:51
 */
namespace App\Modules\People\Provider;

use App\Modules\People\Entity\CustomFieldData;
use App\Provider\AbstractProvider;

/**
 * Class CustomFieldDataProvider
 * @package App\Modules\People\Provider
 * @author Craig Rayner <craig@craigrayner.com>
 */
class CustomFieldDataProvider extends AbstractProvider
{
    /**
     * @var string
     */
    protected $entityName = CustomFieldData::class;
}
