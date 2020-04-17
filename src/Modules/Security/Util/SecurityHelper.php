<?php
/**
 * Created by PhpStorm.
 *
 * Kookaburra
 *
 * (c) 2018 Craig Rayner <craig@craigrayner.com>
 *
 * User: craig
 * Date: 19/12/2018
 * Time: 12:17
 */
namespace App\Modules\Security\Util;

use App\Exception\RouteConfigurationException;
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
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationCredentialsNotFoundException;

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
     * SecurityHelper constructor.
     * @param LoggerInterface $logger
     * @param AuthorizationCheckerInterface $checker
     */
    public function __construct(
        LoggerInterface $logger,
        AuthorizationCheckerInterface $checker
    ) {
        self::$logger = $logger;
        self::$checker = $checker;
    }

    /**
     * @return ActionProvider
     */
    public static function getActionProvider(): ActionProvider
    {
        return ProviderFactory::create(Action::class);
    }

    /**
     * @return ModuleProvider
     */
    public static function getModuleProvider(): ModuleProvider
    {
        return ProviderFactory::create(Module::class);
    }

    /**
     * @var array
     */
    private static $highestGroupedActionList = [];

    /**
     * getHighestGroupedAction
     * @param string $address
     * @return bool|string
     */
    public static function getHighestGroupedAction(string $address)
    {
        $module = self::checkModuleReady($address);
        if (isset(self::$highestGroupedActionList[$address]))
            return self::$highestGroupedActionList[$address];
        if ($user = UserHelper::getCurrentUser() === null)
            return self::$highestGroupedActionList[$address] = false;
        $result = self::getActionProvider()->getRepository()->findHighestGroupedAction(self::getActionName($address), $module);
        return self::$highestGroupedActionList[$address] = $result ? $result['name'] : false;
    }

    /**
     * @var array
     */
    private static $checkModuleReadyList = [];

    /**
     * checkModuleReady
     * @param string $address
     * @return \App\Manager\EntityInterface|bool
     */
    public static function checkModuleReady(string $address)
    {
        if (isset(self::$checkModuleReadyList[$address]))
            return self::$checkModuleReadyList[$address];
        try {
            return self::$checkModuleReadyList[$address] = self::getModuleProvider()->findOneBy(['name' => self::getModuleName($address), 'active' => 'Y']);
        } catch (PDOException | \PDOException $e) {
        }

        return self::$checkModuleReadyList[$address] = false;
    }

    /**
     * checkModuleRouteReady
     * @param string $route
     * @return \App\Manager\EntityInterface|bool
     * @throws RouteConfigurationException
     */
    public static function checkModuleRouteReady(string $route)
    {
        try {
            return self::getModuleProvider()->findOneBy(['name' => self::getModuleNameFromRoute($route), 'active' => 'Y']);
        } catch (PDOException | \PDOException $e) {
        }

        return false;
    }

    /**
     * getModuleFromRoute
     * @param string|null $route
     * @return array
     */
    public static function getModuleFromRoute(?string $route): array
    {
        if (is_null($route))
            return [];
        self::getActionFromRoute($route);
        if (!self::$module && mb_strpos($route, '__') !== false) {
            $route = explode('__', $route);
            $route = $route[0];
            try {
                self::$module = ProviderFactory::getRepository(Module::class)->findOneBy(['name' => ucwords(str_replace('_', ' ', $route))]);
            } catch (DriverException $e) {
                self::$module = null;
            }
        }

        return self::$module ? self::$module->toArray() : [];
    }

    /**
     * getModuleName
     * @param string $address
     * @return bool|string
     */
    public static function getModuleName(string $address)
    {
        if (strpos($address, '__'))
        {
            $module = explode('__', $address);
            $module = explode('_', $module[0]);
            foreach($module as $q=>$w)
                $module[$q] = ucfirst($w);
            return implode(' ', $module);
        }
        return substr(substr($address, 9), 0, strpos(substr($address, 9), '/'));
    }

    /**
     * getActionName
     * @param $address
     * @return bool|string
     */
    public static function getActionName($address)
    {
        return substr($address, (10 + strlen(self::getModuleName($address))));
    }

    /**
     * getModuleNameFromRoute
     * @param string $route
     * @return mixed
     * @throws RouteConfigurationException
     */
    public static function getModuleNameFromRoute(string $route)
    {
        $route = self::splitRoute($route);
        return $route['module'];
    }

    /**
     * getActionFromRoute
     * @param $route
     * @return array
     */
    public static function getActionFromRoute($route): array
    {
        if (null === self::$action && mb_strpos($route, '__') !== false) {
            self::$action = ProviderFactory::getRepository(Action::class)->findOneByRoute($route);
            self::$module = self::$action ? self::$action->getModule() : null;
        }
        return self::$action ? self::$action->toArray() : [];
    }

    /**
     * getActionNameFromRoute
     * @param $route
     * @return mixed
     * @throws RouteConfigurationException
     */
    public static function getActionNameFromRoute($route)
    {
        $route = self::splitRoute($route);
        return $route['action'];
    }

    /**
     * splitRoute
     * @param string $route
     * @return array
     * @throws RouteConfigurationException
     */
    public static function splitRoute(string $route): array
    {
        $route = explode('__', $route);
        if (count($route) !== 2)
            throw new RouteConfigurationException(implode('__', $route));
        $route['module'] = ucwords(str_replace('_', ' ', $route[0]));
        $route['action'] = $route[1];
        return $route;
    }
    /**
     * isActionAccessible
     * @param string $address
     * @param string $sub
     * @return bool
     */
    public static function isActionAccessible(string $address, string $sub = '%', ?LoggerInterface $logger = null): bool
    {
        if (null !== $logger)
            self::$logger = $logger;
        return self::isActionAccessibleToRole(self::checkModuleReady($address),self::getActionName($address), $address, $sub);
    }

    /**
     * isActionAccessibleToRole
     * @param Module|bool $module
     * @param string $action
     * @param string $sub
     * @return bool
     */
    private static function isActionAccessibleToRole($module, string $action, string $address, string $sub)
    {
        if (UserHelper::getCurrentUser() instanceof Person) {
            //Check user has a current role set
            if (! empty(UserHelper::getCurrentUser()->getPrimaryRole())) {
                //Check module ready
                if ($module instanceof Module) {
                    //Check current role has access rights to the current action.
                    try {
                        $role = UserHelper::getCurrentUser()->getPrimaryRole();
                        if (count(self::getActionProvider()->findByURLListModuleRole(
                                [
                                    'name' => "%".$action."%",
                                    "module" => $module,
                                    'role' => $role,
                                    'sub' => $sub === '' ? '%' : $sub,
                                ]
                            )) > 0)
                            return true;
                    } catch (PDOException $e) {
                    }
                } else {
                    self::$logger->warning(sprintf('No module was linked to the address "%s"', $address));
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
        return self::isActionAccessibleToRole(self::checkModuleRouteReady($route),self::getActionNameFromRoute($route), $route, $sub);
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
}