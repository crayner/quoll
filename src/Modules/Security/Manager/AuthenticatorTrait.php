<?php
/**
 * Created by PhpStorm.
 *
* Quoll
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

use App\Manager\SessionManager;
use App\Modules\System\Entity\I18n;
use App\Modules\School\Entity\AcademicYear;
use App\Provider\LogProvider;
use App\Provider\ProviderFactory;
use App\Twig\FastFinder;
use App\Util\ErrorHelper;
use App\Modules\Security\Entity\Role;
use App\Modules\People\Entity\Person;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * Trait AuthenticatorTrait
 * @package App\Modules\Security\Manager
 */
trait AuthenticatorTrait
{
    /**
     * setLanguage
     * @param Request $request
     */
    public function setLanguage(Request $request, int $i18nID = null)
    {
        $session = $request->getSession();

        if (intval($i18nID) > 0 && intval($i18nID) !== intval($session->get('i18n')->getId()))
            ProviderFactory::create(I18n::class)->setLanguageSession($session,  ['id' => $i18nID], false);


        if (null !== $i18nID && intval($i18nID) !== intval($session->get(['i18n', 'gibboni18nID'])))
            ProviderFactory::create(I18n::class)->setLanguageSession($session,  ['id' => $i18nID], false);
        elseif ($request->request->has('gibboni18nID') && intval($request->request->get('gibboni18nID')) !== intval($session->get(['i18n', 'gibboni18nID'])))
            ProviderFactory::create(I18n::class)->setLanguageSession($session,  ['id' => $request->request->get('gibboni18nID')], false);
        elseif ($session->has('gibboni18nIDPersonal') && intval($session->get('gibboni18nIDPersonal')) > 0)
            ProviderFactory::create(I18n::class)->setLanguageSession($session,  ['id' => $session->get('gibboni18nIDPersonal'), 'active' => 'Y'], false);
    }

    /**
     * setAcademicYear
     * @param SessionInterface $session
     * @param int $AcademicYear
     * @return bool
     */
    public function setAcademicYear(SessionInterface $session, int $AcademicYear)
    {
        $AcademicYear = $AcademicYear === 0 ? ProviderFactory::getRepository(AcademicYear::class)->findOneByStatus('Current') : ProviderFactory::getRepository(AcademicYear::class)->find($AcademicYear);

        if ($AcademicYear instanceof AcademicYear) {
            $session->set('academicYear', $AcademicYear);
        } else {
            $session->forget('AcademicYear');
        }

        return true;
    }

    /**
     * checkAcademicYear
     * @param Person $person
     * @param SessionInterface $session
     * @param int $AcademicYear
     * @return bool|RedirectResponse|Response
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function checkAcademicYear(Person $person, SessionInterface $session, int $AcademicYear = 0)
    {
        if (0 === $AcademicYear || $AcademicYear === intval($session->get('gibbonAcademicYearID')))
            return $this->setAcademicYear($session, $AcademicYear);

        if ($person->getPrimaryRole() === null)
            return $this->authenticationFailure('return.fail.9');

        $role = $person->getPrimaryRole();
/**
        if (! $role->isFutureYearsLogin() && ! $role->isPastYearsLogin()) {
            LogProvider::setLog($AcademicYear, null, $person, 'Login - Failed', ['username' => $person->getUsername(), 'reason' => 'Not permitted to access non-current school year'], null);
            return $this->authenticationFailure('return.fail.9');
        }
*/
        $AcademicYear = ProviderFactory::create(AcademicYear::class)->find($AcademicYear);

        if (!$AcademicYear instanceof AcademicYear)
            return ErrorHelper::ErrorResponse('Configuration Error: there is a problem accessing the current Academic Year from the database.',[], static::$instance);

        if (!$role->isPastYearsLogin() && $session->get('gibbonAcademicYearSequenceNumber') > $AcademicYear->getSequenceNumber()) {
            LogProvider::setLog($AcademicYear, null, $person, 'Login - Failed', ['username' => $person->getUsername(), 'reason' => 'Not permitted to access non-current school year'], null);
            return $this->authenticationFailure('return.fail.9');
        }

        $this->setAcademicYear($session, $AcademicYear->getId());
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

        // all legacy
        $primaryRole = $userData->getPrimaryRole();

        // Cache FF actions on login
        FastFinder::cacheFastFinderActions();

        return $userData;
    }
}