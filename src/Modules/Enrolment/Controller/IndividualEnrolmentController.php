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
use App\Modules\Enrolment\Form\CourseClassPersonType;
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
        $form = $this->createForm(IndividualEnrolmentClassListType::class, new IndividualEnrolment(), ['action' => $this->generateUrl('individual_enrolment_manage', ['person' => $person->getId()]), 'person' => $person]);

        $pagination->setContent(ProviderFactory::getRepository(CourseClassStudent::class)->findIndividualClassEnrolmentContent($person))
            ->setToken($csrfTokenManager);

        if ($this->isPostContent()) {
            $content = $this->jsonDecode();
            $form->submit($content);
            if ($form->isValid()) {
                foreach ($content['classes'] as $id) {
                    $class = ProviderFactory::getRepository(CourseClass::class)->find($id);
                    if ($class) {
                        $ccp = ProviderFactory::getRepository(CourseClassStudent::class)->findOneBy(['courseClass' => $class, 'person' => $person]) ?: new CourseClassStudent($class);
                        $ccp->setPerson($person)
                            ->setRole($content['role'])
                            ->mirrorReportable();
                        ProviderFactory::create(CourseClassStudent::class)->persistFlush($ccp, false);
                    }
                }
                ProviderFactory::create(CourseClassStudent::class)->flush();
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
            ->createBreadcrumbs(
                [
                    '{name}',
                    ['{name}' => $person->getFullName()],
                    'messages'
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
     * 11/09/2020 15:32
     * @param CourseClassStudent $ccp
     * @Route("/individual/enrolment/{ccp}/edit/",name="individual_enrolment_edit")
     * @IsGranted("ROLE_ROUTE")
     * @return JsonResponse
     */
    public function individualParticipationEdit(CourseClassStudent $ccp)
    {
        $form = $this->createForm(CourseClassPersonType::class, $ccp, ['action' => $this->generateUrl('individual_enrolment_edit', ['ccp' => $ccp->getId()])]);

        if ($this->isPostContent()) {
            $content = $this->jsonDecode();
            $form->submit($content);
            if ($form->isValid()) {
                ProviderFactory::create(CourseClassStudent::class)->persistFlush($ccp);
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
                        'trans_params' => ['{name}' => $ccp->getPerson()->getFullName()],
                        'uri_params' => ['person' => $ccp->getPerson()->getId()]
                    ],
                ]
            )
            ->render(
                [
                    'containers' => $this->getContainerManager()
                        ->singlePanel($form->createView())
                        ->setReturnRoute($this->generateUrl('individual_enrolment_manage', ['person' => $ccp->getPerson()->getId()]))
                        ->getBuiltContainers(),
                ]
            )
        ;
    }

    /**
     * individualParticipantRemove
     *
     * 14/09/2020 09:52
     * @param IndividualClassEnrolmentPagination $pagination
     * @param CourseClassStudent $ccp
     * @Route("/individual/enrolment/{ccp}/remove/",name="individual_enrolment_remove")
     * @IsGranted("ROLE_ROUTE")
     * @return JsonResponse
     */
    public function removeIndividualParticipant(IndividualClassEnrolmentPagination $pagination, CourseClassStudent $ccp)
    {
        $person = $ccp->getPerson();
        ProviderFactory::create(CourseClassStudent::class)->delete($ccp);

        return $this->manageIndividualEnrolment($person, $pagination);
    }

    /**
     * removeSelected
     *
     * 14/09/2020 13:51
     * @param IndividualClassEnrolmentPagination $pagination
     * @Route("/individual/enrolment/{person}/remove/selected/",name="individual_enrolment_remove_selected")
     * @IsGranted("ROLE_ROUTE")
     * @return JsonResponse
     */
    public function removeSelected(IndividualClassEnrolmentPagination $pagination)
    {
        $content = $this->jsonDecode();
        $person = null;
        if ($this->isCsrfTokenValid($pagination->getPaginationTokenName(), $content['_token'])) {
            foreach ($content['selected'] as $q=>$participant) {
                if (!$person) {
                    $person = ProviderFactory::getRepository(CourseClassStudent::class)->find($participant['id'])->getPerson();
                }
                ProviderFactory::create(CourseClassStudent::class)->delete($participant['id'], false);
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

    /**
     * removeSelected
     *
     * 14/09/2020 13:51
     * @param IndividualClassEnrolmentPagination $pagination
     * @Route("/individual/enrolment/mark/selected/as/left/",name="individual_enrolment_mark_selected_as_left")
     * @IsGranted("ROLE_ROUTE")
     * @return JsonResponse
     */
    public function markSelectedAsLeft(IndividualClassEnrolmentPagination $pagination)
    {
        $content = $this->jsonDecode();
        if ($this->isCsrfTokenValid($pagination->getPaginationTokenName(), $content['_token'])) {
            foreach ($content['selected'] as $q=>$participant) {
                $ccp = ProviderFactory::getRepository(CourseClassStudent::class)->find($participant['id']);
                if (strpos($ccp->getRole(), 'Student') === 0) {
                    $ccp->setRole('Student - Left');
                } else {
                    $ccp->setRole('Teacher - Left');
                }
                ProviderFactory::create(CourseClassStudent::class)->persistFlush($ccp, false);
                $person = $ccp->getPerson();
            }
            ProviderFactory::create(CourseClassStudent::class)->flush();
            if ($this->isStatusSuccess()) {
                $this->getStatusManager()->info('individual_enrolment_selected_mark_as_left', ['name' => $person->getFullName(), 'count' => count($content['selected'])],'Enrolment');
            }
            $this->getStatusManager()->setReDirect($this->generateUrl('individual_enrolment_manage', ['person' => $person->getId()]), true);
        } else {
            $this->getStatusManager()->error(StatusManager::INVALID_TOKEN);
        }
        return $this->getStatusManager()->toJsonResponse();
    }
}
