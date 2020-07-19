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
     * @param Person $person
     * @return Response
     * @Route("/staff/{person}/edit/",name="staff_edit",methods={"POST"})
     * @IsGranted("ROLE_ROUTE")
     * 19/07/2020 09:21
     */
    public function editStaff(ContainerManager $manager, Person $person)
    {
        if ($this->getRequest()->getContentType() === 'json') {

            $staff = $person->getStaff() ?: new Staff();
            $staff->setPerson($person);

            $form = $this->createForm(StaffType::class, $staff,
                [
                    'action' => $this->generateUrl('staff_edit', ['person' => $person->getId()]),
                ]
            );

            return $this->saveContent($form, $manager, $staff);
        } else {
            $form = $this->createForm(StaffType::class, $person->getSTaff(),
                [
                    'action' => $this->generateUrl('staff_edit', ['person' => $person->getId()]),
                ]
            );
            $data = ErrorMessageHelper::getInvalidInputsMessage([], true);
            $manager->singlePanel($form->createView());
            $data['form'] = $manager->getFormFromContainer();
            return new JsonResponse($data);
        }
    }
    /**
     * edit
     * @param ContainerManager $manager
     * @param Person $person
     * @return Response
     * @Route("/staff/{person}/school/edit/",name="staff_school_edit",methods={"POST"})
     * @IsGranted("ROLE_ROUTE")
     * 19/07/2020 09:21
     */
    public function editSchoolStaff(ContainerManager $manager, Person $person)
    {
        if ($this->getRequest()->getContentType() === 'json') {

            $staff = $person->getStaff() ?: new Staff();
            $staff->setPerson($person);

            $form = $this->createForm(SchoolStaffType::class, $staff,
                [
                    'action' => $this->generateUrl('staff_school_edit', ['person' => $person->getId()]),
                    'remove_personal_background' => $this->generateUrl('staff_personal_background_remove', ['person' => $person->getId()]),
                ]
            );

            return $this->saveContent($form, $manager, $staff);
        } else {
            $staff = $person->getStaff() ?: new Staff($person);
            $form = $this->createForm(SchoolStaffType::class, $staff,
                [
                    'action' => $this->generateUrl('staff_school_edit', ['person' => $person->getId()]),
                    'remove_personal_background' => $this->generateUrl('staff_personal_background_remove', ['person' => $person->getId()]),
                ]
            );
            $data = ErrorMessageHelper::getInvalidInputsMessage([], true);
            $manager->singlePanel($form->createView());
            $data['form'] = $manager->getFormFromContainer();
            return new JsonResponse($data);
        }
    }

    /**
     * staffDeletePersonalBackground
     * @param Person $person
     * @return JsonResponse
     * @Route("/staff/{person}/personal/background/remove/",name="staff_personal_background_remove")
     * @IsGranted("ROLE_ROUTE")
     * 19/07/2020 10:23
     */
    public function staffDeletePersonalBackground(Person $person)
    {
        $staff = $person->getStaff();

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
            $data = ProviderFactory::create(Staff::class)->persistFlush($person, []);
            if ($data['status'] === 'success') {
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
     * @return JsonResponse
     * 19/07/2020 16:29
     */
    private function saveContent(FormInterface $form, ContainerManager $manager, Staff $staff)
    {
        $content = json_decode($this->getRequest()->getContent(), true);

        $form->submit($content);
        $data = [];
        if ($form->isValid()) {
            $data = ProviderFactory::create(Staff::class)->persistFlush($staff,$data);
            if ($data['status'] !== 'success') {
                $data = ErrorMessageHelper::getDatabaseErrorMessage($data, true);
                $manager->singlePanel($form->createView());
                $data['form'] = $manager->getFormFromContainer();
                return new JsonResponse($data);
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
