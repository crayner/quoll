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
 * Time: 15:07
 */
namespace App\Modules\Security\Util;

use App\Modules\System\Entity\Action;

/**
 * Class ActionVoterSubject
 * @package App\Modules\Security\Util
 * @author Craig Rayner <craig@craigrayner.com>
 */
class ActionVoterSubject
{
    /**
     * @var Action|null
     */
    private ?Action $action;

    /**
     * @var string
     */
    private string $route;

    /**
     * @var bool
     */
    private bool $actionAccessible = false;

    /**
     * ActionVoterSubject constructor.
     * @param string $route
     */
    public function __construct(string $route)
    {
        $this->route = $route;
    }

    /**
     * @return string
     */
    public function getRoute(): string
    {
        return $this->route;
    }

    /**
     * @param string $route
     * @return ActionVoterSubject
     */
    public function setRoute(string $route): ActionVoterSubject
    {
        $this->route = $route;
        return $this;
    }

    /**
     * @return Action|null
     */
    public function getAction(): ?Action
    {
        return $this->action;
    }

    /**
     * @param Action|null $action
     * @return ActionVoterSubject
     */
    public function setAction(?Action $action): ActionVoterSubject
    {
        $this->action = $action;
        return $this;
    }

    /**
     * getHighestGroupedAction
     *
     * 8/09/2020 15:33
     * @return string|null
     */
    public function getHighestGroupedAction(): ?string
    {
        if ($this->isActionAccessible()) {
            return $this->getAction()->getRestriction();
        }
        return null;
    }

    /**
     * isActionAccessible
     *
     * 8/09/2020 15:33
     * @return bool
     */
    public function isActionAccessible(): bool
    {
        return $this->actionAccessible;
    }

    /**
     * setActionAccessible
     *
     * 8/09/2020 15:33
     * @param bool $actionAccessible
     * @return $this
     */
    public function setActionAccessible(bool $actionAccessible): ActionVoterSubject
    {
        $this->actionAccessible = $actionAccessible;
        return $this;
    }


}
