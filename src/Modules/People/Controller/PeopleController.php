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
use App\Manager\StatusManager;
use App\Modules\People\Entity\Person;
use App\Modules\People\Form\CareGiverType;
use App\Modules\People\Form\ChangePasswordType;
use App\Modules\People\Form\ContactType;
use App\Modules\People\Form\PersonType;
use App\Modules\People\Form\PersonalDocumentationType;
use App\Modules\People\Form\SchoolStaffType;
use App\Modules\People\Form\SchoolStudentType;
use App\Modules\People\Pagination\PeoplePagination;
use App\Modules\Security\Entity\SecurityUser;
use App\Modules\Security\Form\Entity\SecurityUserType;
use App\Modules\Security\Util\SecurityHelper;
use App\Modules\Staff\Form\StaffType;
use App\Modules\Student\Form\StudentType;
use App\Provider\ProviderFactory;
use App\Twig\SidebarContent;
use App\Twig\Sidebar\Photo;
use App\Util\TranslationHelper;
use Exception;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class PeopleController
 * @package App\Modules\People\Controller
 */
class PeopleController extends AbstractPageController
{
    /**
     * list
     *
     * 19/08/2020 15:53
     * @param PeoplePagination $pagination
     * @Route("/people/list/", name="people_list")
     * @IsGranted("ROLE_ROUTE")
     * @return JsonResponse
     */
    public function list(PeoplePagination $pagination)
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
     *
     * 19/08/2020 15:54
     * @param PeoplePagination $pagination
     * @Route("/people/content/loader/", name="people_content_loader")
     * @IsGranted("ROLE_ROUTE")
     * @return JsonResponse
     */
    public function manageContent(PeoplePagination $pagination)
    {
        try {
            $content = ProviderFactory::create(Person::class)->getPeoplePaginationContent();

            $pagination->setContent($content);
            return new JsonResponse(['content' => $pagination->getContent(), 'pageMax' => $pagination->getPageMax(), 'status' => 'success'], 200);
        } catch (Exception $e) {
            return new JsonResponse(['class' => 'error', 'message' => $e->getMessage()], 200);
        }
    }

    /**
     * edit
     *
     * 19/08/2020 15:55
     * @param SidebarContent $sidebar
     * @param Person|null $person
     * @param string $tabName
     * @Route("/person/{person}/edit/{tabName}", name="person_edit")
     * @Route("/person/add/{tabName}", name="person_add")
     * @IsGranted("ROLE_ROUTE")
     * @return JsonResponse
     */
    public function edit(SidebarContent $sidebar, ?Person $person = null, string $tabName = 'Basic')
    {

        $request = $this->getRequest();

        if (is_null($person)) {
            $person = new Person();
            $action = $this->generateUrl('person_add', ['tabName' => 'Basic']);
        } else {
            $action = $this->generateUrl('person_edit', ['person' => $person->getID(), 'tabName' => $tabName]);
        }

        $photo = new Photo($person->getPersonalDocumentation(), 'getPersonalImage', '200', 'user max200', '/build/static/DefaultPerson.png');
        $photo->setTransDomain(false)->setTitle($person->formatName('Standard'));
        $sidebar->addContent($photo);

        TranslationHelper::setDomain('People');

        $form = $this->createForm(PersonType::class, $person,
            [
                'action' => $action,
            ]
        );

        if ($request->getContent() !== '') {
            $content = json_decode($request->getContent(), true);
            $form->submit($content);
            if ($form->isValid())
            {
                $id = $person->getId();
                ProviderFactory::create(Person::class)->persistFlush($person);
                if ($id !== $person->getId() && $this->isStatusSuccess())
                {
                    $this->getStatusManager()
                        ->setReDirect($this->generateUrl('person_edit', ['person' => $person->getId(), 'tabName' => $tabName]))
                        ->convertToFlash();
                }
            } else {
                $this->getStatusManager()->error(StatusManager::INVALID_INPUTS);
            }

            return $this->generateJsonResponse(
                [
                    'form' => $this->getContainerManager()
                        ->singlePanel($form->createView())
                        ->getFormFromContainer(),
                ]
            );
        }
        $container = new Container($tabName);

        $panel = new Panel('Basic', 'People', new Section('form', 'Basic'));

        $container->addForm('Basic', $form->createView())->addPanel($panel);
        if ($person->getId() !== null) {
            if ($person->isStaff()) {
                $staffForm = $this->createForm(StaffType::class, $person->getStaff(),
                    [
                        'action' => $this->generateUrl('staff_edit', ['staff' => $person->getStaff()->getId()]),
                    ]
                );
                $panel = new Panel('Staff', 'Staff', new Section('form', 'Staff'));
                $container->addForm('Staff', $staffForm->createView())->addPanel($panel);
                $schoolStaffForm = $this->createForm(SchoolStaffType::class, $person->getStaff(),
                    [
                        'action' => $this->generateUrl('staff_school_edit', ['staff' => $person->getStaff()->getId()]),
                        'remove_personal_background' => $this->generateUrl('staff_personal_background_remove', ['staff' => $person->getStaff()->getId()])
                    ]
                );
                $panel = new Panel('School', 'People', new Section('form', 'School'));
                $container->addForm('School', $schoolStaffForm->createView())->addPanel($panel);
            }
            if ($person->isStudent()) {
                $studentForm = $this->createForm(StudentType::class, $person->getStudent(),
                    [
                        'action' => $this->generateUrl('student_edit', ['student' => $person->getStudent()->getId()]),
                    ]
                );
                $panel = new Panel('Student', 'Student', new Section('form', 'Student'));
                $container->addForm('Student', $studentForm->createView())->addPanel($panel);
                $schoolStudentForm = $this->createForm(SchoolStudentType::class, $person->getStudent(),
                    [
                        'action' => $this->generateUrl('student_school_edit', ['student' => $person->getStudent()->getId()]),
                        'remove_personal_background' => $this->generateUrl('student_personal_background_remove', ['student' => $person->getStudent()->getId()])
                    ]
                );
                $panel = new Panel('School', 'People', new Section('form', 'School'));
                $container->addForm('School', $schoolStudentForm->createView())->addPanel($panel);
            }
            if ($person->isCareGiver()) {
                $parentForm = $this->createForm(CareGiverType::class, $person->getCareGiver(),
                    [
                        'action' => $this->generateUrl('care_giver_edit', ['careGiver' => $person->getCareGiver()->getId()]),
                    ]
                );
                $panel = new Panel('Care Giver', 'People', new Section('form', 'Care Giver'));
                $container->addForm('Care Giver', $parentForm->createView())->addPanel($panel);
            }

            $documentationForm = $this->createForm(PersonalDocumentationType::class, $person->getPersonalDocumentation(),
                [
                    'action' => $this->generateUrl('personal_documentation_edit', ['documentation' => $person->getPersonalDocumentation()->getId()]),
                    'remove_birth_certificate_scan' => $this->generateUrl('remove_birth_certificate_scan', ['documentation' => $person->getPersonalDocumentation()->getId()]),
                    'remove_passport_scan' => $this->generateUrl('remove_passport_scan', ['documentation' => $person->getPersonalDocumentation()->getId()]),
                    'remove_personal_image' => $this->generateUrl('remove_personal_image', ['documentation' => $person->getPersonalDocumentation()->getId()]),
                    'remove_id_card_scan' => $this->generateUrl('remove_id_card_scan', ['documentation' => $person->getPersonalDocumentation()->getId()]),
                ]
            );
            $panel = new Panel('Documentation', 'People', new Section('form', 'Documentation'));
            $container->addForm('Documentation', $documentationForm->createView())->addPanel($panel);

            $contactForm = $this->createForm(ContactType::class, $person->getContact(),
                [
                    'action' => $this->generateUrl('contact_edit', ['contact' => $person->getContact()->getId()]),
                ]
            );

            $panel = new Panel('Contact', 'People', new Section('form', 'Contact'));
            $container->addForm('Contact', $contactForm->createView())->addPanel($panel);

            $securityUserForm = $this->createForm(SecurityUserType::class, $person->getSecurityUser(),
                [
                    'action' => $this->generateUrl('security_user_edit', ['user' => $person->getSecurityUser()->getId()]),
                    'user' => $this->getUser(), // Current User, not the user under edit.
                ]
            );
            $panel = new Panel('Security', 'People', new Section('form', 'Security'));
            $container->addForm('Security', $securityUserForm->createView())->addPanel($panel);
        }


        if ($person !== null) {
            $this->getContainerManager()
                ->setAddElementRoute($this->generateUrl('person_add'));
        }

        return $this->getPageManager()->createBreadcrumbs([$person->getId() !== null ? 'Edit Person: {name}' : 'Add Person', ['{name}' => $person->getFullName()]])
            ->render(
                [
                    'containers' => $this->getContainerManager()
                        ->setReturnRoute($this->generateUrl('people_list'))
                        ->addContainer($container)
                        ->getBuiltContainers(),
                ]
            );
    }


    /**
     * delete
     *
     * 19/08/2020 15:57
     * @param Person $person
     * @param PeoplePagination $pagination
     * @Route("/person/{person}/delete/",name="person_delete")
     * @IsGranted("ROLE_ROUTE")
     * @return JsonResponse
     */
    public function delete(Person $person, PeoplePagination $pagination)
    {
        ProviderFactory::create(Person::class)->delete($person);

        $pagination->setStack($this->getPageManager()->getStack())
            ->setContent([])
            ->setAddElementRoute($this->generateUrl('person_add'))
            ->setStoreFilterURL($this->generateUrl('people_list_filter'))
            ->setContentLoader($this->generateUrl('people_content_loader'));

        return $this->getPageManager()->createBreadcrumbs('Manage People')
            ->setMessages($this->getStatusManager()->getMessageArray())
            ->render(
                [
                    'pagination' => $pagination->toArray(),
                    'url' => $this->generateUrl('people_list'),
                ]
            );
    }

    /**
     * writePeopleFilter
     *
     * 19/08/2020 16:08
     * @param PeoplePagination $pagination
     * @Route("/people/filter/list", name="people_list_filter")
     * @IsGranted("ROLE_ROUTE")
     * @return JsonResponse
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
     *
     * 19/08/2020 16:07
     * @param Person $person
     * @param ContainerManager $manager
     * @Route("/password/{person}/reset/",name="person_reset_password")
     * @IsGranted("ROLE_ROUTE")
     * @return JsonResponse|Response
     */
    public function resetPassword(Person $person, ContainerManager $manager)
    {
        $request = $this->getPageManager()->getRequest();

        if ($this->getUser()->getPerson()->isEqualto($person)) {
            $this->addFlash('info', TranslationHelper::translate('Use the {anchor}references{endAnchor} details to change your own password.', ['{endAnchor}' => '</a>', '{anchor}' => '<a href="' . $this->generateUrl('preferences', ['tabName' => 'Reset Password']) . '">'], 'People'));
            return $this->redirectToRoute('people_list');
        }

        $form = $this->createForm(ChangePasswordType::class, $person,
            [
                'action' => $this->generateUrl('person_reset_password', ['person' => $person->getId()]),
                'policy' => $this->renderView('security/password_policy.html.twig', ['passwordPolicy' => SecurityHelper::getPasswordPolicy()])
            ]
        );

        if ($request->getContent() !== '') {
            $content = json_decode($request->getContent(), true);
            $form->submit($content);
            if ($form->isValid()) {
                $user = new SecurityUser($person);
                $user->changePassword($content['raw']['first']);
                $this->getStatusManager()->info('Your account has been successfully updated. You can now continue to use the system as per normal.', [], 'Security');
                $user->setPasswordForceReset($content['passwordForceReset']);
                ProviderFactory::create(Person::class)->persistFlush($person);
            } else {
                $this->getStatusManager()->error(StatusManager::INVALID_INPUTS);
            }
            return $this->generateJsonResponse(
                [
                    'form' => $this->getContainerManager()
                        ->singlePanel($form->createView())
                        ->getFormFromContainer(),
                ]
            );

        }

        $manager->setReturnRoute($this->generateUrl('people_list'));
        $manager->singlePanel($form->createView());

        return $this->getPageManager()->createBreadcrumbs('Reset Password')
            ->render(['containers' => $manager->getBuiltContainers()]);
    }
}
