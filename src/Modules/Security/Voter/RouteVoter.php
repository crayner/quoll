<?php
/**
 * Created by PhpStorm.
 *
 * quoll
 * (c) 2020 Craig Rayner <craig@craigrayner.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * User: craig
 * Date: 18/04/2020
 * Time: 12:37
 */

namespace App\Modules\Security\Voter;

use App\Modules\Security\Exception\RoleRouteException;
use App\Modules\Security\Manager\SecurityUser;
use App\Modules\Security\Util\SecurityHelper;
use App\Modules\System\Entity\Action;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\RoleHierarchyVoter;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Security\Core\Role\RoleHierarchyInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Class RouteVoter
 * @package App\Modules\Security\Voter
 */
class RouteVoter extends RoleHierarchyVoter
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
     * GibbonVoter constructor.
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
     * @return int|void
     */
    public function vote(TokenInterface $token, $subject, array $attributes)
    {
        if (in_array('ROLE_ROUTE', $attributes))
        {
            $action = $this->getRequest()->attributes->get('action');
            $route = $this->getRequest()->attributes->get('_route');

            if (!$action instanceof Action) {
                $this->logger->warning(sprintf('The user "%s" attempted to access the route "%s" and was denied as the ACTION was not set correctly.', $token->getUser()->formatName(['title' => false]),
                $route));
//                throw new RoleRouteException($route);
                return VoterInterface::ACCESS_DENIED;
            }

            if (null === $action->getRole())
                return VoterInterface::ACCESS_GRANTED;

            $attributes = [$action->getRole()];

            $result = parent::vote($token, $subject, $attributes);

            if ($result === VoterInterface::ACCESS_ABSTAIN)
                $this->logger->error(sprintf('The user "%s" attempted to access the route "%s" but the ACTION role "%s" was not found.', $token->getUser()->formatName(), $route, $action->getRole()), $action);

            if ($result === VoterInterface::ACCESS_DENIED) {
                if ($token->getUser() instanceof SecurityUser)
                    $this->logger->info(sprintf('The user "%s" attempted to access the route "%s" and was denied.', $token->getUser()->formatName(), $route), [$action]);
                else
                    $this->logger->info(sprintf('The user "%s" attempted to access the route "%s" and was denied.', 'anon.', $route), [$action]);
            }
            return $result;
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

}