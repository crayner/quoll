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
 * Date: 13/11/2019
 * Time: 11:21
 */

namespace App\Twig\Extension;

use App\Manager\PageManager;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * Class ScriptExtension
 * @package App\Twig\Extension
 */
class ScriptExtension extends AbstractExtension
{
    /**
     * @var PageManager
     */
    private $manager;

    /**
     * ScriptExtension constructor.
     * @param PageManager $manager
     */
    public function __construct(PageManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * getFunctions
     * @return array|TwigFunction[]
     */
    public function getFunctions()
    {
        return [
            new TwigFunction('addPageScript', [$this->manager, 'addPageScript']),
        ];
    }
}