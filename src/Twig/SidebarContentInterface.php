<?php
/**
 * Created by PhpStorm.
 *
 * Project: Kookaburra
 * Build: Quoll
 *
 * (c) 2019 Craig Rayner <craig@craigrayner.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * User: craig
 * Date: 8/11/2019
 * Time: 11:51
 */

namespace App\Twig;


interface SidebarContentInterface
{
    /**
     * render
     * @param array $options
     * @return string
     */
    public function render(array $options): string;

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
