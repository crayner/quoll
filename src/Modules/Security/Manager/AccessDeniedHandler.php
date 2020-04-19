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

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Http\Authorization\AccessDeniedHandlerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class AccessDeniedHandler
 * @package App\Modules\Security\Manager
 */
class AccessDeniedHandler implements AccessDeniedHandlerInterface
{
    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * AccessDeniedHandler constructor.
     * @param TranslatorInterface $translator
     */
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * handle
     * @param Request $request
     * @param AccessDeniedException $accessDeniedException
     * @return JsonResponse|RedirectResponse|\Symfony\Component\HttpFoundation\Response|null
     */
    public function handle(Request $request, AccessDeniedException $accessDeniedException)
    {
        // If Route is api_*
        if ($request->getContentType() === 'json'){
            return new JsonResponse(
                [
                    'error' => $this->translator->trans('return.error.0'),
                ],
                200);
        }

        $request->getSession()->getFlashBag()->add('warning', 'return.error.0');
        return new RedirectResponse('/');
    }
}