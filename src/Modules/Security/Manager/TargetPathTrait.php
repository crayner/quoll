<?php
/**
 * Created by PhpStorm.
 *
 * Kookaburra
 *
 * (c) 2018 Craig Rayner <craig@craigrayner.com>
 *
 * User: craig
 * Date: 7/01/2019
 * Time: 08:25
 */
namespace App\Modules\Security\Manager;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * Trait to get (and set) the URL the user last visited before being forced to authenticate.
 */
trait TargetPathTrait
{
    /**
     * Sets the target path the user should be redirected to after authentication.
     *
     * Usually, you do not need to set this directly.
     *
     * @param SessionInterface $session
     * @param string           $providerKey The name of your firewall
     * @param string           $uri         The URI to set as the target path
     */
    private function saveTargetPath(SessionInterface $session, $providerKey, $uri)
    {
        $session->set('_security.'.$providerKey.'.target_path', $uri);
    }

    /**
     * Returns the URL (if any) the user visited that forced them to login.
     *
     * @param SessionInterface $session
     * @param string           $providerKey The name of your firewall
     *
     * @return string
     */
    private function getTargetPath(Request $request, $providerKey)
    {
        $session = $request->getSession();
        $path =  $session->get('_security.'.$providerKey.'.target_path');
        $address = $session->has('address') ? $session->get('address') : null;

        if ($request->getContentType() === 'json')
            return $path;

        if (strpos($path, 'api_') === 0)
            $path = '';

        if (strpos($path, '_') === 0)
            $path = '';

        if (null !== $address && $address !== '/registration/public/')
                $path = $address;

        return $path;
    }

    /**
     * Removes the target path from the session.
     *
     * @param SessionInterface $session
     * @param string           $providerKey The name of your firewall
     */
    private function removeTargetPath(SessionInterface $session, $providerKey)
    {
        $session->remove('_security.'.$providerKey.'.target_path');
    }
}
