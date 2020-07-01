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

use App\Modules\Security\Manager\GoogleAuthenticator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class OAuthController
 * @package App\Modules\Security\Controller
 */
class OAuthController extends AbstractController
{
    /**
     * connectGoogle
     * @param GoogleAuthenticator $manager
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @Route("/google/connect/", name="google_oauth")
     */
	public function connectGoogle(GoogleAuthenticator $manager, Request $request)
	{
	    $state = null;
	    if ($request->query->has('state'))
	        $state = $request->query->get('state');

        if ($request->query->has('q')) {
            if (null === $state)
                $state = '0:0:' . $request->query->get('q');
            else
                $state .= ':' . $request->query->get('q');
        }

        if (null !== $state && !$request->query->has('q'))
        	    $state .= ':false';

        if (null !== $state)
            $request->getSession()->set('google_state', $state);

        return $this->redirect($manager->connectUrl());
	}

    /**
     * After going to Google, you're redirected back here
     * because this is the "redirect_route" you configured
     * in config.yml
     *
     * @Route("/security/oauth2callback/", name="connect_google_check")
     * @param Request $request
     */
	public function connectCheckGoogle(Request $request)
	{
	}
}
