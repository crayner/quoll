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
 * Date: 17/04/2020
 * Time: 15:13
 */
namespace App\Modules\People\Controller;

use App\Container\Container;
use App\Container\ContainerManager;
use App\Container\Panel;
use App\Container\Section;
use App\Controller\AbstractPageController;
use App\Modules\People\Entity\Person;
use App\Modules\People\Form\ChangePasswordType;
use App\Modules\People\Form\PersonType;
use App\Modules\People\Pagination\PeoplePagination;
use App\Modules\People\Util\UserHelper;
use App\Modules\Security\Manager\SecurityUser;
use App\Modules\Security\Util\SecurityHelper;
use App\Provider\ProviderFactory;
use App\Twig\Sidebar\Photo;
use App\Twig\SidebarContent;
use App\Util\ErrorMessageHelper;
use App\Util\TranslationHelper;
use Doctrine\DBAL\Driver\PDOException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class PeopleController
 * @package App\Modules\People\Controller
 */
class PeopleController extends AbstractPageController
{
    /**
     * manage
     * @param PeoplePagination $pagination
     * @return JsonResponse
     * @Route("/people/list/", name="people_list")
     * @IsGranted("ROLE_ROUTE")
     */
    public function manage(PeoplePagination $pagination)
    {
        $pagination->setStack($this->getPageManager()->getStack())
            ->setContent([])
            ->setAddElementRoute($this->generateUrl('person_add'))
            ->setStoreFilterURL($this->generateUrl('people_list_filter'))
            ->setContentLoader($this->generateUrl('people_content_loader'));

        return $this->getPageManager()->createBreadcrumbs('Manage People')
            ->render(['pagination' => $pagination->toArray()]);
    }

    /**
     * manageContent
     * @param PeoplePagination $pagination
     * @Route("/people/content/loader/", name="people_content_loader")
     * @return JsonResponse
     */
    public function manageContent(PeoplePagination $pagination)
    {
        try {
            $content = ProviderFactory::create(Person::class)->getPaginationContent();

            $pagination->setContent($content);
            return new JsonResponse(['content' => $pagination->getContent(), 'pageMax' => $pagination->getPageMax(), 'status' => 'success'], 200);
        } catch (\Exception $e) {
            return new JsonResponse(['class' => 'error', 'message' => $e->getMessage()], 200);
        }
    }

    /**
     * edit
     * @param ContainerManager $manager
     * @param SidebarContent $sidebar
     * @param Person|null $person
     * @param string $tabName
     * @return Response
     * @Route("/person/{person}/edit/{tabName}", name="person_edit")
     * @Route("/person/add/{tabName}", name="person_add")
     * @IsGranted("ROLE_ROUTE")
     */
    public function edit(ContainerManager $manager, SidebarContent $sidebar, ?Person $person = null, string $tabName = 'Basic')
    {

        $request = $this->getRequest();

        if (is_null($person)) {
            $person = new Person();
            $action = $this->generateUrl('person_add', ['tabName' => 'Basic']);
        } else {
            $action = $this->generateUrl('person_edit', ['person' => $person->getID(), 'tabName' => $tabName]);
        }

        $photo = new Photo($person, 'getImage240', '200', 'user max200');
        $photo->setTransDomain(false)->setTitle($person->formatName(['informal' => true]));
        $sidebar->addContent($photo);

        $container = new Container($tabName);
        $section = new Section('form', 'single');
        TranslationHelper::setDomain('People');

        $propertyAccessor = PropertyAccess::createPropertyAccessor();

        $form = $this->createForm(PersonType::class, $person,
            [
                'action' => $action,
                'user_roles' => $this->getUser()->getAllRoles(),
            ]
        );

        if ($request->getContent() !== '') {
            $content = json_decode($request->getContent(), true);
            $errors = [];
            $status = 'success';
            $redirect = '';
            $form->submit($content);
            if ($form->isValid())
            {
                $id = $person->getId();
                $em = $this->getDoctrine()->getManager();
                $em->persist($person);
                $em->flush();
                if ($id !== $person->getId())
                {
                    $status = 'redirect';
                    $redirect = $this->generateUrl('person_edit', ['person' => $person->getId(), 'tabName' => $tabName]);
                    $this->addFlash('success', ErrorMessageHelper::onlySuccessMessage());
                } else {
                    $data = ErrorMessageHelper::getSuccessMessage([], true);
                    $status = $data['status'];
                    $errors = $data['errors'];
                }
            } else {
                $data = ErrorMessageHelper::getInvalidInputsMessage([], true);
                $status = $data['status'];
                $errors = $data['errors'];
            }

            $panel = new Panel('Basic', 'People', $section);
            $container->addForm('single', $form->createView())->addPanel($panel);

            $panel = new Panel('System', 'People', $section);
            $container->addPanel($panel);

            if ($person->getId() !== null) {
                $panel = new Panel('Contact', 'People', $section);
                $container->addPanel($panel);

                $panel = new Panel('School', 'People', $section);
                $container->addPanel($panel);

                $panel = new Panel('Background', 'People', $section);
                $container->addPanel($panel);

                if (UserHelper::isParent($person)) {
                    $panel = new Panel('Employment', 'People', $section);
                    $container->addPanel($panel);
                }

                if (UserHelper::isStaff($person)) {
                    $panel = new Panel('Emergency', 'People', $section);
                    $container->addPanel($panel);
                }

                $panel = new Panel('Miscellaneous', 'People', $section);
                $container->addPanel($panel);
            }

            $manager->addContainer($container)->buildContainers();

            return new JsonResponse(
                [
                    'form' => $manager->getFormFromContainer(),
                    'errors' => $errors,
                    'status' => $status,
                    'redirect' => $redirect,
                ],
                200);
        }

        $panel = new Panel('Basic', 'People', $section);

        $container->addForm('single', $form->createView())->addPanel($panel);

        $panel = new Panel('System', 'People', $section);
        $container->addPanel($panel);

        if ($person->getId() !== null) {
            $panel = new Panel('Contact', 'People', $section);
            $container->addPanel($panel);

            $panel = new Panel('School', 'People', $section);
            $container->addPanel($panel);

            $panel = new Panel('Background', 'People', $section);
            $container->addPanel($panel);

            if (UserHelper::isParent($person)) {
                $panel = new Panel('Employment', 'People', $section);
                $container->addPanel($panel);
            }

            if (UserHelper::isStaff($person)) {
                $panel = new Panel('Emergency', 'People', $section);
                $container->addPanel($panel);
            }

            $panel = new Panel('Miscellaneous', 'People', $section);
            $container->addPanel($panel);
        }

        $manager->setReturnRoute($this->generateUrl('people_list'));
        $manager->addContainer($container)->buildContainers();

        return $this->getPageManager()->createBreadcrumbs($person->getId() !== null ? 'Edit Person' : 'Add Person')
            ->render(
                [
                    'containers' => $manager->getBuiltContainers(),
                ]
            );
    }


    /**
     * delete
     * @param Person $person
     * @param PeoplePagination $pagination
     * @Route("/person/{person}/delete/",name="person_delete")
     * @IsGranted("ROLE_ROUTE")
     * @return JsonResponse
     */
    public function delete(Person $person, PeoplePagination $pagination)
    {
        if ($person->canDelete()) {
            try {
                $em = $this->getDoctrine()->getManager();
                $em->remove($person);
                $em->flush();
                $this->getPageManager()->addMessage('success', ErrorMessageHelper::onlySuccessMessage(true));
            } catch (PDOException $e) {
                $this->getPageManager()->addMessage('error', ErrorMessageHelper::onlyDatabaseErrorMessage(true));
            }
        } else {
            $this->getPageManager()->addMessage('warning', ErrorMessageHelper::onlyLockedRecordMessage($person->formatName(['informal' => true]), get_class($person), true));
        }

        $pagination->setStack($this->getPageManager()->getStack())
            ->setContent([])
            ->setAddElementRoute($this->generateUrl('person_add'))
            ->setStoreFilterURL($this->generateUrl('people_list_filter'))
            ->setContentLoader($this->generateUrl('people_content_loader'));

        return $this->getPageManager()->createBreadcrumbs('Manage People')
            ->render(
                [
                    'pagination' => $pagination->toArray(),
                    'url' => $this->generateUrl('people_list'),
                ]
            );
    }

    /**
     * writePeopleFilter
     * @param PeoplePagination $pagination
     * @return JsonResponse
     * @Route("/people/filter/list", name="people_list_filter")
     * @IsGranted("ROLE_ROUTE")
     */
    public function writePeopleFilter(PeoplePagination $pagination)
    {
        if ($this->getPageManager()->getRequest()->getContent() !== '') {
            $filter = json_decode($this->getPageManager()->getRequest()->getContent(), true);
            $pagination->setStack($this->getPageManager()->getStack())
                ->writeFilter($filter);
        }
        return new JsonResponse([]);
    }

    /**
     * resetPassword
     * @param Person $person
     * @param ContainerManager $manager
     * @return Response
     * @Route("/password/{person}/reset/",name="person_reset_password")
     * @IsGranted("ROLE_ROUTE")
     */
    public function resetPassword(Person $person, ContainerManager $manager)
    {
        $request = $this->getPageManager()->getRequest();

        if ($this->getUser()->getPerson()->isEqualto($person)) {
            $this->addFlash('info', TranslationHelper::translate('Use the {anchor}references{endAnchor} details to change your own password.', ['{endAnchor}' => '</a>', '{anchor}' => '<a href="'.$this->generateUrl('preferences', ['tabName' => 'Reset Password']).'">'], 'People'));
            return $this->redirectToRoute('people_list');
        }

        $form = $this->createForm(ChangePasswordType::class, $person,
            [
                'action' => $this->generateUrl('person_reset_password', ['person' => $person->getId()]),
                'policy' => $this->renderView('security/password_policy.html.twig', ['passwordPolicy' => SecurityHelper::getPasswordPolicy()])
            ]
        );

        if ($request->getContent() !== '')
        {
            $content = json_decode($request->getContent(), true);
            $form->submit($content);
            $data = [];
            if ($form->isValid()) {
                $user = new SecurityUser($person);
                $user->changePassword($content['raw']['first']);
                $data['status'] = 'success';
                $data['errors'][] = ['class' => 'success', 'message' => TranslationHelper::translate('Your account has been successfully updated. You can now continue to use the system as per normal.', [], 'Security')];
                $manager->singlePanel($form->createView());
                $person->setPasswordForceReset($content['passwordForceReset']);
                $this->getDoctrine()->getManager()->persist($person);
                $this->getDoctrine()->getManager()->flush();
                $data['form'] = $manager->getFormFromContainer();
                return new JsonResponse($data, 200);
            } else {
                $manager->singlePanel($form->createView());
                $data = ErrorMessageHelper::getInvalidInputsMessage([],true);
                $data['form'] = $manager->getFormFromContainer();
                return new JsonResponse($data, 200);
            }

        }

        $manager->setReturnRoute($this->generateUrl('people_list'));
        $manager->singlePanel($form->createView());

        return $this->getPageManager()->createBreadcrumbs('Reset Password')
            ->render(['containers' => $manager->getBuiltContainers()]);
    }
}