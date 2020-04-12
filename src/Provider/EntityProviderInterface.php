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
 * Date: 1/07/2019
 * Time: 09:38
 */

namespace App\Provider;

use App\Manager\EntityInterface;

interface EntityProviderInterface
{
    /**
     * find
     * @param $id
     * @return EntityInterface|null
     * @throws \Exception
     */
    public function find($id): ?EntityInterface;
}