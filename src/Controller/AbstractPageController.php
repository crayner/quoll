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
 * Date: 14/04/2020
 * Time: 09:05
 */

namespace App\Controller;

use App\Manager\PageManager;
use MongoDB\Driver\Exception\InvalidArgumentException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;

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


    /**
     * Adds a flash message to the current session for type.
     *
     * @param string $type
     * @param string|array $message
     */
    protected function addFlash(string $type, $message): void
    {
        if (!$this->container->has('session')) {
            throw new \LogicException('You can not use the addFlash method if sessions are disabled. Enable them in "config/packages/framework.yaml".');
        }

        if (!((is_array($message) && count($message) === 3) || is_string($message))) {
            throw new \InvalidArgumentException('The message must be a string or a translation array of 3 parts [id, [params], domain] to be correctly handled by the flash display logic.');
        }

        $this->container->get('session')->getFlashBag()->add($type, $message);
    }

    /**
     * getRequest
     * @return Request
     */
    protected function getRequest(): Request
    {
        return $this->getPageManager()->getRequest();
    }
}