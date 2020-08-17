<?php
/**
 * Created by PhpStorm.
 *
 * bilby
 * (c) 2019 Craig Rayner <craig@craigrayner.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * User: craig
 * Date: 28/06/2019
 * Time: 15:01
 */
namespace App\Provider;

use App\Manager\StatusManager;
use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Class ProviderFactory
 * @package App\Provider
 * @author Craig Rayner <craig@craigrayner.com>
 */
class ProviderFactory
{
    /**
     * @var EntityManagerInterface
     */
    private static $entityManager;

    /**
     * @var StatusManager
     */
    private static StatusManager $messageManager;

    /**
     * @var AuthorizationCheckerInterface
     */
    private static $authorizationChecker;

    /**
     * @var RouterInterface
     */
    private static $router;

    /**
     * @var array
     */
    private static $instances;

    /**
     * @var ProviderFactory
     */
    private static $factory;

    /**
     * @var RequestStack
     */
    private static $stack;

    /**
     * @var ParameterBagInterface
     */
    private static $parameterBag;

    /**
     * @var LoggerInterface
     */
    private static $logger;

    /**
     * ProviderFactory constructor.
     * @param EntityManagerInterface $entityManager
     * @param StatusManager $messageManager
     * @param AuthorizationCheckerInterface $authorizationChecker
     * @param RouterInterface $router
     * @param RequestStack $stack
     * @param ParameterBagInterface $parameterBag
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        StatusManager $messageManager,
        AuthorizationCheckerInterface $authorizationChecker,
        RouterInterface $router,
        RequestStack $stack,
        ParameterBagInterface $parameterBag
    )  {
        self::$entityManager = $entityManager;
        self::$messageManager = $messageManager;
        self::$authorizationChecker = $authorizationChecker;
        self::$router = $router;
        self::$factory = $this;
        self::$stack = $stack;
        self::$parameterBag = $parameterBag;

//        file_put_contents(__DIR__ . '/../../var/log/construct.log', json_encode(debug_backtrace()));
     }

    /**
     * getProvider
     * @param string $entityName
     * @return EntityProviderInterface
     * @throws ProviderException
     */
    public function getProvider(string $entityName): EntityProviderInterface
    {
        return self::create($entityName);
    }

    /**
     * getRepository
     * @param string $entityName
     * @return ObjectRepository
     */
    public static function getRepository(string $entityName): ObjectRepository
    {
        return self::$entityManager->getRepository($entityName);
    }

    /**
     * create
     * @param string $entityName
     * @return EntityProviderInterface
     */
    public static function create(string $entityName): EntityProviderInterface
    {
        // The $entityName could be the plain name or the namespace name of the entity.
        // e.g. App\Modules\System\Entity\Module or Module
        $namespace = dirname($entityName);
        $entityName = basename($entityName);

        if (isset(self::$instances[$entityName])) {
            return self::$instances[$entityName];
        }
//        file_put_contents(__DIR__ . '/../../var/log/create.log', json_encode(debug_backtrace()));

        $providerName = str_replace('Entity', 'Provider', $namespace) . '\\' . $entityName . 'Provider';
        if (class_exists($providerName)) {
            try {
                return self::addInstance($entityName, new $providerName(self::$factory));
            } catch (\Exception $e) {
                self::getLogger()->error(sprintf('The Entity Provider for the "%s" entity is not available. The namespace used was %s', $entityName, $namespace));
//                throw $e;
            }
        }

        throw new ProviderException(sprintf('The Entity Provider for the "%s" entity is not available. The namespace used was %s', $entityName, $namespace));
    }

    /**
     * @return EntityManagerInterface
     */
    public static function getEntityManager(): EntityManagerInterface
    {
        return self::$entityManager;
    }

    /**
     * getMessageManager
     *
     * 16/08/2020 14:40
     * @return StatusManager
     */
    public static function getMessageManager(): StatusManager
    {
        return self::$messageManager;
    }

    /**
     * @return AuthorizationCheckerInterface
     */
    public static function getAuthorizationChecker(): AuthorizationCheckerInterface
    {
        return self::$authorizationChecker;
    }

    /**
     * @return RouterInterface
     */
    public static function getRouter(): RouterInterface
    {
        return self::$router;
    }

    /**
     * @var null|SessionInterface
     */
    private static ?SessionInterface $session;

    /**
     * @return SessionInterface
     */
    public static function getSession(): ?SessionInterface
    {
        if (null === self::$session)
            self::$session = self::getRequest() ? self::getRequest()->getSession() : null;

        return self::$session;
    }

    /**
     * @var Request|null
     */
    private static ?Request $request = null;

    /**
     * getRequest
     * @return Request|null
     * 15/08/2020 10:14
     */
    public static function getRequest(): ?Request
    {
        if (null === self::$request) {
            self::$request = self::getStack()->getCurrentRequest();
        }
        return self::$request;
    }

    /**
     * @return RequestStack
     */
    public static function getStack(): RequestStack
    {
        return self::$stack;
    }

    /**
     * addInstance
     * @param string $name
     * @param EntityProviderInterface $provider
     * @return EntityProviderInterface
     */
    public static function addInstance(string $name, EntityProviderInterface $provider): EntityProviderInterface
    {
        if (isset(self::$instances[$name]))
            return self::$instances[$name];

        self::$instances[$name] = $provider;

        return self::$instances[$name];
    }

    /**
     * @return ParameterBagInterface
     */
    public static function getParameterBag(): ParameterBagInterface
    {
        return self::$parameterBag;
    }

    /**
     * @return LoggerInterface
     */
    public static function getLogger(): LoggerInterface
    {
        return self::$logger;
    }

    /**
     * @param LoggerInterface $logger
     */
    public static function setLogger(LoggerInterface $logger): void
    {
        self::$logger = $logger;
    }
}