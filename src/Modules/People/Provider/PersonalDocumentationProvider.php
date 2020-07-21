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
 * Date: 20/07/2020
 * Time: 12:09
 */
namespace App\Modules\People\Provider;

use App\Modules\People\Entity\PersonalDocumentation;
use App\Provider\AbstractProvider;

/**
 * Class PersonalDocumentationProvider
 * @package App\Modules\People\Provider
 * @author Craig Rayner <craig@craigrayner.com>
 */
class PersonalDocumentationProvider extends AbstractProvider
{
    /**
     * @var string
     */
    protected $entityName = PersonalDocumentation::class;
}