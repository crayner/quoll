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
 * Date: 21/07/2020
 * Time: 14:21
 */
namespace App\Modules\People\Controller;

use App\Container\ContainerManager;
use App\Manager\StatusManager;
use App\Modules\Security\Entity\SecurityUser;
use App\Modules\Security\Form\Entity\SecurityUserType;
use App\Provider\ProviderFactory;
use App\Util\ErrorMessageHelper;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class SecurityUserController extends PeopleController
{
    /**
     * editSecurityUser
     *
     * 20/08/2020 11:28
     * @param SecurityUser $user
     * @Route("/security/user/{user}/edit/",name="security_user_edit")
     * @IsGranted("ROLE_ROUTE")
     * @return JsonResponse
     */
    public function editSecurityUser(SecurityUser $user)
    {
        if ($this->getRequest()->getContentType() === 'json') {

            $form = $this->createSecurityUserForm($user);

            return $this->saveSecurityUserContent($form, $user);
        } else {
            $form = $this->createSecurityUserForm($user);
            $this->getStatusManager()->error(StatusManager::INVALID_INPUTS);
            return $this->singleForm($form);
        }
    }

    /**
     * createSecurityUserForm
     *
     * 20/08/2020 11:28
     * @param SecurityUser $user
     * @return FormInterface
     */
    private function createSecurityUserForm(SecurityUser $user): FormInterface
    {
        return $this->createForm(SecurityUserType::class, $user,
            [
                'action' => $this->generateUrl('security_user_edit', ['user' => $user->getId()]),
                'user' => $this->getUser(), // The current user
            ]
        );
    }

    /**
     * saveSecurityUserContent
     *
     * 20/08/2020 11:28
     * @param FormInterface $form
     * @param SecurityUser $securityUser
     * @return JsonResponse
     */
    private function saveSecurityUserContent(FormInterface $form, SecurityUser $securityUser)
    {
        $content = json_decode($this->getRequest()->getContent(), true);
        $form->submit($content);

        if ($form->isValid()) {
            ProviderFactory::create(SecurityUser::class)->persistFlush($securityUser);
            if ($this->isStatusSuccess()) {
                $form = $this->createSecurityUserForm($securityUser);
            }
        } else {
            $this->getStatusManager()->error(StatusManager::INVALID_INPUTS);
        }
        return $this->singleForm($form);
    }
}
