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
 * Date: 19/06/2020
 * Time: 13:22
 */
namespace App\Modules\Security\Voter;

use App\Modules\Department\Entity\DepartmentStaff;
use App\Modules\RollGroup\Entity\RollGroup;
use App\Provider\ProviderFactory;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

/**
 * Class RollGroupVoter
 * @package App\Modules\Security\Voter
 * @author Craig Rayner <craig@craigrayner.com>
 */
class RollGroupVoter extends RouteVoter
{
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
        if (in_array('ROLE_ROLL_GROUP', $attributes)) {
            if (($vote = parent::vote($token, $subject, ['ROLE_ROUTE'])) === VoterInterface::ACCESS_GRANTED) {
                if (!$subject instanceof RollGroup) {
                    VoterInterface::ACCESS_DENIED;
                }

                // Roll Group are available to Principals and System Admin and the tutors of the RollGroup.
                if ($token->getUser()->getPerson()->isPrincipal() ||
                    $token->getUser()->getPerson()->isRegistrar() ||
                    $token->getUser()->getPerson()->equalTo($subject->getTutor()) ||
                    $token->getUser()->getPerson()->equalTo($subject->getTutor2()) ||
                    $token->getUser()->getPerson()->equalTo($subject->getTutor3())) {
                    return $vote;
                }

                // Check if this HeadTeacher has access to this Roll Group because Tutor or assistants
                if ($token->getUser()->getPerson()->isHeadTeacher()) {
                    $staff = $subject->getTutors();
                    $staff = array_merge($staff, $subject->getAssistants());
                    foreach($staff as $q=>$w) {
                        $staff[$q] = $w->getId();
                    }
                    if (ProviderFactory::create(DepartmentStaff::class)->isHeadTeacherOf($token->getUser()->getPerson(), $staff)) {
                        return $vote;
                    }
                }


                return VoterInterface::ACCESS_DENIED;
            } else {
                return $vote;
            }
        }
        return VoterInterface::ACCESS_ABSTAIN;
    }

}