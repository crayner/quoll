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
use App\Modules\Security\Entity\SecurityUser;
use App\Modules\Student\Entity\Student;
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
    private static SecurityUser $securityUser;

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
        if ($this->supports($subject, $attributes)) {
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
     *
     * 5/10/2020 15:21
     * @param $subject
     * @param array $attributes
     * @return bool
     */
    public function supports($subject, array $attributes): bool
    {
        return $this instanceof StudentProfileVoter && $subject instanceof Student && array_intersect($attributes, ['ALLOWED_STUDENT_VIEW', 'ALLOWED_STUDENT_EDIT']);
    }

    /**
     * @var array
     */
    private static array $studentList;

    /**
     * getStudentList
     *
     * 5/10/2020 14:28
     * @return array
     */
    public static function getStudentList(): array
    {
        if (!isset(self::$studentList)) {
            self::$studentList = [];
            if (self::isStudent()) {
                self::$studentList[] = self::getPerson();
            }
            if (self::isCareGiver()) {
                self::$studentList = array_merge(self::$studentList, ProviderFactory::create(FamilyMemberCareGiver::class)->getStudentsOfParent(self::getPerson()));
            }
            if (self::isTeacher()()) {
                self::$studentList = array_merge(self::$studentList, StudentManager::getStudentsOfStaff(self::$securityUser->getPerson()));
            }
            array_unique(self::$studentList);
        }
        return self::$studentList;
    }

    /**
     * isStudent
     *
     * 5/10/2020 14:41
     * @return bool
     */
    public static function isStudent(): bool
    {
        return self::getPerson()->isStudent();
    }

    /**
     * isCareGiver
     *
     * 5/10/2020 14:46
     * @return bool
     */
    public static function isCareGiver(): bool
    {
        return self::getPerson()->isCareGiver();
    }

    /**
     * isTeacher
     *
     * 5/10/2020 14:42
     * @return bool
     */
    public static function isTeacher(): bool
    {
        return self::getPerson()->isTeacher();
    }
}