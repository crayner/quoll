<?php
/**
 * Created by PhpStorm.
 *
 * quoll
 * (c) 2020 Craig Rayner <craig@craigrayner.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * User: craig
 * Date: 14/04/2020
 * Time: 09:05
 */

namespace App\Controller;

use App\Manager\PageManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * Class AbstractPageController
 * @package App\Controller
 */
abstract class AbstractPageController extends AbstractController
{
    /**
     * getSubscribedServices
     * @return array
     */
    public static function getSubscribedServices()
    {
        return (array_merge(parent::getSubscribedServices(), ['page_manager' => PageManager::class]));
    }

    /**
     * getPageManager
     * @return PageManager
     */
    protected function getPageManager(): PageManager
    {
        return $this->get('page_manager');
    }
}