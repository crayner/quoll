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
use App\Manager\StatusManager;
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
     * editStaff
     *
     * 20/08/2020 14:25
     * @param Staff $staff
     * @Route("/staff/{staff}/edit/",name="staff_edit",methods={"POST"})
     * @IsGranted("ROLE_ROUTE")
     * @return JsonResponse
     */
    public function editStaff(Staff $staff)
    {
        if ($this->getRequest()->getContentType() === 'json') {

            $form = $this->createStaffForm($staff);

            return $this->saveContent($form, $staff, 'Staff');
        } else {
            $form = $this->createStaffForm($staff);
            $this->getStatusManager()->error(StatusManager::INVALID_INPUTS);
            return $this->singleForm($form);
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
    public function editSchoolStaff(Staff $staff)
    {
        if ($this->getRequest()->getContentType() === 'json') {
            $form = $this->createSchoolStaffForm($staff);
            return $this->saveContent($form, $staff, 'School');
        } else {
            $form = $this->createSchoolStaffForm($staff);
            $this->getStatusManager()->error(StatusManager::INVALID_INPUTS);
            return $this->singleForm($form);
        }
    }

    /**
     * staffDeletePersonalBackground
     *
     * 20/08/2020 14:27
     * @param Staff $staff
     * @Route("/staff/{staff}/personal/background/remove/",name="staff_personal_background_remove")
     * @IsGranted("ROLE_ROUTE")
     * @return JsonResponse
     */
    public function staffDeletePersonalBackground(Staff $staff)
    {
        $staff->removePersonalBackground();

        ProviderFactory::create(Staff::class)->persistFlush($staff);

        return $this->getStatusManager()->toJsonResponse();
    }

    /**
     * addToStaff
     *
     * 20/08/2020 14:32
     * @param Person $person
     * @Route("/staff/{person}/add/",name="staff_add")
     * @IsGranted("ROLE_ROUTE")
     * @return JsonResponse
     */
    public function addToStaff(Person $person)
    {
        if (null === $person->getStaff()) {
            $staff = new Staff($person);
            $staff->setPerson($person);
            $person->getSecurityUser()->addSecurityRole('ROLE_STAFF');
            ProviderFactory::create(Staff::class)->persistFlush($staff, false);
            ProviderFactory::create(Person::class)->persistFlush($person);
            if ($this->isStatusSuccess()) {
                $this->getStatusManager()->setReDirect($this->generateUrl('person_edit', ['person' => $person->getId(), 'tabName' => 'Staff']))
                    ->convertToFlash();
            }
            return $this->getStatusManager()->toJsonResponse();
       } else {
            $this->getStatusManager()->warning(StatusManager::NOTHING_TO_DO);
            $form = $this->createStaffForm();
            return $this->singleForm($form);
        }
    }

    /**
     * saveContent
     *
     * 20/08/2020 14:34
     * @param FormInterface $form
     * @param Staff $staff
     * @param string $tabName
     * @return JsonResponse
     */
    private function saveContent(FormInterface $form, Staff $staff, string $tabName)
    {
        $content = json_decode($this->getRequest()->getContent(), true);

        $form->submit($content);
        if ($form->isValid()) {
            ProviderFactory::create(Staff::class)->persistFlush($staff);
            if ($this->isStatusSuccess()) {
                if ($tabName === 'School') {
                    $form = $this->createSchoolStaffForm($staff);
                } else {
                    $form = $this->createStaffForm($staff);
                }
            }
        } else {
            $this->getStatusManager()->error(StatusManager::INVALID_INPUTS);
        }
        return $this->singleForm($form);
    }

    /**
     * createSchoolStaffForm
     *
     * 20/08/2020 14:34
     * @param Staff $staff
     * @return FormInterface
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
     * createStaffForm
     *
     * 20/08/2020 14:34
     * @param Staff $staff
     * @return FormInterface
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
