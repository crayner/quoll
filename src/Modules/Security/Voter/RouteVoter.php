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
 * Date: 18/04/2020
 * Time: 12:37
 */
namespace App\Modules\Security\Voter;

use App\Manager\PageDefinition;
use App\Modules\Security\Entity\SecurityUser;
use App\Modules\System\Entity\Action;
use App\Modules\System\Exception\InvalidActionException;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\RoleHierarchyVoter;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Security\Core\Role\RoleHierarchyInterface;

/**
 * Class RouteVoter
 * @package App\Modules\Security\Voter
 * @author Craig Rayner <craig@craigrayner.com>
 */
class RouteVoter extends RoleHierarchyVoter
{
    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * @var RequestStack
     */
    private RequestStack $stack;

    /**
     * @var PageDefinition
     */
    private PageDefinition $definition;

    /**
     * RouteVoter constructor.
     * @param LoggerInterface $logger
     * @param RequestStack $stack
     * @param RoleHierarchyInterface $roleHierarchy
     * @param PageDefinition $definition
     */
    public function __construct(LoggerInterface $logger, RequestStack $stack, RoleHierarchyInterface $roleHierarchy, PageDefinition $definition)
    {
        $this->logger = $logger;
        $this->stack = $stack;
        $this->definition = $definition;
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
            if ($this->definition->getAction() === null || $this->definition->getModule() === null) $this->definition->setAction();
            $action = $this->definition->getAction();
            $route = $this->definition->getRoute();

            $this->logger->debug(sprintf('Checking out route "%s".', $route));

            if (!$token->getUser() instanceof SecurityUser) {
                $this->logger->error(sprintf('The user is not valid and attempted to access the route "%s" and was denied.', $route), [$action]);
                return VoterInterface::ACCESS_DENIED;
            }

            if (!$this->definition->isValidPage()) {
                $this->logger->error(sprintf('The page for route "%s" has not been defined in the database correctly. Access is denied as the page definition is not valid.', $route), [$this->definition]);
                return VoterInterface::ACCESS_DENIED;

            }

            if ($token->getUser() instanceof SecurityUser && $token->getUser()->isSuperUser()) return VoterInterface::ACCESS_GRANTED;

            if (count($action->getSecurityRoles()) === 0) {
                $this->logger->debug('The Action has no restrictions.');
                return VoterInterface::ACCESS_GRANTED;
            }

            $result = parent::vote($token, $subject, $action->getSecurityRoles());

            if ($result === VoterInterface::ACCESS_ABSTAIN)
                $this->logger->error(sprintf('The user "%s" attempted to access the route "%s" but the ACTION role "%s" was not found.', $token->getUser()->getPerson()->getFullNameReversed(), $route, implode(',',$action->getSecurityRoles())), $action);

            if ($result === VoterInterface::ACCESS_DENIED) {
                if ($token->getUser() instanceof SecurityUser)
                    $this->logger->warning(sprintf('The user "%s" attempted to access the route "%s" and was denied.', $token->getUser()->getPerson()->getFullNameReversed(), $route), [$action]);
                else
                    $this->logger->error(sprintf('The user is not valid and attempted to access the route "%s" and was denied.', $route), [$action]);
            }
            return $result;
        }
        return VoterInterface::ACCESS_ABSTAIN;
    }

    /**
     * @var Request
     */
    private Request $request;

    /**
     * getRequest
     * @return Request
     */
    private function getRequest(): Request
    {
        if (!isset($this->request))
            $this->request = $this->stack->getCurrentRequest();
        return $this->request;
    }
}
