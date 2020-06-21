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
namespace App\Modules\People\Twig\Extension;

use App\Modules\People\Entity\Person;
use App\Twig\Sidebar\Photo;
use App\Util\ImageHelper;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * Class PersonExtension
 * @package App\Modules\People\Twig\Extension
 * @author Craig Rayner <craig@craigrayner.com>
 */
class PersonExtension extends AbstractExtension
{
    /**
     * getFunctions
     * @return array|TwigFunction[]
     */
    public function getFunctions()
    {
        return [
            new TwigFunction('displayImage', [$this, 'displayImage']),
        ];
    }

    /**
     * displayImage
     * @param Person $entity
     * @param string $method
     * @param string $size
     * @param string $class
     * @return Photo
     */
    public function displayImage(Person $entity, string $method, string $size = '75', string $class = ''): Photo
    {
        return ImageHelper::displayImage($entity, $method, $size, $class);
    }
}
