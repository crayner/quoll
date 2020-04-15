<?php
namespace App\Modules\Security\ControllerNoPrefix;

use App\Modules\Security\Manager\GoogleAuthenticator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class OAuthController
 * @package App\Modules\UserAdmin\Controller
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
