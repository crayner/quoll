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
 * Date: 12/06/2020
 * Time: 16:52
 */
namespace App\Manager\Traits;

use Symfony\Component\HttpFoundation\Request;

/**
 * Trait IPTrait
 * @package App\Manager\Traits
 */
trait IPTrait
{

    /**
     * getIPAddress
     * @param Request $request
     * @param string|null $ip
     * @return bool|mixed|string|null
     * 12/06/2020 16:51
     */
    public function getIPAddress(Request $request, string $ip = null)
    {
        if (null !== $ip)
            return $ip;

        if ($request->server->has('HTTP_CLIENT_IP'))
            return $request->server->get('HTTP_CLIENT_IP');
        else if($request->server->has('HTTP_X_FORWARDED_FOR'))
            return $request->server->get('HTTP_X_FORWARDED_FOR');
        else if($request->server->has('HTTP_X_FORWARDED'))
            return $request->server->get('HTTP_X_FORWARDED');
        else if($request->server->has('HTTP_FORWARDED_FOR'))
            return $request->server->get('HTTP_FORWARDED_FOR');
        else if($request->server->has('HTTP_FORWARDED'))
            return $request->server->get('HTTP_FORWARDED');
        else if($request->server->has('REMOTE_ADDR'))
            return $request->server->get('REMOTE_ADDR');

        return false;
    }

}