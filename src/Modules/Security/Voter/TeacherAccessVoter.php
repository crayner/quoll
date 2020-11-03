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
 * Date: 3/11/2020
 * Time: 08:46
 */
namespace App\Modules\Security\Voter;

use App\Manager\PageDefinition;
use App\Modules\Department\Entity\DepartmentStaff;
use App\Modules\Enrolment\Entity\CourseClass;
use App\Modules\RollGroup\Entity\RollGroup;
use App\Modules\Student\Entity\Student;
use App\Provider\ProviderFactory;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Security\Core\Role\RoleHierarchyInterface;

/**
 * Class TeacherAccessVoter
 *
 * 3/11/2020 09:00
 * @package App\Modules\Security\Voter
 * @author Craig Rayner <craig@craigrayner.com>
 */
class TeacherAccessVoter extends RouteVoter
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
     * vote
     * @param TokenInterface $token
     * @param mixed $subject
     * @param array $attributes
     * @return bool|int|void
     * 19/06/2020 13:28
     */
    public function vote(TokenInterface $token, $subject, array $attributes)
    {
        if ($this->supports($subject,$attributes)) {
            if (($vote = parent::vote($token, $subject, ['ROLE_ROUTE'])) === VoterInterface::ACCESS_GRANTED) {
                if ($token->getUser()->getPerson()->isPrincipal()) return $vote;
                if ($token->getUser()->getPerson()->isRegistrar()) return $vote;
                if ($token->getUser()->getPerson()->isSuperUser()) return $vote;



                switch (get_class($subject)) {
                    case RollGroup::class:
                        return $this->voteRollGroup($subject, $token);
                }
            } else {
                return $vote;
            }
        }
        return VoterInterface::ACCESS_ABSTAIN;
    }

    /**
     * supports
     *
     * 3/11/2020 08:52
     * @param $subject
     * @param array $attributes
     * @return bool
     */
    private function supports($subject, array $attributes): bool
    {
        if (in_array('ROLE_TEACHER_ACCESS', $attributes)) {
            if ($subject instanceof Student) return true;
            if ($subject instanceof RollGroup) return true;
            if ($subject instanceof CourseClass) return true;
        }
        return false;
    }

    /**
     * voteRollGroup
     *
     * 3/11/2020 09:08
     * @param RollGroup $rollGroup
     * @param TokenInterface $token
     * @return int
     */
    private function voteRollGroup(RollGroup $rollGroup, TokenInterface $token): int
    {
        // Roll Group are available to Principals and System Admin and the tutors of the RollGroup.
        if ($token->getUser()->getStaff()->isEqualTo($rollGroup->getTutor()) ||
            $token->getUser()->getStaff()->isEqualTo($rollGroup->getTutor2()) ||
            $token->getUser()->getStaff()->isEqualTo($rollGroup->getTutor3())
        ) {
            return Voter::ACCESS_GRANTED;
        }

        // Check if this HeadTeacher has access to this Roll Group because Tutor or assistants
        if ($token->getUser()->getPerson()->isHeadTeacher()) {
            $staff = array_merge($rollGroup->getTutors(), $rollGroup->getAssistants());
            foreach($staff as $q=>$w) {
                $staff[$q] = $w->getId();
            }
            if (ProviderFactory::create(DepartmentStaff::class)->isHeadTeacherOf($token->getUser()->getPerson(), $staff)) {
                return VoterInterface::ACCESS_GRANTED;
            }
        }
        return VoterInterface::ACCESS_DENIED;
    }
}
