<?php
/**
 * Created by PhpStorm.
 *
* Quoll
 *
 * (c) 2018 Craig Rayner <craig@craigrayner.com>
 *
 * User: craig
 * Date: 22/12/2018
 * Time: 05:59
 */
namespace App\Modules\Security\Manager;

use App\Modules\System\Entity\Action;
use App\Provider\ProviderFactory;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
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
     * @return JsonResponse|RedirectResponse|\Symfony\Component\HttpFoundation\Response|null
     */
    public function handle(Request $request, AccessDeniedException $accessDeniedException)
    {
        if ($request->attributes->get('action') === false) {
            $action = ProviderFactory::getRepository(Action::class)->findOneByRoute($request->attributes->get('_route'));
            if (!$action) {
                $request->getSession()->getFlashBag()->add('error', sprintf('The route "%s" is not defined in the action database.', $request->attributes->get('_route')));
                return new RedirectResponse('/');
            }
        }

        $request->getSession()->getFlashBag()->add('warning', 'return.error.0');
        return new RedirectResponse('/');
    }
}