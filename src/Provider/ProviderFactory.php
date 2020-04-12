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

use App\Manager\MessageManager;
use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class ProviderFactory
{
    /**
     * @var EntityManagerInterface
     */
    private static $entityManager;

    /**
     * @var MessageManager
     */
    private static $messageManager;

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
     * ProviderFactory constructor.
     * @param EntityManagerInterface $entityManager
     * @param MessageManager $messageManager
     * @param AuthorizationCheckerInterface $authorizationChecker
     * @param RouterInterface $router
     * @param RequestStack $stack
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        MessageManager $messageManager,
        AuthorizationCheckerInterface $authorizationChecker,
        RouterInterface $router,
        RequestStack $stack
    )  {
        self::$entityManager = $entityManager;
        self::$messageManager = $messageManager;
        self::$authorizationChecker = $authorizationChecker;
        self::$router = $router;
        self::$factory = $this;
        self::$stack = $stack;
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
     * @return ObjectRepository
     * @throws ProviderException
     */
    public static function create(string $entityName): EntityProviderInterface
    {
        //The $entityName could be the plain name or the namespace name of the entity.
        // e.g. Kookaburra\SystemAdmin\Entity\Module or Module

        $namespace = dirname($entityName);
        $entityName = basename($entityName);

        if (isset(self::$instances[$entityName])) {
            return self::$instances[$entityName];
        }

        $providerName = str_replace('Entity', 'Provider', $namespace) . '\\' . $entityName . 'Provider';
        if (class_exists($providerName)) {
            return self::addInstance($entityName,  new $providerName(self::$factory));
        }

        if (self::$stack->getParentRequest()) {
            if (self::$stack->getParentRequest()->attributes->has('module') && false !== self::$stack->getParentRequest()->attributes->get('module')) {
                $module = self::$stack->getParentRequest()->attributes->get('module');
                $providerName = '\Kookaburra\\' . str_replace(' ', '', $module->getName()) . '\Provider\\' . $entityName . 'Provider';
                if (class_exists($providerName)) {
                    return self::addInstance($entityName, new $providerName(self::$factory));
                }
            }
        }

        if (self::$stack->getCurrentRequest()) {
            if (self::$stack->getCurrentRequest()->attributes->has('module') && false !== self::$stack->getCurrentRequest()->attributes->get('module')) {
                $module = self::$stack->getCurrentRequest()->attributes->get('module');
                $providerName = '\Kookaburra\\' . str_replace(' ', '', $module->getName()) . '\Provider\\' . $entityName . 'Provider';
                if (class_exists($providerName)) {
                    return self::addInstance($entityName, new $providerName(self::$factory));
                }
            }
        }

        throw new ProviderException(sprintf('The Entity Provider for the "%s" entity is not available.', $entityName));
    }

    /**
     * @return EntityManagerInterface
     */
    public static function getEntityManager(): EntityManagerInterface
    {
        return self::$entityManager;
    }

    /**
     * @return MessageManager
     */
    public static function getMessageManager(): MessageManager
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
    private static $session;

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
    private static $request;

    /**
     * @return SessionInterface
     */
    public static function getRequest(): ?Request
    {
        if (null === self::$request)
            self::$request = self::getStack()->getCurrentRequest();

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
}