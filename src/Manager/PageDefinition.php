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
 * Date: 1/09/2020
 * Time: 15:37
 */
namespace App\Manager;

use App\Modules\System\Entity\Action;
use App\Modules\System\Entity\Module;
use App\Provider\ProviderFactory;
use Doctrine\DBAL\Exception\ConnectionException;
use Doctrine\DBAL\Exception\TableNotFoundException;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class PageDefinition
 * @package App\Entity
 * @author Craig Rayner <craig@craigrayner.com>
 */
class PageDefinition
{
    /**
     * @var Action|null
     */
    private ?Action $action;

    /**
     * @var Module|null
     */
    private ?Module $module;

    /**
     * @var string|null
     */
    private ?string $route;

    /**
     * @var string
     */
    private string $controller;

    /**
     * @var Request|null
     */
    private ?Request $request;

    /**
     * getAction
     *
     * 2/09/2020 08:46
     * @return Action|null
     */
    public function getAction(): ?Action
    {
        if (!isset($this->action)) $this->setAction();
        return isset($this->action) ? $this->action : null;
    }

    /**
     * setAction
     *
     * 1/09/2020 16:19
     * @return $this
     */
    public function setAction(): PageDefinition
    {
        if (null === $this->getModule()) $this->setModule();
        if (!isset($this->action) && $this->getModule() !== null) {
            foreach ($this->getModule()->getActions() as $action) {
                if (in_array($this->getRoute(), $action->getRouteList())) {
                    $this->action = $action;
                    break;
                }
            }
        }
        return $this;
    }

    /**
     * getModule
     *
     * 2/09/2020 08:46
     * @return Module|null
     */
    public function getModule(): ?Module
    {
        if (!isset($this->module)) $this->setModule();

        return $this->module;
    }

    /**
     * setModule
     *
     * 1/09/2020 15:59
     * @return PageDefinition
     */
    public function setModule(): PageDefinition
    {
        if (!isset($this->module)) {
            try {
                $this->module = ProviderFactory::getRepository(Module::class)->findOneBy(['name' => $this->getControllerModuleName()]);
            } catch ( ConnectionException | TableNotFoundException $e) {
                $this->module = null;
            }
        }
        return $this;
    }

    /**
     * getModuleName
     *
     * 2/09/2020 09:41
     * @param string|null $name
     * @return string|null
     */
    public function getModuleName(?string $name = null): ?string
    {
        return $this->getModule() ? $this->getModule()->getName() : $name;
    }

    /**
     * getRoute
     *
     * 1/09/2020 15:57
     * @return string|null
     */
    public function getRoute(): ?string
    {
        return $this->route = isset($this->route) ? $this->route : $this->getRequest()->attributes->get('_route');
    }

    /**
     * getController
     *
     * 1/09/2020 15:57
     * @return string
     */
    public function getController(): string
    {
        return $this->controller = isset($this->controller) ? $this->controller : (string)$this->getRequest()->attributes->get('_controller');
    }

    /**
     * @param string $controller
     * @return PageDefinition
     */
    public function setController(string $controller): PageDefinition
    {
        $this->controller = $controller;
        return $this;
    }

    /**
     * getRequest
     *
     * 2/09/2020 10:40
     * @return Request|null
     */
    public function getRequest(): ?Request
    {
        return $this->request;
    }

    /**
     * setRequest
     *
     * 2/09/2020 10:40
     * @param Request|null $request
     * @return $this
     */
    public function setRequest(?Request $request): PageDefinition
    {
        $this->request = $request;
        return $this;
    }

    /**
     * getControllerModuleName
     *
     * 1/09/2020 16:06
     * @return string
     */
    private function getControllerModuleName(): string
    {
        $controller = explode('\\', $this->getController());
        return trim(implode(' ', preg_split('/(?=[A-Z])/',$controller[2])));
    }

    /**
     * getActionArray
     *
     * 1/09/2020 17:34
     * @return array
     */
    public function getActionArray(): array
    {
        $action = $this->getAction();
        if ($action) {
            return [
                'id' => $action->getId(),
                'name' => $action->getName(),
                'translatedName' => $action->getTranslatedName($this->getModule()->getName()),
                'precedence' => $action->getPrecedence(),
                'category' => $action->getCategory(),
                'routeList' => $action->getRouteList(),
                'entryRoute' => $action->getEntryRoute(),
                'entrySidebar' => $action->isEntrySidebar(),
                'menuShow' => $action->isMenuShow(),
                'module' => $this->getModule() ? $this->getModule()->getId() : null,
            ];
        }
        return [];
    }

    /**
     * getModuleArray
     *
     * 1/09/2020 17:42
     * @return array
     */
    public function getModuleArray(): array
    {
        return $this->getModule() ? $this->getModule()->toArray() : [];
    }

    /**
     * isValidPage
     *
     * 2/09/2020 09:24
     * @return bool
     */
    public function isValidPage(): bool
    {
        return $this->getModule()
            && $this->getAction()
            && $this->getAction()->getModules()->contains($this->getModule())
            && in_array($this->getRoute(), $this->getAction()->getRouteList());
    }

    /**
     * getModuleEntryRoute
     *
     * 2/09/2020 09:57
     * @return string|null
     */
    public function getModuleEntryRoute(): ?string
    {
        return $this->getModule() ? $this->getModule()->getEntryRoute() : null;
    }

    /**
     * getActionName
     *
     * 2/09/2020 10:18
     * @return string|null
     */
    public function getActionName(): ?string
    {
        return $this->getAction() ? $this->getAction()->getName() : null;
    }

    /**
     * toArray
     *
     * 8/09/2020 14:43
     * @return array
     */
    public function toArray()
    {
        return [
            'action' => $this->getAction() ? $this->getAction()->toArray() : 'empty',
            'module' => $this->getModule() ? $this->getModule()->toArray() : 'empty',
            'route' => $this->getRoute(),
            'valid' => $this->isValidPage(),
        ];
    }
}
