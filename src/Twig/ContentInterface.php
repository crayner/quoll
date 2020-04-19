<?php
/**
 * Created by PhpStorm.
 *
* Quoll
 * (c) 2019 Craig Rayner <craig@craigrayner.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * User: craig
 * Date: 29/07/2019
 * Time: 12:03
 */

namespace App\Twig;

/**
 * Interface ContentInterface
 * @package App\Twig
 */
interface ContentInterface
{
    /**
     * execute
     */
    public function execute(): void;

    /**
     * getAttribute
     * @param string $name
     * @return mixed
     */
    public function getAttribute(string $name);

    /**
     * hasAttribute
     * @param string $name
     * @return bool
     */
    public function hasAttribute(string $name): bool;

    /**
     * addAttribute
     * @param string $name
     * @param $content
     * @return ContentInterface
     */
    public function addAttribute(string $name, $content): ContentInterface;

}