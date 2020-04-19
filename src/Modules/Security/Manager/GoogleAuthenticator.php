<?php
namespace App\Modules\Security\Manager;

use App\Manager\MessageManager;
use App\Modules\People\Entity\Person;
use App\Modules\System\Entity\Setting;
use App\Modules\System\Provider\SettingProvider;
use App\Provider\ProviderFactory;
use App\Util\EntityHelper;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Guard\AuthenticatorInterface;
use Symfony\Component\Yaml\Yaml;

/**
 * Class GoogleAuthenticator
 * @package App\Modules\Security\Manager
 */
class GoogleAuthenticator implements AuthenticatorInterface
{
    use TargetPathTrait;
    use AuthenticatorTrait;

	/**
	 * @var EntityManagerInterface
	 */
	private $em;

	/**
	 * @var RouterInterface
	 */
	private $router;

	/**
	 * @var MessageManager
	 */
	private $messageManager;

	/**
	 * @var SettingProvider
	 */
	private $settingManager;

	/**
	 * @var LoggerInterface
	 */
	private $logger;

	/**
	 * @var Object
	 */
	private $google_user;

    /**
     * @var SecurityUserProvider
     */
    private $provider;

    /**
     * @var Request
     */
    private $request;

    /**
     * GoogleAuthenticator constructor.
     * @param RouterInterface $router
     * @param MessageManager $messageManager
     * @param LoggerInterface $logger
     * @param SecurityUserProvider $provider
     */
	public function __construct(
	    RouterInterface $router,
        MessageManager $messageManager,
        LoggerInterface $logger,
        SecurityUserProvider $provider,
        RequestStack $request,
        ProviderFactory $factory
    ) {
        $this->settingManager = ProviderFactory::create(Setting::class);
		$this->em = $this->settingManager->getEntityManager();
		$this->router = $router;
		$this->messageManager = $messageManager;
        $this->logger = $logger;
        $this->provider = $provider;
        $this->request = $request->getCurrentRequest();
        if ($this->readGoogleOAuth() !== false) {
            $this->getClient();
            $this->getClient()->setLogger($logger);
        }
	}



    /**
     * getCredentials
     * @param Request $request
     * @return array|mixed
     * @throws \Google_Exception
     */
	public function getCredentials(Request $request)
	{
		$this->logger->debug("Google Authentication: Google authentication attempted.");

		return $this->fetchAccessToken($this->getGoogleClient());
	}

    /**
     * @param mixed $credentials
     * @param UserProviderInterface $userProvider
     * @return UserInterface
     */
    public function getUser($credentials, UserProviderInterface $userProvider): UserInterface
    {
        $refreshToken = isset($credentials['refresh_token']) ? $credentials['refresh_token'] : null;

        if (empty($user)) {
            // 2) do we have a matching user by email?
            $user = $userProvider->loadUserByUsername($this->getGoogleUser()->getEmail());

            if (!empty($refreshToken)) {
                $userProvider->getUser()->setGoogleAPIRefreshToken($refreshToken);

                $this->em->persist($userProvider->getUser());
                $this->em->flush();
            }
        }

		// 3) Maybe you just want to "register" them by creating
		// a UserProvider object
//		$user->setGoogleId($googleUser->getId());
//		$this->em->persist($user);
//		$this->em->flush();
        $this->logger->debug(sprintf('Google Authentication: The user "%s" authenticated using Google.', $this->getGoogleUser()->getName()));

        $this->setAccessToken($credentials);

		return $user;
	}

	private $token;

    /**
     * getGoogleClient
     * @return mixed
     * @throws \Google_Exception
     */
    private function getGoogleClient()
    {
        // @todo For some reason the Request Query is not set correctly on some servers.  When the setting causing this is identified we can do something about this.
        if (empty($code = $this->getRequest()->query->get('code'))) {
            $uri = 'http://' . $this->getRequest()->getHttpHost() . $this->getRequest()->getRequestUri();
            parse_str(parse_url($uri, PHP_URL_QUERY), $query);
            $code = $query['code'];
        }

        $this->token = $this->getClient()->fetchAccessTokenWithAuthCode($code);// to get code
        $this->getClient()->setAccessToken($this->token); // to get access token by setting of $code
        $service = new \Google_Service_Oauth2($this->getClient());
        $this->google_user = $service->userinfo->get();   // to get user detail by using access token
        return $this->google_user;
    }

    /**
     * onAuthenticationFailure
     * @param Request $request
     * @param AuthenticationException $exception
     * @return RedirectResponse|Response|null
     */
	public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
	{
		$this->logger->notice("Google Authentication Failure: ".  $exception->getMessage());
        LogProvider::setLog($request->getSession()->get('gibbonAcademicYearIDCurrent'), null, null, 'Google Login - Failed', ['username' => $this->google_user, 'message' => $exception->getMessage()], $request->server->get('REMOTE_ADDR'));

        $this->authenticationFailure($request->query->all());

        if ($targetPath = $this->getTargetPath($request, 'main'))
            return new RedirectResponse($targetPath);

		return new RedirectResponse('/');
	}

    /**
     * @param Request $request
     * @param TokenInterface $token
     * @param string $providerKey
     * @return null|RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
	{
		$user = $token->getUser();
		$this->logger->notice("Google Authentication: UserProvider #" . $user->getId() . " (" . $user->getEmail() . ") The user authenticated via Google.");

		$this->getProvider()->setSecurityUser($user);

		$user = $this->getProvider()->getUser();

		if (!$user->isCanLogin())
		    return $this->authenticationFailure(['loginReturn' => 'fail2']);

        if ($user->isPasswordForceReset())
            $request->getSession()->set('passwordForceReset', 'Y');

		if (!$user->getPrimaryRole())
            return $this->authenticationFailure(['loginReturn' => 'fail2']);

		if ($user->getFailCount() >= 3 && $user->isLastFailTimestampTooOld()) {
		    if ($user->getFailCount() === 3) {
		        $user->incFailCount();
		        $user->setLastFailTimestamp(new \DateTime('now'));
		        $user->setLastFailIPAddress($request->server->get('REMOTE_ADDR'));
                $provider = EntityHelper::getProviderFactory()->create(Person::class);
                $provider->setEntity($user)->saveEntity();
            }
            $this->logger->warning('Too many failed login (Google)');
            return $this->authenticationFailure(['loginReturn' => 'fail6']);
        }

		if (null !== $user->getLocale())
			$request->setLocale($user->getLocale());

        if (empty($user->getGoogleAPIRefreshToken()) && empty($this->getClient()->getAccessToken()['refresh_token'])) {
            $this->getClient()->setApprovalPrompt('force');
            $targetPath = $this->getClient()->createAuthUrl();
            return new RedirectResponse($targetPath);
        }

        $user = $this->createUserSession($user, $request->getSession());
        $this->setAcademicYear($request->getSession(), 0);

        $q = null;
        if ($request->getSession()->has('google_state')) {
            $state = $request->getSession()->get('google_state');
            list($AcademicYearID, $i18nID, $q) = explode(':', $state);
            if ($q === 'false')
                $q = null;
            $request->getSession()->forget('google_state');
            if (($response = $this->checkAcademicYear($user, $request->getSession(), intval($AcademicYearID))) instanceof Response)
                return $response;

            $this->setLanguage($request, $i18nID);
            if (null !== $q)
                if (strpos($q, '.php') === false) {
                    return new RedirectResponse($q);
                } else
                    return new RedirectResponse('/?q=' . $q);
        }


        if ($targetPath = $this->getTargetPath($request, $providerKey))
            return new RedirectResponse($targetPath);
        return new RedirectResponse($this->getLoginUrl());
	}

    /**
     * @param Request $request
     * @param AuthenticationException|null $authException
     * @return RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function start(Request $request, AuthenticationException $authException = null)
	{
		return new RedirectResponse($this->router->generate('login'));
	}

    /**
     * createAuthenticatedToken
     * @param UserInterface $user
     * @param string $providerKey
     * @return UsernamePasswordToken|\Symfony\Component\Security\Guard\Token\GuardTokenInterface
     */
	public function createAuthenticatedToken(UserInterface $user, $providerKey)
	{
		return new UsernamePasswordToken(
			$user,
			$user->getPassword(),
			$providerKey,
			$user->getRoles()
		);
	}

    /**
     * checkCredentials
     * @param mixed $credentials
     * @param UserInterface $user
     * @return bool
     * @throws \Google_Exception
     */
	public function checkCredentials($credentials, UserInterface $user)
	{
        $service = new \Google_Service_Oauth2($this->getClient());
        $this->google_user = $service->userinfo->get();   // to get user detail by using access token
        if ($this->google_user->getEmail() !== $user->getEmail())
            return false;
		return true;
	}

	/**
	 * @param Request $request
	 *
	 * @return bool
	 */
	public function supports(Request $request): bool
	{
	    return strpos($request->getPathInfo(), 'security/oauth2callback') !== false && $request->query->has('code');
	}

    /**
     * fetchAccessToken
     * @return array
     * @throws \Google_Exception
     */
	protected function fetchAccessToken()
	{
	    return $this->getClient()->getAccessToken();
	}

	/**
	 * @return bool
	 */
	public function supportsRememberMe()
	{
		return false;
	}

    /**
     * getLoginUrl
     * @return string
     */
    protected function getLoginUrl()
    {
        return $this->router->generate('login');
    }

    /**
     * @return SettingProvider
     */
    public function getSettingManager(): SettingProvider
    {
        return $this->settingManager;
    }

    /**
     * @var array
     */
    private $clientSecrets = [];

    /**
     * getClientSecrets
     * @return array
     * @throws \Exception
     */
    public function getClientSecrets(): array
    {
        if (! empty($this->clientSecrets))
            return $this->clientSecrets;
        $config = Yaml::parse(file_get_contents($this->getProjectDir() . '/config/packages/quoll.yaml'));
        $clientSecrets = [];
        $clientSecrets['web']['client_id'] = $config['parameters']['google_client_id'];
        $clientSecrets['web']['project_id'] = $config['parameters']['google_api_key'];
        $clientSecrets['web']['auth_uri'] = 'https://accounts.google.com/o/oauth2/auth';
        $clientSecrets['web']['token_uri'] = 'https://www.googleapis.com/oauth2/v3/token';
        $clientSecrets['web']['auth_provider_x509_cert_url'] = 'https://www.googleapis.com/oauth2/v1/certs';
        $clientSecrets['web']['client_secret'] = $config['parameters']['google_client_secret'];;
        $clientSecrets['web']['redirect_uris'] = [$this->getSettingManager()->getSettingByScopeAsString('System', 'googleRedirectUri')];
        return $this->clientSecrets = $clientSecrets;
    }

    /**
     * getConfig
     * @return array
     * @throws \Exception
     */
    public function getConfig(): array {
        return [
            'application_name' => $this->getSettingManager()->getSettingByScopeAsString('System', 'googleClientName'),
            'access_type' => 'offline',
            'include_granted_scopes' => true,
            'developer_key' => $this->getSettingManager()->getSettingByScopeAsString('System', 'googleDeveloperKey'),
        ];
    }

    /**
     * connectUrl
     */
    public function connectUrl(): string
    {
        return $this->getClient()->createAuthUrl();
    }

    /**
     * @var \Google_Client|null
     */
    private $client;

    /**
     * oogle_Exception
     */
    public function getClient(): \Google_Client
    {
        if (! empty($this->client))
            return $this->client;
        $client = new \Google_Client($this->getConfig());
        $client->setAuthConfig($this->getClientSecrets());
        $client->addScope(['email', 'profile', 'https://www.googleapis.com/auth/calendar']);
        $client->setRedirectUri($this->getRouter()->generate('connect_google_check',[],UrlGeneratorInterface::ABSOLUTE_URL));
        return $this->client = $client;
    }

    /**
     * @return RouterInterface
     */
    public function getRouter(): RouterInterface
    {
        return $this->router;
    }

    /**
     * isAuthenticated
     * @return bool
     * @throws \Google_Exception
     */
    public function isAuthenticated(): bool
    {
        if ($this->getAccessToken())
        {
            $this->getClient()->setAccessToken(json_encode($this->getAccessToken()));
            if ($this->getClient()->isAccessTokenExpired()) { //Need to refresh the token
                //Re-establish $client
                if (empty($this->getUser($this->getAccessToken(), $this->getProvider())->getGoogleAPIAccessToken())) {
                    $this->getSettingManager()->getMessageManager()->add('danger', 'Your request failed due to a database error.');
                    return false;
                } else {
                    $this->getClient()->refreshToken($this->getUser($this->getAccessToken(), $this->getProvider())->getGoogleAPIRefreshToken());
                    $this->setAccessToken($this->getClient()->getAccessToken());
                    return true;
                }
            } else {
                $this->getClient()->fetchAccessTokenWithRefreshToken($this->getClient()->getAccessToken());
                return true;
            }
        }

        return false;
    }

    /**
     * getAccessToken
     * @return mixed
     */
    public function getAccessToken()
    {
        $googleAPIAccessToken = $this->getSettingManager()->getSession()->get('googleAPIAccessToken', false);
        return $googleAPIAccessToken;
    }

    /**
     * getAccessToken
     * @return mixed
     */
    public function setAccessToken($googleAPIAccessToken)
    {
        $this->getRequest()->getSession()->set('googleAPIAccessToken', $googleAPIAccessToken);
        return $this;
    }

    /**
     * getProvider
     * @return SecurityUserProvider
     */
    public function getProvider(): SecurityUserProvider
    {
        return $this->provider;
    }

    /**
     * @return Object
     */
    public function getGoogleUser(): Object
    {
        return $this->google_user;
    }

    /**
     * readGoogleOAuth
     * @return bool|string
     */
    private function readGoogleOAuth()
    {
        $file = realpath($this->getProjectDir() . '/config/google_oauth.json');
        if (!realpath($this->getProjectDir() . '/config/packages/kookaburra.json') || ! $file)
            return false;

        try {
            $this->clientSecrets = json_decode(file_get_contents($file), true);
        } catch (\Exception $e) {
            return false;
        }
        return true;
    }

    /**
     * getProjectDir
     * @return string
     */
    private function getProjectDir(): string
    {
        return realpath(__DIR__ . '/../../../..');
        return realpath(__DIR__ . '/../../../..');
    }

    /**
     * @return Request
     */
    public function getRequest(): Request
    {
        return $this->request;
    }
}
