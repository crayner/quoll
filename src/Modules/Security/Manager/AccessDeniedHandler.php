<?php
/**
 * Created by PhpStorm.
 *
 * Project: Kookaburra
 * Build: Quoll
 *
 * (c) 2018 Craig Rayner <craig@craigrayner.com>
 *
 * User: craig
 * Date: 22/12/2018
 * Time: 05:59
 */
namespace App\Modules\Security\Manager;

use App\Manager\StatusManager;
use App\Modules\System\Entity\Action;
use App\Provider\ProviderFactory;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Http\Authorization\AccessDeniedHandlerInterface;

/**
 * Class AccessDeniedHandler
 * @package App\Modules\Security\Manager
 */
class AccessDeniedHandler implements AccessDeniedHandlerInterface
{
    /**
     * handle
     * @param Request $request
     * @param AccessDeniedException $accessDeniedException
     * @return JsonResponse|RedirectResponse|Response|null
     */
    public function handle(Request $request, AccessDeniedException $accessDeniedException)
    {
        if (!$request->attributes->get('action') instanceof Action && $request->getContentType() === 'json') {
            $route = $request->attributes->get('_route');
            $action = ProviderFactory::getRepository(Action::class)->findOneByRoute($route);
            if (!$action instanceof Action) {

                $data = [];
                $data['status'] = 'redirect';
                $data['check'] = 'Handler';

                $data['redirect'] = sprintf('/route/%s/error/', $route);

                return new JsonResponse($data);
            }
        }

        $request->getSession()->getFlashBag()->add('warning', StatusManager::NO_ACCESS);
        return new RedirectResponse('/home/');
    }
}