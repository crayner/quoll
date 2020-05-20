<?php
/**
 * Created by PhpStorm.
 *
* Quoll
 *
 * (c) 2018 Craig Rayner <craig@craigrayner.com>
 *
 * User: craig
 * Date: 19/12/2018
 * Time: 12:17
 */
namespace App\Modules\Security\Util;

use App\Modules\People\Entity\Person;
use App\Modules\People\Util\UserHelper;
use App\Modules\Security\Manager\SecurityUser;
use App\Modules\System\Entity\Action;
use App\Modules\System\Entity\Module;
use App\Modules\System\Entity\Setting;
use App\Modules\System\Provider\ActionProvider ;
use App\Modules\System\Provider\ModuleProvider ;
use App\Provider\ProviderFactory;
use Doctrine\DBAL\Driver\PDOException;
use Doctrine\DBAL\Exception\DriverException;
use Psr\Log\LoggerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationCredentialsNotFoundException;
use Symfony\Component\Security\Core\Role\RoleHierarchyInterface;

/**
 * Class SecurityHelper
 * @package App\Util
 */
class SecurityHelper
{

    /**
     * @var LoggerInterface
     */
    private static $logger;

    /**
     * @var AuthorizationCheckerInterface
     */
    private static $checker;

    /**
     * @var Module|null
     */
    private static $module;

    /**
     * @var Action|null
     */
    private static $action;

    /**
     * @var RoleHierarchyInterface
     */
    private static $hierarchy;

    /**
     * @var TokenStorageInterface
     */
    private static $storage;

    /**
     * SecurityHelper constructor.
     * @param LoggerInterface $logger
     * @param AuthorizationCheckerInterface $checker
     * @param TokenStorageInterface $storage
     * @param RoleHierarchyInterface $hierarchy
     */
    public function __construct(
        LoggerInterface $logger,
        AuthorizationCheckerInterface $checker,
        TokenStorageInterface $storage,
        RoleHierarchyInterface $hierarchy
    ) {
        self::$logger = $logger;
        self::$checker = $checker;
        self::$hierarchy = $hierarchy;
        self::$storage = $storage;
    }

    /**
     * @var array
     */
    private static $highestGroupedActionList = [];

    /**
     * getHighestGroupedAction
     * @param string $route
     * @return bool|mixed
     */
    public static function getHighestGroupedAction(string $route)
    {
        $module = self::checkModuleReady($route);
        if (null === $module)
            return false;
        if (isset(self::$highestGroupedActionList[$route]))
            return self::$highestGroupedActionList[$route];
        if ($user = UserHelper::getCurrentUser() === null)
            return self::$highestGroupedActionList[$route] = false;
        $result = ProviderFactory::create(Action::class)->getRepository()->findHighestGroupedAction(self::getActionName($route), $module);
        return self::$highestGroupedActionList[$route] = $result ? $result['name'] : false;
    }

    /**
     * @var array
     */
    private static $checkModuleReadyList = [];

    /**
     * checkModuleReady
     * @param string $route
     * @return \App\Manager\EntityInterface|bool
     */
    public static function checkModuleReady(string $route)
    {
        if (isset(self::$checkModuleReadyList[$route]))
            return self::$checkModuleReadyList[$route];
        try {
            return self::$checkModuleReadyList[$route] = ProviderFactory::create(Module::class)->findOneBy(['name' => self::getModuleName($route), 'active' => 'Y']);
        } catch (PDOException | \PDOException $e) {
        }

        return self::$checkModuleReadyList[$route] = false;
    }

    /**
     * checkModuleRouteReady
     * @param string $route
     * @return Module|bool
     */
    public static function checkModuleRouteReady(string $route)
    {
        if (self::getModuleFromRoute($route) instanceof Module)
            return true;
        return false;
    }

    /**
     * getModuleFromRoute
     * @param string|null $route
     * @return Module|null
     */
    public static function getModuleFromRoute(?string $route): ?Module
    {
        if (is_null($route))
            return null;
        self::getActionFromRoute($route);

        return self::$module ?: null;
    }

    /**
     * getModuleName
     * @param string $route
     * @return bool|string
     */
    public static function getModuleName(string $route)
    {
        if (strpos($route, '__'))
        {
            $module = explode('__', $route);
            $module = explode('_', $module[0]);
            foreach($module as $q=>$w)
                $module[$q] = ucfirst($w);
            return implode(' ', $module);
        }
        return substr(substr($route, 9), 0, strpos(substr($route, 9), '/'));
    }

    /**
     * getActionName
     * @param $route
     * @return bool|string
     */
    public static function getActionName($route)
    {
        return substr($route, (10 + strlen(self::getModuleName($route))));
    }

    /**
     * getModuleNameFromRoute
     * @param string $route
     * @return mixed
     */
    public static function getModuleNameFromRoute(string $route)
    {
        if (!self::$module instanceof Module) {
            self::getActionFromRoute($route);
        }
        return self::$module ? self::$module->getName() : '';
    }

    /**
     * @var array
     */
    private static $routeActions = [];

    /**
     * getActionFromRoute
     * @param string $route
     * @return Action|null
     */
    public static function getActionFromRoute(string $route): ?Action
    {
        if (!key_exists($route, self::$routeActions)) {
            try {
                self::$action = ProviderFactory::getRepository(Action::class)->findOneByRoute($route);
                self::$routeActions[$route] = self::$action;
            } catch (\PDOException | PDOException | DriverException $e) {
                return null;
            }
        } else {
            self::$action = self::$routeActions[$route];
        }
        self::$module = self::$action instanceof Action ? self::$action->getModule() : null;
        return self::$routeActions[$route];
    }

    /**
     * getActionNameFromRoute
     * @param string $route
     * @return string
     */
    public static function getActionNameFromRoute(string $route): string
    {
        return self::isActionAccessible($route) ? self::$action->getName() : '';
    }

    /**
     * isActionAccessible
     * @param string $route
     * @param string $sub
     * @param LoggerInterface|null $logger
     * @return bool
     */
    public static function isActionAccessible(string $route, string $sub = '%', ?LoggerInterface $logger = null): bool
    {
        if (null !== $logger)
            self::$logger = $logger;
        if (self::checkActionReady($route) === false) {
            self::$logger->warning(sprintf('No action was linked to the route "%s"', $route));
            return false;
        }

        return self::isActionAccessibleToUser(self::$module,self::$action, $route, $sub);
    }

    /**
     * checkActionReady
     * @param string $route
     * @return bool
     */
    public static function checkActionReady(string $route): bool
    {
        return self::getActionFromRoute($route) instanceof Action;
    }

    /**
     * isActionAccessibleToRole
     * @param Module $module
     * @param string $action
     * @param string $route
     * @param string $sub
     * @return bool
     */
    private static function isActionAccessibleToUser(Module $module, Action $action, string $route, string $sub)
    {
        if (UserHelper::getCurrentUser() instanceof Person) {
            //Check user has a current role set
            if (! empty(UserHelper::getCurrentUser()->getPrimaryRole())) {
                //Check module ready
                if ($module instanceof Module && $action instanceof Action) {
                    //Check current role has access rights to the current action.
                    return self::$checker->isGranted($action->getRole()) === VoterInterface::ACCESS_GRANTED;
                } else {
                    self::$logger->warning(sprintf('No module or action was linked to the route "%s"', $route));
                }
            } else {
                self::$logger->debug(sprintf('The user does not have a valid Primary Role.' ));
            }
        } else {
            self::$logger->debug(sprintf('The user was not valid!' ));
        }

        self::$logger->debug(sprintf('The action "%s", role "%s" and sub-action "%s" combination is not accessible.', $action,isset($role) ? $role : '', $sub ));

        return false;
    }
    /**
     * isRouteAccessible
     * @param string $route
     * @param string $sub
     * @param LoggerInterface|null $logger
     * @return bool
     * @throws \Exception
     */
    public static function isRouteAccessible(string $route, string $sub = '%', ?LoggerInterface $logger = null): bool
    {
        if (null !== $logger)
            self::$logger = $logger;
        if (self::checkModuleRouteReady($route) === false) {
            self::$logger->warning(sprintf('No module or action was linked to the route "%s"', $route));
            return false;
        }

        return self::isActionAccessibleToUser(self::$module,self::$action,$route,$sub);
    }

    /**
     * @return AuthorizationCheckerInterface
     */
    public static function getChecker(): AuthorizationCheckerInterface
    {
        return self::$checker;
    }

    /**
     * @var null|string
     */
    private static $passwordPolicy;

    /**
     * getPasswordPolicy
     * @return array
     */
    public static function getPasswordPolicy(): array
    {
        if (null !== self::$passwordPolicy)
            return self::$passwordPolicy;

        $output = [];
        $provider = ProviderFactory::create(Setting::class);
        $alpha = $provider->getSettingByScopeAsBoolean('System', 'passwordPolicyAlpha');
        $numeric = $provider->getSettingByScopeAsBoolean('System', 'passwordPolicyNumeric');
        $punctuation = $provider->getSettingByScopeAsBoolean('System', 'passwordPolicyNonAlphaNumeric');
        $minLength = $provider->getSettingByScopeAsInteger('System', 'passwordPolicyMinLength');

        if (!$alpha || !$numeric || !$punctuation || $minLength >= 0) {
            $output[] = 'The password policy stipulates that passwords must:';
            if ($alpha)
                $output[] = 'Contain at least one lowercase letter, and one uppercase letter.';
            if ($numeric)
                $output[] = 'Contain at least one number.';
            if ($punctuation)
                $output[] = 'Contain at least one non-alphanumeric character (e.g. a punctuation mark or space).';
            if ($minLength >= 0)
                $output[] = 'Must be at least {oneString} characters in length.';
        }
        $output['minLength'] = $minLength;

        self::$passwordPolicy = $output;
        return self::$passwordPolicy;
    }

    /**
     * isGranted
     * @param $role
     * @param null $object
     * @param null $field
     * @return bool
     */
    public static function isGranted($role, $object = null)
    {
        if (null === self::$checker) {
            return false;
        }

        try {
            return self::$checker->isGranted($role, $object);
        } catch (AuthenticationCredentialsNotFoundException $e) {
            return false;
        }
    }

    /**
     * encodeAndSetPassword
     * @param SecurityUser $user
     * @param string $raw
     */
    public static function encodeAndSetPassword(SecurityUser $user, string $raw)
    {
        $password = UserHelper::getEncoder()->encodePassword($user, $raw);

        $person = $user->getPerson();

        $person->setPassword($password);
    }

    /**
     * @return RoleHierarchyInterface
     */
    public static function getHierarchy(): RoleHierarchyInterface
    {
        return self::$hierarchy;
    }

    /**
     * getRoleCategory
     * @param string $role
     * @return string
     */
    public static function getRoleCategory(string $role): string
    {
        $reachableRoles = self::getHierarchy()->getReachableRoleNames([$role]);

        if (in_array('ROLE_STAFF', $reachableRoles))
            return 'Staff';
        if (in_array('ROLE_STUDENT', $reachableRoles))
            return 'Student';
        if (in_array('ROLE_PARENT', $reachableRoles))
            return 'Parent';
        return 'Other';
    }

    /**
     * getCurrentUser
     * @return string|\Stringable|\Symfony\Component\Security\Core\User\UserInterface
     */
    public function getCurrentUser()
    {
        $token = self::$storage->getToken();

        return $token->getUser();
    }
}