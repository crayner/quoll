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
 * Date: 14/04/2020
 * Time: 09:05
 */
namespace App\Controller;

use App\Container\ContainerManager;
use App\Manager\StatusManager;
use App\Manager\PageManager;
use App\Modules\System\Manager\SettingFactory;
use App\Provider\ProviderFactory;
use InvalidArgumentException;
use LogicException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class AbstractPageController
 * @package App\Controller
 */
abstract class AbstractPageController extends AbstractController
{
    /**
     * getSubscribedServices
     * @return array
     */
    public static function getSubscribedServices()
    {
        return (array_merge(parent::getSubscribedServices(),
            [
                'page_manager' => PageManager::class,
                'container_manager' => ContainerManager::class,
                'provider_factory' => ProviderFactory::class,
                'setting_factory' => SettingFactory::class,
                'message_status_manager' => StatusManager::class,
            ]
        ));
    }

    /**
     * getPageManager
     * @return PageManager
     */
    protected function getPageManager(): PageManager
    {
        return $this->get('page_manager');
    }


    /**
     * Adds a flash message to the current session for type.
     *
     * @param string $type
     * @param string|array $message
     */
    protected function addFlash(string $type, $message): void
    {
        if (!$this->container->has('session')) {
            throw new LogicException('You can not use the addFlash method if sessions are disabled. Enable them in "config/packages/framework.yaml".');
        }

        if (!((is_array($message) && count($message) === 3) || is_string($message))) {
            throw new InvalidArgumentException('The message must be a string or a translation array of 3 parts [id, [params], domain] to be correctly handled by the flash display logic.');
        }

        $this->container->get('session')->getFlashBag()->add($type, $message);
    }

    /**
     * getRequest
     * @return Request
     */
    protected function getRequest(): Request
    {
        return $this->getPageManager()->getRequest();
    }

    /**
     * getContainerManager
     * @return ContainerManager
     * 4/08/2020 13:46
     */
    public function getContainerManager(): ContainerManager
    {
        return $this->get('container_manager');
    }

    /**
     * getStatusManager
     * @return StatusManager
     * 15/08/2020 15:45
     */
    public function getStatusManager(): StatusManager
    {
        return $this->get('message_status_manager');
    }

    /**
     * generateJsonResponse
     *
     * 17/08/2020 13:49
     * @param array $options
     * @return JsonResponse
     */
    public function generateJsonResponse(array $options = []): JsonResponse
    {
        return $this->getStatusManager()->toJsonResponse($options);
    }

    /**
     * isStatusSuccess
     *
     * 19/08/2020 08:36
     * @return bool
     */
    public function isStatusSuccess(): bool
    {
        return $this->getStatusManager()->isStatusSuccess();
    }

    /**
     * singleForm
     *
     * 19/08/2020 16:24
     * @param FormInterface $form
     * @return JsonResponse
     */
    public function singleForm(FormInterface $form): JsonResponse
    {
        return $this->generateJsonResponse(
            [
                'form' => $this->getContainerManager()
                    ->singlePanel($form->createView())
                    ->getFormFromContainer(),
            ]
        );
    }

    /**
     * isPostContent
     *
     * 4/09/2020 10:35
     * @return bool
     */
    protected function isPostContent(): bool
    {
        return $this->getRequest()->getMethod() === 'POST' && $this->getRequest()->getContent() !== '';
    }

    /**
     * jsonDecode
     *
     * 4/09/2020 10:34
     * @return array
     */
    protected function jsonDecode(): array
    {
        return json_decode($this->getRequest()->getContent(), true);
    }

    /**
     * isRoute
     *
     * 23/09/2020 12:40
     * @param string $routeName
     * @return bool
     */
    protected function isRoute(string $routeName): bool
    {
        return $routeName === $this->getRequest()->attributes->get('_route');
    }
}