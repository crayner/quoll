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
 * Date: 10/11/2019
 * Time: 09:34
 */
namespace App\Modules\Department\Twig;

use App\Twig\SidebarContentInterface;
use App\Twig\SidebarContentTrait;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

/**
 * Class MyClasses
 * @package App\Modules\Department\Twig
 * @author Craig Rayner <craig@craigrayner.com>
 */
class MyClasses implements SidebarContentInterface
{
    use SidebarContentTrait;

    /**
     * @var array
     */
    private array $classes = [];

    /**
     * @var string
     */
    private string $name = 'My Classes';

    /**
     * @var string
     */
    private string $position = 'top';

    /**
     * MyClasses constructor.
     * @param array $classes
     */
    public function __construct(array $classes = [])
    {
        $this->classes = $classes;
    }

    /**
     * render
     * @param array $options
     * @return string
     * 4/06/2020 14:23
     */
    public function render(array $options): string
    {
        try {
            return $this->setContent($this->getTwig()->render('department/sidebar/my_classes.html.twig', ['classes' => $this]))->getContent();
        } catch (LoaderError | RuntimeError | SyntaxError $e) {}
        return '';
    }

    /**
     * @return array
     */
    public function getClasses(): array
    {
        return $this->classes ?: [];
    }

    /**
     * Classes.
     *
     * @param array $classes
     * @return MyClasses
     */
    public function setClasses(array $classes): MyClasses
    {
        $this->classes = $classes;
        return $this;
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