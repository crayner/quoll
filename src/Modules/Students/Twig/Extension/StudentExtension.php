<?php
/**
 * Created by PhpStorm.
 *
 * Quoll
 * (c) 2020 Craig Rayner <craig@craigrayner.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * User: craig
 * Date: 18/06/2020
 * Time: 11:03
 */
namespace App\Modules\Students\Twig\Extension;

use App\Modules\Students\Manager\StudentManager;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * Class StudentExtension
 * @package App\Modules\Students\Twig\Extension
 * @author Craig Rayner <craig@craigrayner.com>
 */
class StudentExtension extends AbstractExtension
{
    /**
     * @var StudentManager
     */
    private $manager;

    /**
     * StudentExtension constructor.
     * @param StudentManager $manager
     */
    public function __construct(StudentManager $manager)
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
            new TwigFunction('getAlertBar', [$this->manager, 'getAlertBar']),
        ];
    }
}
