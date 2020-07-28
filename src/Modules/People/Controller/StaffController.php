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
 * Date: 19/07/2020
 * Time: 09:21
 */
namespace App\Modules\People\Controller;

use App\Container\ContainerManager;
use App\Modules\People\Entity\Person;
use App\Modules\People\Form\SchoolStaffType;
use App\Modules\Staff\Entity\Staff;
use App\Modules\Staff\Form\StaffType;
use App\Provider\ProviderFactory;
use App\Util\ErrorMessageHelper;
use Doctrine\DBAL\Driver\PDOException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class StaffController
 * @package App\Modules\People\Controller
 * @author Craig Rayner <craig@craigrayner.com>
 */
class StaffController extends PeopleController
{
    /**
     * edit
     * @param ContainerManager $manager
     * @param Staff $staff
     * @return Response
     * @Route("/staff/{staff}/edit/",name="staff_edit",methods={"POST"})
     * @IsGranted("ROLE_ROUTE")
     * 19/07/2020 09:21
     */
    public function editStaff(ContainerManager $manager, Staff $staff)
    {
        if ($this->getRequest()->getContentType() === 'json') {

            $form = $this->createStaffForm($staff);

            return $this->saveContent($form, $manager, $staff, 'Staff');
        } else {
            $form = $this->createStaffForm($staff);
            $data = ErrorMessageHelper::getInvalidInputsMessage([], true);
            $manager->singlePanel($form->createView());
            $data['form'] = $manager->getFormFromContainer();
            return new JsonResponse($data);
        }
    }

    /**
     * edit
     * @param ContainerManager $manager
     * @param Staff $staff
     * @return Response
     * @Route("/staff/{staff}/school/edit/",name="staff_school_edit",methods={"POST"})
     * @IsGranted("ROLE_ROUTE")
     * 19/07/2020 09:21
     */
    public function editSchoolStaff(ContainerManager $manager, Staff $staff)
    {
        if ($this->getRequest()->getContentType() === 'json') {

            $form = $this->createSchoolStaffForm($staff);

            return $this->saveContent($form, $manager, $staff, 'School');
        } else {
            $form = $this->createSchoolStaffForm($staff);
            $data = ErrorMessageHelper::getInvalidInputsMessage([], true);
            $manager->singlePanel($form->createView());
            $data['form'] = $manager->getFormFromContainer();
            return new JsonResponse($data);
        }
    }

    /**
     * staffDeletePersonalBackground
     * @param Staff $staff
     * @return JsonResponse
     * @Route("/staff/{staff}/personal/background/remove/",name="staff_personal_background_remove")
     * @IsGranted("ROLE_ROUTE")
     * 19/07/2020 10:23
     */
    public function staffDeletePersonalBackground(Staff $staff)
    {
        $staff->removePersonalBackground();

        $data = ProviderFactory::create(Staff::class)->persistFlush($staff, []);

        return new JsonResponse($data);
    }

    /**
     * addToStaff
     * @param Person $person
     * @Route("/staff/{person}/add/",name="staff_add")
     * @IsGranted("ROLE_ROUTE")
     * @return JsonResponse
     * 19/07/2020 12:13
     */
    public function addToStaff(Person $person)
    {
        if (null === $person->getStaff()) {
            $staff = new Staff($person);
            $em = $this->getDoctrine()->getManager();
            try {
                $em->persist($staff);
                $staff->setPerson($person);
                $person->getSecurityUser()->addSecurityRole('ROLE_STAFF');
                $em->persist($person);
                $em->flush();
                $data['status'] = 'redirect';
                $data['redirect'] = $this->generateUrl('person_edit', ['person' => $person->getId(), 'tabName' => 'Staff']);
                $this->addFlash('warning', ErrorMessageHelper::onlyNothingToDoMessage());
            } catch (PDOException | \PDOException | \Exception $e) {
                $data = [];
                $data['errors'][] = ['class' => 'error', 'message' => ErrorMessageHelper::onlyDatabaseErrorMessage(true)];
                $data['status'] = 'redirect';
                $data['redirect'] = $this->generateUrl('person_edit', ['person' => $person->getId(), 'tabName' => 'Staff']);
            }
       } else {
            $data['status'] = 'redirect';
            $data['redirect'] = $this->generateUrl('person_edit', ['person' => $person->getId(), 'tabName' => 'Staff']);
            $this->addFlash('warning', ErrorMessageHelper::onlyNothingToDoMessage());
        }
        return new JsonResponse($data);
    }

    /**
     * saveContent
     * @param FormInterface $form
     * @param ContainerManager $manager
     * @param Staff $staff
     * @param string $tabName
     * @return JsonResponse
     * 19/07/2020 16:29
     */
    private function saveContent(FormInterface $form, ContainerManager $manager, Staff $staff, string $tabName)
    {
        $content = json_decode($this->getRequest()->getContent(), true);

        $form->submit($content);
        $data = [];
        if ($form->isValid()) {
            $data = ProviderFactory::create(Staff::class)->persistFlush($staff, $data);
            if ($data['status'] !== 'success') {
                $data = ErrorMessageHelper::getDatabaseErrorMessage($data, true);
                $manager->singlePanel($form->createView());
                $data['form'] = $manager->getFormFromContainer();
                return new JsonResponse($data);
            } else {
                if ($tabName === 'School') {
                    $form = $this->createSchoolStaffForm($staff);
                } else {
                    $form = $this->createStaffForm($staff);
                }
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

    /**
     * createSchoolStaffForm
     * @param Staff $staff
     * @return FormInterface
     * 20/07/2020 10:57
     */
    private function createSchoolStaffForm(Staff $staff): FormInterface
    {
        return $this->createForm(SchoolStaffType::class, $staff,
            [
                'action' => $this->generateUrl('staff_school_edit', ['staff' => $staff->getId()]),
                'remove_personal_background' => $this->generateUrl('staff_personal_background_remove', ['staff' => $staff->getId()]),
            ]
        );
    }

    /**
     * createSchoolStaffForm
     * @param Staff $staff
     * @return FormInterface
     * 20/07/2020 10:57
     */
    private function createStaffForm(Staff $staff): FormInterface
    {
        return $this->createForm(StaffType::class, $staff,
            [
                'action' => $this->generateUrl('staff_edit', ['staff' => $staff->getId()]),
            ]
        );
    }
}
