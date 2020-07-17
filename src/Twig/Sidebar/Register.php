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
 * Date: 16/02/2020
 * Time: 16:06
 */

namespace App\Twig\Sidebar;


use App\Twig\SidebarContentInterface;
use App\Twig\SidebarContentTrait;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class Register implements SidebarContentInterface
{
    use SidebarContentTrait;

    /**
     * @var string
     */
    private $name = 'Welcome';

    /**
     * render
     * @param array $options
     * @return string
     */
    public function render(array $options): string
    {
        try {
            return $this->getTwig()->render('home/register.html.twig');
        } catch (LoaderError | RuntimeError | SyntaxError $e) {
            return '';
        }
    }

    /**
     * toArray
     * @return array
     */
    public function toArray(): array
    {
        return ['content' => $this->render([])];
    }
}