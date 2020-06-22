<?php
/**
 * Created by PhpStorm.
 *
 * Project: Kookaburra
 * Build: Quoll
 *
 * (c) 2019 Craig Rayner <craig@craigrayner.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * User: craig
 * Date: 30/11/2019
 * Time: 12:09
 */
namespace App\Modules\Security\Manager;

use App\Modules\People\Util\UserHelper;
use App\Modules\System\Entity\I18n;
use App\Modules\School\Entity\AcademicYear;
use App\Provider\ProviderFactory;
use App\Twig\FastFinder;
use App\Modules\People\Entity\Person;
use App\Util\ErrorHelper;
use App\Util\TranslationHelper;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Security;

/**
 * Trait AuthenticatorTrait
 * @package App\Modules\Security\Manager
 */
trait AuthenticatorTrait
{
    /**
     * setLanguage
     * @param Request $request
     * @param string|null $i18nID
     */
    public function setLanguage(Request $request, string $i18nID = null)
    {
        $session = $request->getSession();

        if ($i18nID !== null && $i18nID !== $session->get('i18n')->getId())
            ProviderFactory::create(I18n::class)->setLanguageSession($session,  ['id' => $i18nID], false);

        if (null !== $i18nID && ($i18nID !== $session->get('i18n')->getId()))
            ProviderFactory::create(I18n::class)->setLanguageSession($session,  ['id' => $i18nID], false);
    }

    /**
     * setAcademicYear
     * @param SessionInterface $session
     * @param AcademicYear|null $academicYear
     * @return bool
     */
    public function setAcademicYear(SessionInterface $session, ?AcademicYear $academicYear = null)
    {
        $academicYear = $academicYear === null ? ProviderFactory::getRepository(AcademicYear::class)->findOneByStatus('Current') : ProviderFactory::getRepository(AcademicYear::class)->find($academicYear);

        if ($academicYear instanceof AcademicYear) {
            $session->set('academicYear', $academicYear);
        } else {
            $session->forget('AcademicYear');
        }

        return true;
    }

    /**
     * checkAcademicYear
     * @param Person $person
     * @param SessionInterface $session
     * @param AcademicYear|string $academicYear
     * @return bool|RedirectResponse|Response
     * @throws \Exception
     */
    public function checkAcademicYear(Person $person, SessionInterface $session, $academicYear)
    {
        if (is_string($academicYear)) {
            $academicYear = ProviderFactory::getRepository(AcademicYear::class)->find($academicYear);
        }
        if (null === $academicYear || $academicYear->getid() === $session->get('academicYear')->getId())
            return $this->setAcademicYear($session, $academicYear);

        if ($person->getSecurityRoles() === null || [] === $person->getSecurityRoles())
            return $this->authenticationFailure('return.fail.9');

        $roles = $person->getSecurityRoles();
/**
        if (! $role->isFutureYearsLogin() && ! $role->isPastYearsLogin()) {
            LogProvider::setLog($academicYear, null, $person, 'Login - Failed', ['username' => $person->getUsername(), 'reason' => 'Not permitted to access non-current school year'], null);
            return $this->authenticationFailure('return.fail.9');
        }
*/

        if (!$academicYear instanceof AcademicYear)
            return ErrorHelper::ErrorResponse('Configuration Error: there is a problem accessing the current Academic Year from the database.',[], static::$instance);

        if (!UserHelper::isPastYearsLogin() && $session->get('academicYear')->getId() > $academicYear->getId()) {

            return $this->authenticationFailure('return.fail.9');
        }

        $this->setAcademicYear($session, $academicYear->getId());
        return true;
    }

    /**
     * createUserSession
     * @param string|Person $username
     * @param $session
     * @return Person
     * @todo Clear legacy
     */
    public function createUserSession($username, SessionInterface $session) {

        if ($username instanceof Person)
            $userData = $username;
        elseif ($username instanceof SecurityUser)
            $userData = ProviderFactory::getRepository(Person::class)->find($username->getId());
        else
            $userData = ProviderFactory::getRepository(Person::class)->loadUserByUsernameOrEmail($username);

        $session->clear('backgroundImage');
        $session->set('person', $userData);

        // Cache FF actions on login
        FastFinder::cacheFastFinderActions();

        return $userData;
    }

    /**
     * authenticationFailure
     * @param string $message
     * @param Request $request
     * @param array $options
     * @return RedirectResponse
     */
    public function authenticationFailure(string $message, Request $request, array $options = [])
    {
        if ($request->hasSession()) {
            $request->getSession()->clear();
            $request->getSession()->set(Security::AUTHENTICATION_ERROR, new AuthenticationException(TranslationHelper::translate($message, $options,'Security')));
            $request->getSession()->getBag('flashes')->add('warning', [$message, $options, 'Security']);
        }
        return new RedirectResponse($this->getLoginUrl());
    }

}