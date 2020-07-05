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
 * Date: 5/04/2020
 * Time: 09:26
 */

namespace App\Manager;

/**
 * Interface SpecialInterface
 * @package App\Manager
 */
interface SpecialInterface
{
    /**
     * getName
     * @return string
     */
    public function getName(): string;

    /**
     * toArray
     * @return array
     */
    public function toArray(): array;
}