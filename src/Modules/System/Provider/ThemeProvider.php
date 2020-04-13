<?php
/**
 * Created by PhpStorm.
 *
 * bilby
 * (c) 2019 Craig Rayner <craig@craigrayner.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * User: craig
 * Date: 28/06/2019
 * Time: 15:00
 */

namespace App\Modules\System\Provider;

use App\Modules\System\Entity\Theme;
use App\Manager\Traits\EntityTrait;

class ThemeProvider implements EntityProviderInterface
{
    use EntityTrait;

    /**
     * @var string
     */
    private $entityName = Theme::class;
}