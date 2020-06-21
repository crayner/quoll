<?php
/**
 * Created by PhpStorm.
 *
 * Quoll
 * (c) 2020 Craig Rayner <craig@craigrayner.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * User: craig
 * Date: 18/06/2020
 * Time: 14:53
 */

namespace App\Modules\Security\Voter;


use App\Modules\People\Entity\Person;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

class StudentProfileVoter extends StudentVoter
{
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
        if (in_array('ROLE_STUDENT_PROFILE', $attributes)) {
            return parent::vote($token,$subject,$attributes);
        }
        return VoterInterface::ACCESS_ABSTAIN;
    }

    /**
     * isStudentAccessible
     * @param Person $student
     * @return bool
     * 20/06/2020 12:53
     */
    public static function isStudentAccessible(Person $student): bool
    {
        return in_array($student, self::getStudentList());
    }
}
