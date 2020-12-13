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
 * Date: 5/11/2020
 * Time: 08:31
 */
namespace App\Modules\Security\Voter;

use App\Manager\PageDefinition;
use App\Modules\Enrolment\Entity\CourseClass;
use App\Modules\RollGroup\Entity\RollGroup;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Security\Core\Role\RoleHierarchyInterface;

/**
 * Class CourseClassAccessVoter
 *
 * 5/11/2020 08:32
 * @package App\Modules\Security\Voter
 * @author Craig Rayner <craig@craigrayner.com>
 */
class CourseClassAccessVoter extends RouteVoter
{
    /**
     * RouteVoter constructor.
     * @param LoggerInterface $logger
     * @param RequestStack $stack
     * @param RoleHierarchyInterface $roleHierarchy
     * @param PageDefinition $definition
     */
    public function __construct(LoggerInterface $logger, RequestStack $stack, RoleHierarchyInterface $roleHierarchy, PageDefinition $definition)
    {
        parent::__construct($logger, $stack, $roleHierarchy, $definition);
    }

    /**
     * supports
     *
     * 5/11/2020 08:24
     * @param array $attributes
     * @return bool
     */
    private function supports(array $attributes): bool
    {
        return in_array('ROLE_COURSE_CLASS', $attributes);
    }

    /**
     * vote
     *
     * 4/11/2020 15:43
     * @param TokenInterface $token
     * @param mixed $subject
     * @param array $attributes
     * @return int|void
     */
    public function vote(TokenInterface $token, $subject, array $attributes)
    {
        if ($this->supports($attributes)) {
            $this->getLogger()->debug('Checking access to Course Class');
            if (parent::vote($token, null, ['ROLE_ROUTE']) === VoterInterface::ACCESS_GRANTED && $subject instanceof CourseClass) {
                if ($token->getUser()->getPerson()->isPrincipal()) return VoterInterface::ACCESS_GRANTED;
                if ($token->getUser()->getPerson()->isSuperUser()) return VoterInterface::ACCESS_GRANTED;
                if ($token->getUser()->getPerson()->isRegistrar()) return VoterInterface::ACCESS_GRANTED;

                if ($subject->isTutor($token->getUser()->getStaff())) return VoterInterface::ACCESS_GRANTED;

                $department = $subject->getCourse()->getDepartment();
                if ($department->isHeadTeacher($token->getUser()->getStaff())) return VoterInterface::ACCESS_GRANTED;
            }
            $this->getLogger()->warning(sprintf('The user "%s" attempted to access the course class "%s" and was denied.', $token->getUser()->getPerson()->getFullNameReversed(), $subject->getFullName()));
            return VoterInterface::ACCESS_DENIED;
        }
        return VoterInterface::ACCESS_ABSTAIN;
    }
}
