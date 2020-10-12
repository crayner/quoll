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
 * Date: 10/09/2020
 * Time: 13:18
 */
namespace App\Modules\Enrolment\Controller;

use App\Container\Container;
use App\Container\Panel;
use App\Container\Section;
use App\Controller\AbstractPageController;
use App\Manager\StatusManager;
use App\Modules\Enrolment\Entity\CourseClass;
use App\Modules\Enrolment\Entity\CourseClassStudent;
use App\Modules\Enrolment\Entity\CourseClassTutor;
use App\Modules\Enrolment\Form\CourseClassTutorType;
use App\Modules\Enrolment\Form\CourseClassStudentType;
use App\Modules\Enrolment\Form\IndividualEnrolmentClassListType;
use App\Modules\Enrolment\Manager\Hidden\IndividualEnrolment;
use App\Modules\Enrolment\Pagination\IndividualClassEnrolmentPagination;
use App\Modules\Enrolment\Pagination\IndividualEnrolmentPagination;
use App\Modules\People\Entity\Person;
use App\Modules\School\Util\AcademicYearHelper;
use App\Provider\ProviderFactory;
use App\Util\TranslationHelper;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

/**
 * Class IndividualEnrolmentController
 * @package App\Modules\Enrolment\Controller
 * @author Craig Rayner <craig@craigrayner.com>
 */
class IndividualEnrolmentController extends AbstractPageController
{
    /**
     * individualEnrolment
     *
     * 10/09/2020 13:20
     * @param IndividualEnrolmentPagination $pagination
     * @param CsrfTokenManagerInterface $csrfTokenManager
     * @return JsonResponse
     * @Route("/individual/enrolment/list/",name="individual_enrolment_list")
     * @IsGranted("ROLE_ROUTE")
     */
    public function individualEnrolment(IndividualEnrolmentPagination $pagination, CsrfTokenManagerInterface $csrfTokenManager)
    {
        $pagination->setContent(ProviderFactory::create(Person::class)->getIndividualEnrolmentPaginationContent())
            ->setToken($csrfTokenManager)
            ->setPageMax(50);

        $container = new Container();
        $panel = new Panel('null', 'Enrolment', new Section('html', $this->renderView('enrolment/individual_search_warning.html.twig')));
        $panel->addSection(new Section('pagination', $pagination));
        $container->addPanel(AcademicYearHelper::academicYearWarning($panel));

        return $this->getPageManager()
            ->createBreadcrumbs('Individual Enrolment')
            ->setUrl($this->generateUrl('individual_enrolment_list'))
            ->setMessages($this->getStatusManager()->getMessageArray())
            ->render([
                'containers' => $this->getContainerManager()
                    ->addContainer($container)
                    ->getBuiltContainers(),
            ]);
    }

    /**
     * manageIndividualEnrolment
     *
     * 14/09/2020 14:24
     * @param Person $person
     * @param IndividualClassEnrolmentPagination $pagination
     * @param CsrfTokenManagerInterface $csrfTokenManager
     * @Route("/individual/enrolment/{person}/manage/",name="individual_enrolment_manage")
     * @IsGranted("ROLE_ROUTE")
     * @return JsonResponse
     */
    public function manageIndividualEnrolment(Person $person, IndividualClassEnrolmentPagination $pagination, CsrfTokenManagerInterface $csrfTokenManager)
    {
        if ($person->isStudent()) {
            $form = $this->createForm(IndividualEnrolmentClassListType::class, new IndividualEnrolment(), ['action' => $this->generateUrl('individual_enrolment_manage', ['person' => $person->getId()]), 'person' => $person]);
        } else {
            $form = $this->createForm(IndividualEnrolmentClassListType::class, new IndividualEnrolment(), ['action' => $this->generateUrl('individual_enrolment_manage', ['person' => $person->getId()]), 'person' => $person]);
        }

        $pagination->setPerson($person)
            ->setContent(ProviderFactory::create(CourseClassStudent::class)->getIndividualClassEnrolmentContent($person))
            ->setToken($csrfTokenManager);

        if ($this->isPostContent()) {
            $content = $this->jsonDecode();
            $form->submit($content);
            if ($form->isValid()) {
                $flush = false;
                foreach ($content['classes'] as $id) {
                    $class = ProviderFactory::getRepository(CourseClass::class)->find($id);
                    if ($class instanceof CourseClass) {
                        if ($person->isStudent()) {
                            $enrolment = ProviderFactory::getRepository(CourseClassStudent::class)->findOneBy(['courseClass' => $class, 'student' => $person->getStudent()]) ?: new CourseClassStudent($class);
                            $enrolment->setStudent($person->getStudent())
                                ->mirrorReportable();
                            ProviderFactory::create(CourseClassStudent::class)->persist($enrolment);
                            $flush = true;
                        } else if ($person->isStaff()) {
                            $tutor = ProviderFactory::getRepository(CourseClassTutor::class)->findOneBy(['courseClass' => $class, 'staff' => $person->getStaff()]) ?: new CourseClassTutor($class);
                            $tutor->setStaff($person->getStaff());
                            $class->addTutor($tutor);
                            ProviderFactory::create(CourseClassTutor::class)->persist($tutor);
                            $flush = true;
                        }
                    }
                }
                if ($flush) ProviderFactory::create(CourseClass::class)->flush();
                if ($this->isStatusSuccess()) {
                    $this->getStatusManager()->info('count records where updated.',['count' => count($content['classes'])],'Enrolment');
                    $this->getStatusManager()->setReDirect( $this->generateUrl('individual_enrolment_manage', ['person' => $person->getId()]), true);
                }
            } else {
                $this->getStatusManager()->invalidInputs();
            }

            return $this->singleForm($form);
        }

        $container = new Container();
        $panel = new Panel('Current', 'Enrolment', new Section('html', '<h3>'.TranslationHelper::translate('Add Classes').'</h3>'));
        $panel->addSection(new Section('form', 'classes'));
        $panel->addSection(new Section('html', '<h3>'.TranslationHelper::translate('Current Enrolment').'</h3>'));
        $panel->addSection(new Section('pagination', $pagination));
        $container->addForm('classes', $form->createView())
            ->addPanel(AcademicYearHelper::academicYearWarning($panel));

        return $this->getPageManager()
            ->setMessages($this->getStatusManager()->getMessageArray())
            ->setUrl($this->generateUrl('individual_enrolment_manage', ['person' => $person->getId()]))
            ->setTitle(['Individual Enrolment {name}', ['{name}' => $person->getFullName()], 'Enrolment'])
            ->createBreadcrumbs(
                [
                    '{name}',
                    ['{name}' => $person->getFullName()],
                    'Enrolment'
                ],
                [
                    ['uri' => 'individual_enrolment_list', 'name' => 'Individual Enrolment']
                ]
            )
            ->render(
                [
                    'containers' => $this->getContainerManager()
                        ->addContainer($container)
                        ->getBuiltContainers(),
                ]
            )
        ;
    }

    /**
     * individualParticipationEdit
     *
     * 18/09/2020 08:53
     * @param CourseClass $class
     * @param Person $person
     * @Route("/individual/enrolment/{class}/person/{person}/edit/",name="individual_enrolment_edit")
     * @IsGranted("ROLE_ROUTE")
     * @return JsonResponse
     */
    public function individualParticipationEdit(CourseClass $class, Person $person)
    {
        if ($person->isStudent()) {
            $enrolment = ProviderFactory::getRepository(CourseClassStudent::class)->findOneBy(['courseClass' => $class, 'student' => $person->getStudent()]);
            if (!$enrolment instanceof CourseClassStudent) {
                $this->getStatusManager()->invalidInputs();
                return $this->generateJsonResponse();
            }
            $form = $this->createForm(CourseClassStudentType::class, $enrolment, ['action' => $this->generateUrl('individual_enrolment_edit', ['class' => $class->getId(), 'person' => $person->getId()])]);
        }
        if ($person->isStaff()) {
            $enrolment = ProviderFactory::getRepository(CourseClassTutor::class)->findOneBy(['courseClass' => $class, 'student' => $person->getStaff()]);
            if (!$enrolment instanceof CourseClassTutor) {
                $this->getStatusManager()->invalidInputs();
                return $this->generateJsonResponse();
            }
            $form = $this->createForm(CourseClassTutorType::class, $enrolment, ['action' => $this->generateUrl('individual_enrolment_edit', ['class' => $class->getId(), 'person' => $person->getId()])]);
        }

        if ($this->isPostContent()) {
            $content = $this->jsonDecode();
            $form->submit($content);
            if ($form->isValid()) {
                if ($person->isStudent()) {
                    ProviderFactory::create(CourseClassStudent::class)->persistFlush($enrolment);
                } else {
                    ProviderFactory::create(CourseClassTutor::class)->persistFlush($enrolment);
                }
            } else {
                $this->getStatusManager()->invalidInputs();
            }
            return $this->singleForm($form);
        }

        return $this->getPageManager()
            ->createBreadcrumbs('Edit Class Participant',
                [
                    [
                        'uri' => 'individual_enrolment_list',
                        'name' => 'Individual Enrolment'
                    ],
                    [
                        'uri' => 'individual_enrolment_manage',
                        'name' => '{name}',
                        'trans_params' => ['{name}' => $person->getFullName()],
                        'uri_params' => ['person' => $person->getId()]
                    ],
                ]
            )
            ->render(
                [
                    'containers' => $this->getContainerManager()
                        ->singlePanel($form->createView())
                        ->setReturnRoute($this->generateUrl('individual_enrolment_manage', ['person' => $person->getId()]))
                        ->getBuiltContainers(),
                ]
            );
    }

    /**
     * removeIndividualParticipant
     *
     * 18/09/2020 09:20
     * @param CourseClass $class
     * @param Person $person
     * @param IndividualClassEnrolmentPagination $pagination
     * @param CsrfTokenManagerInterface $csrfTokenManager
     * @Route("/individual/enrolment/{class}/person/{person}/remove/",name="individual_enrolment_remove")
     * @IsGranted("ROLE_ROUTE")
     * @return JsonResponse
     */
    public function removeIndividualParticipant(CourseClass $class, Person $person, IndividualClassEnrolmentPagination $pagination, CsrfTokenManagerInterface $csrfTokenManager)
    {
        if ($person->isStudent()) {
            $student = ProviderFactory::getRepository(CourseClassStudent::class)->findOneBy(['courseClass' => $class, 'student' => $person->getStudent()]);
            ProviderFactory::create(CourseClassStudent::class)->delete($student);
        } else if ($person->isStaff()) {
            $tutor = ProviderFactory::getRepository(CourseClassTutor::class)->findOneBy(['courseClass' => $class, 'staff' => $person->getStaff()]);
            ProviderFactory::create(CourseClassTutor::class)->delete($tutor);
        }
        return $this->manageIndividualEnrolment($person, $pagination, $csrfTokenManager);
    }

    /**
     * removeSelected
     *
     * 14/09/2020 13:51
     * @param IndividualClassEnrolmentPagination $pagination
     * @return JsonResponse
     * @Route("/individual/enrolment/remove/selected/",name="individual_enrolment_remove_selected")
     * @IsGranted("ROLE_ROUTE")
     */
    public function removeSelected(IndividualClassEnrolmentPagination $pagination)
    {
        $content = $this->jsonDecode();
        $person = null;
        if ($this->isCsrfTokenValid($pagination->getPaginationTokenName(), $content['_token'])) {
            foreach ($content['selected'] as $q=>$participant) {
                $person = $person ?: ProviderFactory::getRepository(Person::class)->find($participant['person_id']);
                if ($person->isStudent()) {
                    ProviderFactory::create(CourseClassStudent::class)->delete($participant['id'], false);
                } else {
                    ProviderFactory::create(CourseClassTutor::class)->delete($participant['id'], false);
                }
            }
            ProviderFactory::create(CourseClassStudent::class)->flush();
            if ($this->isStatusSuccess()) {
                $this->getStatusManager()->info('individual_enrolment_selected_remove', ['name' => $person->getFullName(), 'count' => count($content['selected'])],'Enrolment');
            }
            $this->getStatusManager()->setReDirect($this->generateUrl('individual_enrolment_manage', ['person' => $person->getId()]), true);
        } else {
            $this->getStatusManager()->error(StatusManager::INVALID_TOKEN);
        }
        return $this->getStatusManager()->toJsonResponse();
    }
}
