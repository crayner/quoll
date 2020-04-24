<?php
/**
 * Created by PhpStorm.
 *
 * kookaburra
 * (c) 2019 Craig Rayner <craig@craigrayner.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * User: craig
 * Date: 14/09/2019
 * Time: 14:50
 */

namespace App\Manager;


interface PaginationInterface
{
    /**
     * execute
     *
     * Place all the logic to build your page here.
     * @return PaginationInterface
     */
    public function execute(): PaginationInterface;
}