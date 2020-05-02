<?php
namespace App\Modules\Security\Manager;

use App\Modules\School\Entity\AcademicYear;
use App\Provider\ProviderFactory;
use App\Modules\System\Util\LocaleHelper;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Http\Logout\LogoutSuccessHandlerInterface;

/**
 * Class LogoutSuccessHandler
 * @package App\Modules\Security\Manager
 */
class LogoutSuccessHandler implements LogoutSuccessHandlerInterface
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
        $this->logger = $logger->withName('security');
	}

    /**
     * onLogoutSuccess
     * @param Request $request
     * @return RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
	public function onLogoutSuccess(Request $request)
    {
        if ($request->hasSession())
        {
            $session = $request->getSession();
            $flashBag = $session->getFlashBag()->all();
            try {
                $session->clear();
            } catch(\ErrorException $e) {
                $flashBag = null;
            }

            if (null !== $flashBag)
                $session->getFlashBag()->setAll($flashBag);

            ProviderFactory::create(AcademicYear::class)->setCurrentAcademicYear($session);
        }
		$request->setLocale($request->getDefaultLocale());

        $this->logger->info(sprintf('A user logged out from machine %s', $request->server->get('REMOTE_ADDRESS')));

        $query = [];
        if ($request->query->has('timeout') && $request->query->get('timeout') === 'true')
        {
            $query['timeout'] = 'timeout';
        }

		return new RedirectResponse($this->router->generate('home', $query));
	}
}