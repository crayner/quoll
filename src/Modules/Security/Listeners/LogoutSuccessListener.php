<?php
namespace App\Modules\Security\Listeners;

use App\Modules\School\Entity\AcademicYear;
use App\Provider\ProviderFactory;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\SecurityBundle\EventListener\FirewallListener;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Http\Event\LogoutEvent;

/**
 * Class LogoutSuccessListener
 * @package App\Modules\Security\Manager
 * @author Craig Rayner <craig@craigrayner.com>
 */
class LogoutSuccessListener
{
	/**
	 * @var RouterInterface
	 */
	private $router;

    /**
     * @var LoggerInterface
     */
	private $logger;

    /**
     * LogoutSuccessHandler constructor.
     * @param RouterInterface $router
     * @param LoggerInterface $logger
     * @param string $locale
     */
	public function __construct(RouterInterface $router, LoggerInterface $logger)
	{
		$this->router = $router;
        $this->logger = $logger;
	}


    /**
     * onLogoutSuccess
     * @param LogoutEvent $event
     * @return void
     */
	public function onSymfonyComponentSecurityHttpEventLogoutEvent(LogoutEvent $event)
    {
        $request = $event->getRequest();
        if ($request->hasSession())
        {
            $session = $request->getSession();
            try {
                $session->invalidate();
            } catch(\ErrorException $e) {
                $flashBag = null;
            }
        }
		$request->setLocale($request->getDefaultLocale());
        $this->logger->info(sprintf('A user logged out from machine %s', $request->server->get('REMOTE_ADDRESS')));

        $query = [];
        if ($request->query->has('timeout') && $request->query->get('timeout') === 'true')
        {
            $query['timeout'] = 'timeout';
        }

		$event->setResponse(new RedirectResponse($this->router->generate('home', $query)));
	}
}