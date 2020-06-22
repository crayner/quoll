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
 * Date: 6/04/2020
 * Time: 09:42
 */

namespace App\Manager;

/**
 * Interface PaginationSortInterface
 * @package App\Manager
 */
interface PaginationSortInterface
{
    /**
     * getDetails
     * @return array
     */
    public function getDetails(): array;

    /**
     * setDetails
     * @param array $details
     */
    public function setDetails(array $details);
}