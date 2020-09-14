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
 * Date: 3/09/2020
 * Time: 08:14
 */
namespace App\Modules\Enrolment\Controller;

use App\Container\Container;
use App\Container\Panel;
use App\Container\Section;
use App\Controller\AbstractPageController;
use App\Manager\StatusManager;
use App\Modules\Enrolment\Entity\CourseClass;
use App\Modules\Enrolment\Entity\CourseClassPerson;
use App\Modules\Enrolment\Form\CourseClassPersonType;
use App\Modules\Enrolment\Form\MultipleCourseClassPersonType;
use App\Modules\Enrolment\Pagination\CourseClassEnrolmentPagination;
use App\Modules\Enrolment\Pagination\CourseClassParticipantPagination;
use App\Modules\People\Entity\Person;
use App\Modules\School\Util\AcademicYearHelper;
use App\Provider\ProviderFactory;
use App\Util\TranslationHelper;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Class ClassEnrolmentController
 * @package App\Modules\Enrolment\Controller
 * @author Craig Rayner <craig@craigrayner.com>
 */
class ClassEnrolmentController extends AbstractPageController
{
    /**
     * list
     *
     * 3/09/2020 08:15
     * @Route("/course/enrolment/by/class/list/",name="course_enrolment_by_class_list")
     * @IsGranted("ROLE_ROUTE")
     * @param CourseClassEnrolmentPagination $pagination
     * @return JsonResponse
     */
    public function list(CourseClassEnrolmentPagination $pagination)
    {
        $pagination->setContent(ProviderFactory::create(CourseClass::class)->getCourseClassEnrolmentPaginationContent(),'CourseClassEnrolmentPagination')
            ->setPageMax(50);

        $container = new Container();
        $panel = new Panel('null', 'Enrolment', new Section('pagination', $pagination));
        $container->addPanel(AcademicYearHelper::academicYearWarning($panel));

        return $this->getPageManager()
            ->createBreadcrumbs('Course Enrolment by Class')
            ->render([
                'containers' => $this->getContainerManager()
                    ->addContainer($container)
                    ->getBuiltContainers(),
            ]);
    }

    /**
     * edit
     *
     * 3/09/2020 11:53
     * @param CourseClass $class
     * @param CourseClassParticipantPagination $pagination
     * @param CsrfTokenManagerInterface $csrfTokenManager
     * @return JsonResponse
     * @Route("/course/class/{class}/enrolment/manage/",name="course_class_enrolment_manage")
     * @IsGranted("ROLE_ROUTE")
     */
    public function manage(CourseClass $class, CourseClassParticipantPagination $pagination,  CsrfTokenManagerInterface $csrfTokenManager)
    {
        $pagination->setCourseClass($class)
            ->setToken($csrfTokenManager)
            ->setContent(ProviderFactory::create(CourseClassPerson::class)->findCourseClassParticipationPagination($class),'CourseClassParticipationPagination')
            ->setAddElementRoute($this->generateUrl('course_class_enrolment_add', ['class' => $class->getId()]))
            ->setPageMax(50);

        $form = $this->createForm(MultipleCourseClassPersonType::class, $class, ['action' => $this->generateUrl('course_class_enrolment_manage', ['class' => $class->getId()])]);

        if ($this->isPostContent()) {
            $form->submit($content = $this->jsonDecode());
            if ($form->isValid()) {
                foreach ($content['people'] as $id) {
                    $person = ProviderFactory::getRepository(Person::class)->find($id);
                    if ($person instanceof Person) {
                        $ccp = ProviderFactory::getRepository(CourseClassPerson::class)->findOneBy(['courseClass' => $class, 'person' => $person]) ?: new CourseClassPerson($class);
                        $ccp->setPerson($person)
                            ->setReportable($class->isReportable())
                            ->setRole($content['role']);
                        ProviderFactory::create(CourseClassPerson::class)->persistFlush($ccp, false);
                    }
                }
                ProviderFactory::create(CourseClassPerson::class)->flush();
            } else {
                $this->getStatusManager()->invalidInputs();
            }
            $this->getStatusManager()->setReDirect($this->generateUrl('course_class_enrolment_manage', ['class' => $class->getId()]), true);
            return $this->getStatusManager()->toJsonResponse();
        }

        $container = new Container();
        $panel = new Panel('null', 'Enrolment', new Section('form', 'single'));
        $panel->addSection(new Section('html', '<h3>'.TranslationHelper::translate('Participants', [], 'Enrolment').'</h3>'));
        $panel->addSection(new Section('pagination', $pagination));
        $container->addPanel(AcademicYearHelper::academicYearWarning($panel))
        ->addForm('single', $form->createView());

        return $this->getPageManager()
            ->createBreadcrumbs(['Manage {name} Enrolment', ['{name}' => $class->getAbbreviatedName()], 'Enrolment'],
                [
                    ['name' => 'Course Enrolment by Class', 'uri' => 'course_enrolment_by_class_list']
                ]
            )
            ->setMessages($this->getStatusManager()->getMessageArray())
            ->setUrl($this->generateUrl('course_class_enrolment_manage', ['class' => $class->getId()]))
            ->render([
                'containers' => $this->getContainerManager()
                    ->addContainer($container)
                    ->getBuiltContainers(),
            ]);
    }

    /**
     * remove
     *
     * 7/09/2020 16:24
     * @param CourseClass $class
     * @param CourseClassPerson $person
     * @param CourseClassParticipantPagination $pagination
     * @param CsrfTokenManagerInterface $csrfTokenManager
     * @Route("/course/class/{class}/enrolment/{person}/delete/",name="course_class_enrolment_delete")
     * @IsGranted("ROLE_ROUTE")
     * @return JsonResponse
     */
    public function remove(CourseClass $class, CourseClassPerson $person, CourseClassParticipantPagination $pagination, CsrfTokenManagerInterface $csrfTokenManager)
    {
        ProviderFactory::create(CourseClassPerson::class)->delete($person);

        return $this->manage($class, $pagination, $csrfTokenManager);
    }

    /**
     * edit
     *
     * 3/09/2020 11:53
     * @param CourseClass $class
     * @param CourseClassPerson|null $person
     * @Route("/course/class/{class}/enrolment/{person}/edit/",name="course_class_enrolment_edit")
     * @Route("/course/class/{class}/enrolment/add/",name="course_class_enrolment_add")
     * @IsGranted("ROLE_ROUTE")
     */
    public function edit(CourseClass $class, ?CourseClassPerson $person = null)
    {
        if (null === $person) {
            $action = $this->generateUrl('course_class_enrolment_add', ['class' => $class->getId()]);
            $person = new CourseClassPerson($class);
        } else {
            $action = $this->generateUrl('course_class_enrolment_edit', ['class' => $class->getId(), 'person' => $person->getId()]);
        }

        $form = $this->createForm(CourseClassPersonType::class, $person, ['action' => $action]);

        if ($this->isPostContent()) {
            $content = $this->jsonDecode();
            $form->submit($content);
            if ($form->isValid()) {
                $id = $person->getId();
                ProviderFactory::create(CourseClassPerson::class)->persistFlush($person);
                if ($id !== $person->getId()) {
                    $this->getStatusManager()->setReDirect($this->generateUrl('course_class_enrolment_edit', ['class' => $class->getId(), 'person' => $person->getId()]), true);
                }
            } else {
                $this->getStatusManager()->invalidInputs();
            }
            return $this->singleForm($form);
        }

        $container = new Container();
        $panel = new Panel('null', 'Enrolment', new Section('form','single'));
        $container->addForm('single', $form->createView())
            ->addPanel($panel);

        if ($person->getId() !== null) $this->getContainerManager()
            ->setAddElementRoute($this->generateUrl('course_class_enrolment_add', ['class' => $class->getId()]));

        return $this->getPageManager()
            ->createBreadcrumbs('Edit Enrolment',
                [
                    ['name' => 'Course Enrolment by Class', 'uri' => 'course_enrolment_by_class_list'],
                    ['name' => 'Manage {name} Enrolment', 'uri' => 'course_class_enrolment_manage', 'uri_params' => ['class' => $class->getId()], 'trans_params' => ['{name}' => $class->getAbbreviatedName()]],
                ]
            )->render(
                [
                    'containers' => $this->getContainerManager()
                        ->setReturnRoute($this->generateUrl('course_class_enrolment_manage', ['class' => $class->getId()]))
                        ->addContainer($container)
                        ->getBuiltContainers(),
                ]
            );
    }

    /**
     * copyToClass
     *
     * 7/09/2020 11:14
     * @param CourseClass $class
     * @param ValidatorInterface $validator
     * @Route("/course/class/{class}/enrolment/copy/to/class/",name="course_class_enrolment_copy_to_class")
     * @IsGranted("ROLE_ROUTE")
     * @return JsonResponse
     */
    public function copyToClass(CourseClass $class, ValidatorInterface $validator)
    {
        $content = $this->jsonDecode();
        if ($this->isCsrfTokenValid('course_class_enrolment_copy', $content['_token'])) {
            $update = 0;
            $insert = 0;
            foreach ($content['selected'] as $participant) {
                $person = ProviderFactory::getRepository(CourseClassPerson::class)->find($participant['id'])->getPerson();
                $ccp = ProviderFactory::getRepository(CourseClassPerson::class)->findOneBy(['person' => $person, 'courseClass' => $class]) ?: new CourseClassPerson($class);
                $id = $ccp->getId();
                $ccp->setPerson($person)
                    ->setRole($participant['role']);
                $ccp->isReportable();
                $errors = $validator->validate($ccp);
                if (count($errors) > 0) {
                    foreach ($errors as $error) $this->getStatusManager()->error($error->getMessage(), [], false);
                    dump($ccp);
                    return  $this->getStatusManager()->toJsonResponse();
                }
                ProviderFactory::create(CourseClassPerson::class)->persistFlush($ccp,false);
                $x = $ccp->getId() === $id ? $update++ : $insert++;
            }
            ProviderFactory::create(CourseClassPerson::class)->flush();
            if ($this->isStatusSuccess()) {
                $this->getStatusManager()->info('Course Class participants were modified for "{name}". Modified: {modified}, Added: {added}', ['{name}' => $class->getFullName(), '{modified}' => $update, '{added}' => $insert],'Enrolment');
            }
        } else {
            $this->getStatusManager()->error(StatusManager::INVALID_TOKEN);
        }
        return $this->getStatusManager()->toJsonResponse();
    }

    /**
     * markAsLeft
     *
     * 14/09/2020 13:46
     * @param CourseClass $class
     * @param ValidatorInterface $validator
     * @param CourseClassParticipantPagination $pagination
     * @Route("/course/class/{class}/enrolment/mark/as/left/",name="course_class_enrolment_mark_as_left")
     * @IsGranted("ROLE_ROUTE")
     * @return JsonResponse
     */
    public function markAsLeft(CourseClass $class, ValidatorInterface $validator, CourseClassParticipantPagination $pagination)
    {
        $content = $this->jsonDecode();
        if ($this->isCsrfTokenValid($pagination->getPaginationTokenName(), $content['_token'])) {
            foreach ($content['selected'] as $participant) {
                $ccp = ProviderFactory::getRepository(CourseClassPerson::class)->find($participant['id']);
                if (strpos($ccp->getRole(), 'Student') === 0) {
                    $ccp->setRole('Student - Left');
                } else {
                    $ccp->setRole('Teacher - Left');
                }
                $errors = $validator->validate($ccp);
                if (count($errors) > 0) {
                    foreach ($errors as $error) $this->getStatusManager()->error($error->getMessage(), [], false);
                    return  $this->getStatusManager()->toJsonResponse();
                }
                ProviderFactory::create(CourseClassPerson::class)->persistFlush($ccp,false);
            }
            ProviderFactory::create(CourseClassPerson::class)->flush();
            if ($this->isStatusSuccess()) {
                $this->getStatusManager()->info('Course Class participants were modified for "{name}". Modified: {modified}, Added: {added}', ['{name}' => $class->getFullName(), '{modified}' => count($content['selected']), '{added}' => 0],'Enrolment');
            }
            $this->getStatusManager()->setReDirect($this->generateUrl('course_class_enrolment_manage', ['class' => $class->getId()]), true);
        } else {
            $this->getStatusManager()->error(StatusManager::INVALID_TOKEN);
        }
        return $this->getStatusManager()->toJsonResponse();
    }

    /**
     * removeFromClass
     *
     * 14/09/2020 13:46
     * @param CourseClass $class
     * @param CourseClassParticipantPagination $pagination
     * @Route("/course/class/{class}/enrolment/remove/from/class/",name="course_class_enrolment_remove_from_class")
     * @IsGranted("ROLE_ROUTE")
     * @return JsonResponse
     */
    public function removeFromClass(CourseClass $class, CourseClassParticipantPagination $pagination)
    {
        $content = $this->jsonDecode();
        if ($this->isCsrfTokenValid($pagination->getPaginationTokenName(), $content['_token'])) {
            foreach ($content['selected'] as $q=>$participant) {
                ProviderFactory::create(CourseClassPerson::class)->delete($participant['id'], false);
            }
            ProviderFactory::create(CourseClassPerson::class)->flush();
            if ($this->isStatusSuccess()) {
                $this->getStatusManager()->info('Course Class participants were removed from "{name}". Removed Participant Count: {removed}', ['{name}' => $class->getFullName(), '{removed}' => count($content['selected'])],'Enrolment');
            }
            $this->getStatusManager()->setReDirect($this->generateUrl('course_class_enrolment_manage', ['class' => $class->getId()]), true);
        } else {
            $this->getStatusManager()->error(StatusManager::INVALID_TOKEN);
        }
        return $this->getStatusManager()->toJsonResponse();
    }
}
