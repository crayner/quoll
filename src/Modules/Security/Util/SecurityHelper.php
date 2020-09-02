<?php
/**
 * Created by PhpStorm.
 *
 * Project: Kookaburra
 * Build: Quoll
 *
 * (c) 2018 Craig Rayner <craig@craigrayner.com>
 *
 * User: craig
 * Date: 19/12/2018
 * Time: 12:17
 */
namespace App\Modules\Security\Util;

use App\Modules\People\Entity\Person;
use App\Modules\Security\Entity\SecurityUser;
use App\Modules\System\Entity\Action;
use App\Modules\System\Entity\Module;
use App\Modules\System\Entity\PageDefinition;
use App\Modules\System\Manager\SettingFactory;
use App\Provider\ProviderFactory;
use App\Util\ParameterBagHelper;
use App\Util\TranslationHelper;
use Doctrine\DBAL\Driver\PDOException;
use Doctrine\DBAL\Exception\DriverException;
use Psr\Log\LoggerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Role\RoleHierarchyInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Class SecurityHelper
 * @package App\Modules\Security\Util
 * @author Craig Rayner <craig@craigrayner.com>
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
     * @var RoleHierarchyInterface
     */
    private static $hierarchy;

    /**
     * @var PageDefinition
     */
    private static PageDefinition $definition;

    /**
     * @var TokenStorageInterface
     */
    private static $storage;

    /**
     * @var SecurityUser|null
     */
    private static $currentUser;

    /**
     * @var UserPasswordEncoderInterface
     */
    private static $encoder;

    /**
     * @var array|null
     */
    private static $allCurrentUserRoles;

    /**
     * SecurityHelper constructor.
     * @param LoggerInterface $logger
     * @param AuthorizationCheckerInterface $checker
     * @param TokenStorageInterface $storage
     * @param RoleHierarchyInterface $hierarchy
     * @param PageDefinition $definition
     * @param UserPasswordEncoderInterface $encoder
     */
    public function __construct(
        LoggerInterface $logger,
        AuthorizationCheckerInterface $checker,
        TokenStorageInterface $storage,
        RoleHierarchyInterface $hierarchy,
        PageDefinition $definition,
        UserPasswordEncoderInterface $encoder
    ) {
        self::$logger = $logger;
        self::$checker = $checker;
        self::$hierarchy = $hierarchy;
        self::$storage = $storage;
        self::$encoder = $encoder;
        self::$definition = $definition;
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
        if ($user = self::getCurrentUser() === null)
            return self::$highestGroupedActionList[$route] = false;
        $result = ProviderFactory::create(Action::class)->getRepository()->findHighestGroupedAction(self::getActionName(), self::getModule());
        return self::$highestGroupedActionList[$route] = $result ?: false;
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
            if (! empty(self::getModuleName($route))) {
                return self::$checkModuleReadyList[$route] = ProviderFactory::create(Module::class)->findOneBy(['name' => self::getModuleName($route), 'active' => 'Y']);
            } else {
                return null;
            }
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
        if (self::checkActionReady() === false) {
            self::$logger->warning(sprintf('No action was linked to the route "%s"', self::getRoute()));
            return false;
        }

        return self::isActionAccessibleToUser(self::getModule(),self::getAction(),$route,$sub);
    }

    /**
     * isActionAccessibleToRole
     * @param Module $module
     * @param Action $action
     * @param string $route
     * @param string $sub
     * @return bool
     */
    private static function isActionAccessibleToUser(Module $module, Action $action, string $route, string $sub)
    {
        if (self::getCurrentUser() instanceof Person) {
            //Check user has a current role set
            if (! empty(self::getCurrentUser()->getSecurityRoles())) {
                //Check module ready
                if ($module instanceof Module && $action instanceof Action) {
                    //Check current user has access rights to this action.
                    return self::isGranted($action->getSecurityRoles());
                } else {
                    self::$logger->warning(sprintf('No module or action was linked to the route "%s"', $route));
                }
            } else {
                self::$logger->debug(sprintf('The user does not have a valid Primary Role.' ));
            }
        } else {
            self::$logger->debug(sprintf('The user was not valid!' ));
        }

        self::$logger->debug(sprintf('The action "%s", route "%s" and sub-action "%s" combination is not accessible.', $action->getName(), $route, $sub ));

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
     * isRouteAccessible
     * @param Module $module
     * @param LoggerInterface|null $logger
     * @return bool
     */
    public static function isModuleAccessible(Module $module, ?LoggerInterface $logger = null): bool
    {
        if (null !== $logger)
            self::$logger = $logger;

        return self::isGranted($module->getSecurityRoles());
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
        $provider = SettingFactory::getSettingManager();
        $alpha = $provider->get('System', 'passwordPolicyAlpha');
        $numeric = $provider->get('System', 'passwordPolicyNumeric');
        $punctuation = $provider->get('System', 'passwordPolicyNonAlphaNumeric');
        $minLength = $provider->get('System', 'passwordPolicyMinLength');

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
     * @param string|string[] $roles
     * @return bool
     * 11/06/2020 12:04
     */
    public static function isGranted($roles): bool
    {
        if (is_string($roles)) {
            $roles = [$roles];
        }

        foreach($roles as $role) {
            if (self::$checker->isGranted($role)) return true;
        }
        return false;
    }

    /**
     * getAllCurrentRoles
     * @return array
     * 11/06/2020 10:09
     */
    public static function getAllCurrentUserRoles(): array
    {
        if (! self::getCurrentUser() instanceof SecurityUser) {
            return [];
        }

        if (self::$allCurrentUserRoles === null) {
            return self::$allCurrentUserRoles = SecurityHelper::getHierarchy()->getReachableRoleNames(self::getCurrentUser()->getSecurityRoles());
        }

        return self::$allCurrentUserRoles;
    }

    /**
     * encodeAndSetPassword
     *
     * 19/08/2020 11:36
     * @param UserInterface $user
     * @param string $raw
     * @return bool
     */
    public static function encodeAndSetPassword(UserInterface $user, string $raw): bool
    {
        $password = self::getEncoder()->encodePassword($user, $raw);

        return $user->changePassword($password);
    }

    /**
     * isPasswordValid
     *
     * 19/08/2020 12:19
     * @param SecurityUser $user
     * @param string|null $raw
     * @return bool
     */
    public static function isPasswordValid(SecurityUser $user, ?string $raw): bool
    {
        return self::getEncoder()->isPasswordValid($user, $raw ?? '');
    }

    /**
     * @return RoleHierarchyInterface
     */
    public static function getHierarchy(): RoleHierarchyInterface
    {
        return self::$hierarchy;
    }

    /**
     * getCurrentUser
     * @return SecurityUser|null
     * 11/06/2020 12:01
     */
    public static function getCurrentUser(): ?SecurityUser
    {
        if (self::$currentUser === null && self::$storage !== null) {
            $token = self::$storage->getToken();

            if ($token !== null && $token->getUser() instanceof SecurityUser) {
                self::$currentUser = $token->getUser();
            }
        }
        if (self::$storage === null) {
            self::$currentUser = null;
        }

        return self::$currentUser;
    }

    /**
     * translateRoles
     * @param array $roles
     * @return array
     * 10/06/2020 12:12
     */
    public static function translateRoles(array $roles): array
    {
        foreach($roles as $q=>$w) {
            $roles[$q] = TranslationHelper::translate($w, [], 'Security');
        }
        return $roles;
    }

    /**
     * @param LoggerInterface $logger
     */
    public static function setLogger(LoggerInterface $logger): void
    {
        self::$logger = $logger;
    }

    /**
     * getAssignableRoleNames
     * @param array|string[] $roles
     * @return array|string[]
     */
    public static function getAssignableRoleNames(array $roles =[]): array
    {
        if ($roles === []) {
            $roles = ['ROLE_SYSTEM_ADMIN'];
        }
        return self::getHierarchy()->getAssignableRoleNames($roles);
    }

    /**
     * @param Module|null $module
     */
    public static function setModule(?Module $module): void
    {
        self::$module = $module;
    }

    /**
     * @param Action|null $action
     */
    public static function setAction(?Action $action): void
    {
        self::$action = $action;
    }

    /**
     * rolesThatHaveAccess
     * @param array $attributes
     * @return array
     * 30/06/2020 17:20
     */
    public static function rolesThatHaveAccess(array $attributes): array
    {
        $roles = self::getHierarchy()->getReachableRoleNames(['ROLE_SYSTEM_ADMIN']);
        $result = [];
        foreach ($roles as $role) {

            $accessAvailable = self::getHierarchy()->getReachableRoleNames([$role]);

            foreach ($attributes as $attribute) {
                if (in_array($attribute, $accessAvailable)) {
                    $result[] = $role;
                }
            }
        }
        return array_unique($result);
    }

    /**
     * @return UserPasswordEncoderInterface
     */
    public static function getEncoder(): UserPasswordEncoderInterface
    {
        return self::$encoder;
    }

    /**
     * useEmailAsUsername
     * @return bool
     * 3/07/2020 10:41
     */
    public static function useEmailAsUsername(): bool
    {
        return self::isEmailUnique();
    }

    /**
     * isEmailUnique
     * @return bool
     * 23/07/2020 10:15
     */
    public static function isEmailUnique(): bool
    {
        if (SettingFactory::getSettingManager() === null) {
            return true;
        }
        return SettingFactory::getSettingManager()->get('People', 'uniqueEmailAddress') || ParameterBagHelper::get('google_oauth');
    }

    /**
     * @return PageDefinition
     */
    public static function getDefinition(): PageDefinition
    {
        return self::$definition;
    }

    /**
     * getAction
     *
     * 1/09/2020 16:11
     * @return Action
     */
    public static function getAction(): Action
    {
        return self::$definition->getAction();
    }

    /**
     * getModule
     *
     * 1/09/2020 16:12
     * @return Module
     */
    public static function getModule(): Module
    {
        return self::$definition->getModule();
    }

    /**
     * getModuleName
     *
     * 2/09/2020 08:51
     * @return string
     */
    public static function getModuleName(): string
    {
        return self::getDefinition()->getModule() ? self::getDefinition()->getModule()->getName() : '';
    }

    /**
     * getActionName
     * @param $route
     * @return bool|string
     */
    public static function getActionName(): ?string
    {
        return self::getDefinition()->getActionName();
    }

    /**
     * checkActionReady
     *
     * 2/09/2020 08:16
     * @return bool
     */
    public static function checkActionReady(): bool
    {
        return self::getDefinition()->isValidPage();
    }

    /**
     * getRoute
     *
     * 2/09/2020 08:21
     * @return string
     */
    public static function getRoute(): string
    {
        return self::getDefinition()->getRoute();
    }
}
