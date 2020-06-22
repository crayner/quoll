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
 * Date: 1/06/2020
 * Time: 13:08
 */
namespace App\Modules\Curriculum\Provider;

use App\Modules\Curriculum\Entity\Rubric;
use App\Provider\AbstractProvider;

/**
 * Class RubricProvider
 * @package App\Modules\Curriculum\Provider
 * @author Craig Rayner <craig@craigrayner.com>
 */
class RubricProvider extends AbstractProvider
{
    /**
     * @var string
     */
    protected $entityName = Rubric::class;
}