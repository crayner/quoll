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
 * Date: 8/09/2020
 * Time: 15:14
 */
namespace App\Modules\Security\Voter;

use App\Modules\Security\Util\ActionVoterSubject;
use App\Modules\System\Entity\Action;
use App\Provider\ProviderFactory;
use Psr\Log\LoggerInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\RoleHierarchyVoter;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Security\Core\Role\RoleHierarchyInterface;

/**
 * Class ActionVoter
 * @package App\Modules\Security\Voter
 * @author Craig Rayner <craig@craigrayner.com>
 */
class ActionVoter extends RoleHierarchyVoter
{
    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * ActionVoter constructor.
     * @param LoggerInterface $logger
     * @param RoleHierarchyInterface $roleHierarchy
     */
    public function __construct(LoggerInterface $logger, RoleHierarchyInterface $roleHierarchy)
    {
        $this->logger = $logger;
        parent::__construct($roleHierarchy);
    }

    /**
     * vote
     *
     * 8/09/2020 15:18
     * @param TokenInterface $token
     * @param mixed $subject
     * @param array $attributes
     * @return int
     */
    public function vote(TokenInterface $token, $subject, array $attributes)
    {
        if (in_array('ROLE_ACTION', $attributes)) {
            if (!$subject instanceof ActionVoterSubject) {
                $this->getLogger()->warning('You must set an "ActionVoterSubject" when using the "ROLE_ACTION"');
                return VoterInterface::ACCESS_DENIED;
            }
            foreach (ProviderFactory::getRepository(Action::class)->findBy(['entryRoute' => $subject->getRoute()], ['precedence' => 'DESC']) as $action) {
                $result = parent::vote($token, null, $action->getSecurityRoles());

                if ($result === VoterInterface::ACCESS_GRANTED || $token->getUser()->isSuperUser()) {
                    $subject->setAction($action)
                        ->setActionAccessible(true);
                    return VoterInterface::ACCESS_GRANTED;
                }
            }
            $this->logger->warning(sprintf('The user "%s" was denied access to the route "%s".', $token->getUser()->getPerson()->getFullNameReversed(), $subject->getRoute()), [$subject]);

            return VoterInterface::ACCESS_DENIED;
        }
        return VoterInterface::ACCESS_ABSTAIN;

    }

    /**
     * @return LoggerInterface
     */
    public function getLogger(): LoggerInterface
    {
        return $this->logger;
    }

}