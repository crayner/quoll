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
 * Date: 17/05/2020
 * Time: 12:52
 */
namespace App\Modules\People\Provider;

use App\Modules\People\Entity\CustomField;
use App\Provider\AbstractProvider;

/**
 * Class CustomFieldProvider
 * @package App\Modules\People\Provider
 */
class CustomFieldProvider extends AbstractProvider
{
    /**
     * @var string
     */
    protected $entityName = CustomField::class;
}