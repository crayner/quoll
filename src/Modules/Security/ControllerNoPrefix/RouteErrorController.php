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
 * Date: 2/05/2020
 * Time: 10:14
 */

namespace App\Modules\Security\ControllerNoPrefix;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class RouteErrorController
 * @package App\Modules\Security\Controller
 */
class RouteErrorController extends AbstractController
{
    /**
     * routeAction
     * @param string $route
     * @Route("/route/{route}/error/", name="role_route_error")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function routeAction(string $route)
    {
        $url = base64_decode($route);
        $url = str_replace($this->getParameter('absoluteURL'), '', $url);

        $router = $this->get('router');
        $route = $router->match($url)['_route'];
        return $this->render('security/role_route_error.html.twig',
            [
                'route' => $route,
            ]
        );
    }
}