<?php
/**
 * Created by PhpStorm.
 *
 * Project: Kookaburra
 * Build: Quoll
 * 
 * (c) 2020 Craig Rayner <craig@craigrayner.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * User: craig
 * Date: 18/06/2020
 * Time: 14:03
 */
namespace App\Modules\Security\Voter;

use App\Modules\People\Entity\FamilyMemberCareGiver;
use App\Modules\People\Entity\Person;
use App\Modules\People\Util\UserHelper;
use App\Modules\Security\Manager\SecurityUser;
use App\Modules\Student\Manager\StudentManager;
use App\Provider\ProviderFactory;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\RoleHierarchyVoter;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Security\Core\Role\RoleHierarchyInterface;

/**
 * Class StudentVoter
 * @package App\Modules\Security\Voter
 * @author Craig Rayner <craig@craigrayner.com>
 */
class StudentVoter extends RoleHierarchyVoter
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var RequestStack
     */
    private $stack;

    /**
     * @var SecurityUser|null
     */
    private static $securityUser;

    /**
     * StudentVoter constructor.
     * @param LoggerInterface $logger
     * @param RequestStack $stack
     * @param RoleHierarchyInterface $roleHierarchy
     */
    public function __construct(LoggerInterface $logger, RequestStack $stack, RoleHierarchyInterface $roleHierarchy)
    {
        $this->logger = $logger;
        $this->stack = $stack;
        parent::__construct($roleHierarchy);
    }

    /**
     * vote
     * @param TokenInterface $token
     * @param mixed $subject
     * @param array $attributes
     * @return int
     * 18/06/2020 14:06
     */
    public function vote(TokenInterface $token, $subject, array $attributes)
    {
        if ($this->supports()) {
            if (($vote = parent::vote($token, $subject, ['ROLE_ROUTE'])) === VoterInterface::ACCESS_GRANTED) {
                self::$securityUser = $token->getUser();
                return $vote;
            } else {
                return $vote;
            }
        }
        return VoterInterface::ACCESS_ABSTAIN;
    }

    /**
     * @var Request
     */
    private $request;

    /**
     * getRequest
     * @return Request
     */
    private function getRequest(): Request
    {
        if (null === $this->request)
            $this->request = $this->stack->getCurrentRequest();
        return $this->request;
    }

    /**
     * @return SecurityUser|null
     */
    public static function getSecurityUser(): ?SecurityUser
    {
        return self::$securityUser = self::$securityUser ?? UserHelper::getSecurityUser();
    }

    /**
     * @param SecurityUser|null $securityUser
     */
    public static function setSecurityUser(?SecurityUser $securityUser): void
    {
        self::$securityUser = $securityUser;
    }

    /**
     * getPerson
     * @return Person|null
     * 18/06/2020 15:07
     */
    public static function getPerson(): ?Person
    {
        return self::getSecurityUser()->getPerson();
    }


    /**
     * supports
     * @return bool
     * 18/06/2020 14:54
     */
    public function supports(): bool
    {
        if ($this instanceof StudentProfileVoter) {
            return true;
        }
        return false;
    }

    /**
     * @var string|null
     */
    private static $studentProfileAccess;

    /**
     * studentProfileAccess
     * @return string|null
     * 18/06/2020 15:03
     */
    public static function getStudentProfileAccess(): ?string
    {
        if (self::$studentProfileAccess === null) {
            if (self::getPerson()->isStudent()) {
                self::$studentProfileAccess = 'My';
            }
            if (self::getPerson()->isCareGiver()) {
                self::$studentProfileAccess = 'Parent';
            }
            if (self::getPerson()->isTeacher() || self::getPerson()->isSupport()) {
                self::$studentProfileAccess = 'Staff';
            }
        }

        return self::$studentProfileAccess;
    }

    /**
     * @var array|null
     */
    private static $studentList;

    public static function getStudentList(): array
    {
        if (self::$studentList === null) {
            self::$studentList = [];
            if (self::getStudentProfileAccess() === 'My') {
                self::$studentList[] = self::getPerson();
            }
            if (self::getStudentProfileAccess() === 'Parent') {
                self::$studentList = array_merge(self::$studentList, ProviderFactory::create(FamilyMemberCareGiver::class)->getStudentsOfParent(self::getPerson()));
            }
            if (self::getStudentProfileAccess() === 'Staff') {
                self::$studentList = array_merge(self::$studentList, StudentManager::getStudentsOfStaff(self::$securityUser->getPerson()));
            }
            array_unique(self::$studentList);
        }
        return self::$studentList ?? [];
    }
}