<?php
/**
 * Created by PhpStorm.
 *
 * This file is part of the Busybee Project.
 *
 * (c) Craig Rayner <craig@craigrayner.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * UserProvider: craig
 * Date: 13/06/2018
 * Time: 16:27
 */
namespace App\Modules\People\Util;

use App\Modules\People\Entity\Person;
use App\Entity\Staff;
use App\Provider\EntityProviderInterface;
use App\Provider\ProviderFactory;
use App\Modules\People\Manager\SecurityUser;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * Class UserHelper
 * @package App\Util
 */
class UserHelper
{
    /**
     * @var TokenStorageInterface
     */
    private static $tokenStorage;

    /**
     * @var EntityProviderInterface
     */
    private static $provider;

    /**
     * @var UserPasswordEncoderInterface
     */
    private static $encoder;

    /**
     * UserHelper constructor.
     * @param TokenStorageInterface $tokenStorage
     */
    public function __construct(TokenStorageInterface $tokenStorage, UserPasswordEncoderInterface $encoder)
    {
        self::$tokenStorage = $tokenStorage;
        self::$provider = ProviderFactory::create(Person::class);
        self::$encoder = $encoder;
    }

    /**
     * @var Person|null
     */
    private static $currentUser;

    /**
     * @var SecurityUser|null
     */
    private static $currentSecurityUser;

    /**
     * getCurrentUser
     * @return Person|null
     */
    public static function getCurrentUser(): ?Person
    {
        if (! is_null(self::$currentUser))
            return self::$currentUser;

        $user = self::getCurrentSecurityUser();

        if ($user instanceof SecurityUser)
            self::$currentUser = $user->getPerson();
        else
            self::$currentUser = null;

        return self::$currentUser;
    }

    /**
     * getCurrentSecurityUser
     * @param Person|null $person
     * @return SecurityUser|null
     */
    public static function getCurrentSecurityUser(Person $person = null): ?SecurityUser
    {
        if (empty(self::$tokenStorage))
            return self::$currentSecurityUser = null;

        $token = self::$tokenStorage->getToken();

        if (is_null($token))
            return self::$currentSecurityUser = null;

        self::$currentSecurityUser = $token->getUser() instanceof SecurityUser ? $token->getUser() : null ;
        return self::$currentSecurityUser;
    }

    /**
     * getSecurityUser
     * @param Person|null $person
     * @return SecurityUser|null
     */
    public static function getSecurityUser(Person $person = null): ?SecurityUser
    {
        if (is_null($person))
            return self::getCurrentSecurityUser();
        self::$currentSecurityUser = new SecurityUser($person);
        return self::$currentSecurityUser;
    }

    /**
     * getProvider
     * @return EntityProviderInterface
     */
    public static function getProvider(): EntityProviderInterface
    {
        return self::$provider;
    }

    /**
     * isStaff
     * @return bool
     */
    public static function isStaff(Person $person = null): bool
    {
        $person = $person ?: self::getCurrentUser();

        return $person instanceof Person && $person->getStaff() instanceof Staff;
    }

    /**
     * isParent
     * @return bool
     */
    public static function isStudent(Person $person = null): bool
    {
        $person = $person ?: self::getCurrentUser();

        return $person instanceof Person && self::$provider->isStudent($person);
    }

    /**
     * isParent
     * @return bool
     */
    public static function isParent(Person $person = null): bool
    {
        $person = $person ?: self::getCurrentUser();

        return $person instanceof Person && self::$provider->isParent($person);
    }

    /**
     * getRoles
     * @return array
     * @throws \Exception
     */
    public static function getRoles(): array
    {
        self::$provider->setEntity(self::getCurrentUser());
        return self::$provider->getUserRoles();
    }

    /**
     * getRoleCategories
     * @return array
     * @throws \Exception
     */
    public static function getRoleCategories(): array
    {
        self::$provider->setEntity(self::getCurrentUser());
        return self::$provider->getUserRoleCategories();
    }

    /**
     * getYearGroups
     * @return array
     * @throws \Exception
     */
    public static function getStaffYearGroupsByCourse(): array
    {
        self::$provider->setEntity(self::getCurrentUser());
        return self::$provider->getStaffYearGroupsByCourse();
    }

    /**
     * getYearGroups
     * @return array
     * @throws \Exception
     */
    public static function getStaffYearGroupsByRollGroup(): array
    {
        self::$provider->setEntity(self::getCurrentUser());
        return self::$provider->getStaffYearGroupsByRollGroup();
    }

    /**
     * getYearGroups
     * @return array
     * @throws \Exception
     */
    public static function getStudentYearGroup(): array
    {
        self::$provider->setEntity(self::getCurrentUser());
        return self::$provider->getStudentYearGroup();
    }

    /**
     * getYearGroups
     * @return array
     * @throws \Exception
     */
    public static function getParentYearGroups(): array
    {
        self::$provider->setEntity(self::getCurrentUser());
        return self::$provider->getParentYearGroups();
    }

    /**
     * @var array
     */
    private static $childrenOfParent;

    /**
     * getChildrenOfParent
     * @return array
     * @throws \Exception
     */
    public static function getChildrenOfParent(Person $person = null): array
    {
        if (! empty(self::$childrenOfParent))
            return self::$childrenOfParent;
        self::$provider->setEntity($person ?: self::getCurrentUser());
        return self::$childrenOfParent = self::$provider->getChildrenOfParent();
    }

    /**
     * getStaffRollGroups
     * @param string $returnStyle
     * @return array
     */
    public static function getStaffRollGroups(string $returnStyle = 'entity'): array
    {
        $x = self::getProvider()->getStaffRollGroups();
        if ($returnStyle === 'entity')
            return $x;
        $result = [];
        foreach($x as $item)
            $result[] = $item->getId();
        return array_unique($result);
    }

    /**
     * getStaffRollGroups
     * @param string $returnStyle
     * @return array
     */
    public static function getStudentRollGroups(string $returnStyle = 'entity'): array
    {
        $x = self::getProvider()->getStudentRollGroups();
        if ($returnStyle === 'entity')
            return $x;
        $result = [];
        foreach($x as $item)
            $result[] = $item->getId();
        return array_unique($result);
    }

    /**
     * getParentRollGroups
     * @param string $returnStyle
     * @return array
     */
    public static function getParentRollGroups(string $returnStyle = 'entity'): array
    {
        $x = self::getProvider()->getParentRollGroups();
        if ($returnStyle === 'entity')
            return $x;
        $result = [];
        foreach($x as $item)
            $result[] = $item->getId();
        return array_unique($result);
    }

    /**
     * getPersonCourses
     * @param string $returnStyle
     * @return array
     * @throws \Exception
     */
    public static function getCoursesByPerson(?Person $person = null, string $returnStyle = 'entity')
    {
        $person = $person ?: self::getCurrentUser();
        self::getProvider()->setEntity($person);

        $x = self::getProvider()->getCoursesByPerson();
        if ($returnStyle === 'entity')
            return $x;
        $result = [];
        foreach($x as $item)
            $result[] = $item->getId();
        return array_unique($result);
    }

    /**
     * getPersonCourses
     * @param string $returnStyle
     * @return array
     * @throws \Exception
     */
    public static function getCourseClassesByPerson(?Person $person = null, string $returnStyle = 'entity')
    {
        $person = $person ?: self::getCurrentUser();
        self::getProvider()->setEntity($person);

        $x = self::getProvider()->getCourseClassesByPerson();
        if ($returnStyle === 'entity')
            return $x;
        $result = [];
        foreach($x as $item)
            $result[] = $item->getId();
        return array_unique($result);
    }

    /**
     * getActivitiesByStaff
     * @param string $returnStyle
     * @return array
     * @throws \Exception
     */
    public static function getActivitiesByStaff(string $returnStyle = 'entity')
    {
        $x = self::getProvider()->getActivitiesByStaff();
        if ($returnStyle === 'entity')
            return $x;
        $result = [];
        foreach($x as $item)
            $result[] = $item->getId();
        return array_unique($result);
    }

    /**
     * getActivitiesByStudents
     * @param string $returnStyle
     * @return array
     * @throws \Exception
     */
    public static function getActivitiesByStudent(?Person $person = null, string $returnStyle = 'entity')
    {
        self::getProvider()->setEntity($person ?: self::getCurrentUser());
        if (! self::getProvider()->getEntity()->isStudent())
            return [];
        $x = self::getProvider()->getActivitiesByStudents();
        if ($returnStyle === 'entity')
            return $x;
        $result = [];
        foreach($x as $item)
            $result[] = $item->getId();
        return array_unique($result);
    }

    /**
     * getActivitiesByParent
     * @param string $returnStyle
     * @return array
     * @throws \Exception
     */
    public static function getActivitiesByParent(string $returnStyle = 'entity'): array
    {
        if (!self::isParent())
            return [];
        $x = self::getProvider()->getActivitiesByParent();
        if ($returnStyle === 'entity')
            return $x;
        $result = [];
        foreach($x as $item)
            $result[] = $item->getId();
        return array_unique($result);
    }

    /**
     * getStudentAttendance
     * @param string $showDate
     * @param Person|null $person
     * @param string $returnStyle
     * @return array
     */
    public static function getStudentAttendance(string $showDate = 'today', string $timezone = 'UTC', ?Person $person = null, string $returnStyle = 'entity'): array
    {
        $person = $person ?: self::getCurrentUser();
        if (!$person->isStudent())
            return [];
        self::getProvider()->setEntity($person);
        $x = self::getProvider()->getStudentAttendance($showDate, $timezone);
        if ($returnStyle === 'entity')
            return $x;
        $result = [];
        foreach($x as $item)
            $result[] = $item->getId();
        return array_unique($result);
    }

    /**
     * getStudentAttendance
     * @param string $showDate
     * @param Person|null $person
     * @param string $returnStyle
     * @return array
     */
    public static function getGroups(?Person $person = null, string $returnStyle = 'entity'): array
    {
        $person = $person ?: self::getCurrentUser();
        self::getProvider()->setEntity($person);
        $x = self::getProvider()->getGroups();
        if ($returnStyle === 'entity')
            return $x;
        $result = [];
        foreach($x as $item)
            $result[] = $item->getId();
        return array_unique($result);
    }

    /**
     * @return UserPasswordEncoderInterface
     */
    public static function getEncoder(): UserPasswordEncoderInterface
    {
        return self::$encoder;
    }
}