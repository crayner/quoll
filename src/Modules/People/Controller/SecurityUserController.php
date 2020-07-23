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
use App\Modules\People\Entity\Person;
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
     * editUser
     * @param ContainerManager $manager
     * @param Person $person
     * @return JsonResponse
     * 20/07/2020 11:27
     * @Route("/security/user/{person}/edit/",name="security_user_edit")
     * @IsGranted("ROLE_ROUTE")
     */
    public function editSecurityUser(ContainerManager $manager, Person $person)
    {
        if ($this->getRequest()->getContentType() === 'json') {

            $user = $person->getSecurityUser() ?: new SecurityUser($person);

            $form = $this->createSecurityUserForm($person);

            return $this->saveSecurityUserContent($form, $manager, $user);
        } else {
            $form = $this->createSecurityUserForm($person);
            $data = ErrorMessageHelper::getInvalidInputsMessage([], true);
            $manager->singlePanel($form->createView());
            $data['form'] = $manager->getFormFromContainer();
            return new JsonResponse($data);
        }
    }

    /**
     * createSecurityUserForm
     * @param Person $person
     * @return FormInterface
     * 21/07/2020 14:24
     */
    private function createSecurityUserForm(Person $person): FormInterface
    {
        return $this->createForm(SecurityUserType::class, $person->getSecurityUser(),
            [
                'action' => $this->generateUrl('security_user_edit', ['person' => $person->getId()]),
                'user_roles' => $this->getUser()->getRoles(),
                'user' => $this->getUser(),
            ]
        );
    }

    /**
     * saveContent
     * @param FormInterface $form
     * @param ContainerManager $manager
     * @param SecurityUser $securityUser
     * @return JsonResponse
     * 20/07/2020 11:31
     */
    private function saveSecurityUserContent(FormInterface $form, ContainerManager $manager, SecurityUser $securityUser)
    {
        $content = json_decode($this->getRequest()->getContent(), true);
        $form->submit($content);

        $data = [];
        if ($form->isValid()) {
            $data = ProviderFactory::create(SecurityUser::class)->persistFlush($securityUser, $data);
            if ($data['status'] !== 'success') {
                $data = ErrorMessageHelper::getDatabaseErrorMessage($data, true);
                $manager->singlePanel($form->createView());
                $data['form'] = $manager->getFormFromContainer();
                return new JsonResponse($data);
            } else {
                $form = $this->createSecurityUserForm($securityUser->getPerson());
            }
            $manager->singlePanel($form->createView());
            $data['form'] = $manager->getFormFromContainer();
        } else {
            $data = ErrorMessageHelper::getInvalidInputsMessage($data, true);
            $manager->singlePanel($form->createView());
            $data['form'] = $manager->getFormFromContainer();
        }
        return new JsonResponse($data);
    }
}
