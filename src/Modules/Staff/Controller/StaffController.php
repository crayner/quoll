<?php
/**
 * Created by PhpStorm.
 *
 * quoll
 * (c) 2020 Craig Rayner <craig@craigrayner.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * User: craig
 * Date: 3/05/2020
 * Time: 14:44
 */
namespace App\Modules\Staff\Controller;

use App\Container\ContainerManager;
use App\Controller\AbstractPageController;
use App\Manager\EntitySortManager;
use App\Modules\People\Manager\Hidden\FamilyAdultSort;
use App\Modules\Staff\Entity\StaffAbsenceType;
use App\Modules\Staff\Form\StaffAbsenceTypeType;
use App\Modules\Staff\Pagination\StaffAbsenceTypePagination;
use App\Provider\ProviderFactory;
use App\Util\ErrorMessageHelper;
use Doctrine\DBAL\Driver\PDOException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class StaffController
 * @package App\Modules\Staff\Controller
 */
class StaffController extends AbstractPageController
{
    /**
     * view
     * @Route("/staff/view/", name="staff_view")
     * @param ContainerManager $manager
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function view(ContainerManager $manager)
    {
        $manager->setContent('<h3>View Staff</h3><p>@todo</p>');
        return $this->getPageManager()->createBreadcrumbs('View Staff')
            ->render(['containers' => $manager->getBuiltContainers()]);
    }

    /**
     * staffAbsenceTypeDelete
     * @param StaffAbsenceType $absenceType
     * @return RedirectResponse
     * @Route("/staff/absence/{absenceType}/type/delete/", name="staff_absence_type_delete")
     * @IsGranted("ROLE_ROUTE")
     */
    public function staffAbsenceTypeDelete(StaffAbsenceType $absenceType)
    {
        if ($absenceType instanceof StaffAbsenceType) {
            try {
                $em = ProviderFactory::getEntityManager();
                $em->remove($absenceType);
                $em->flush();
                $this->addFlash('success', ErrorMessageHelper::onlySuccessMessage());
            } catch (\PDOException | PDOException $e) {
                $this->addFlash('error', ErrorMessageHelper::onlyDatabaseErrorMessage());
            }
        } else {
            $this->addFlash('warning', ErrorMessageHelper::onlyInvalidInputsMessage());
        }

        return $this->redirectToRoute('staff_settings');
    }

    /**
     * staffAbsenceTypeEdit
     * @param ContainerManager $manager
     * @param StaffAbsenceType $absenceType
     * @return RedirectResponse|JsonResponse
     * @Route("/staff/absence/{absenceType}/type/edit/", name="staff_absence_type_edit")
     * @Route("/staff/absence/type/add/", name="staff_absence_type_add")
     * @IsGranted("ROLE_ROUTE")
     */
    public function staffAbsenceTypeEdit(ContainerManager $manager, ?StaffAbsenceType $absenceType = null)
    {
        $absenceType = $absenceType ?: new StaffAbsenceType();

        $route = $absenceType->getId() !== null ? $this->generateUrl('staff_absence_type_edit', ['absenceType' => $absenceType->getId()]) : $this->generateUrl('staff_absence_type_add');

        $form = $this->createForm(StaffAbsenceTypeType::class, $absenceType, ['action' => $route]);

        if ($this->getRequest()->getContent() !== '') {
            $content = json_decode($this->getRequest()->getContent(), true);
            $form->submit($content);
            $data = [];
            $data['status'] = 'success';
            if ($form->isValid()) {
                $id = $absenceType->getId();
                $data = ProviderFactory::create(StaffAbsenceType::class)->persistFlush($absenceType, $data);
                if ($data['status'] === 'success') {
                    if ($id !== $absenceType->getId()) {
                        $data['status'] = 'redirect';
                        $data['redirect'] = $this->generateUrl('staff_absence_type_edit', ['absenceType' => $absenceType->getId()]);
                        $this->addFlash('success', ErrorMessageHelper::onlySuccessMessage());
                    }
                    $route = $absenceType->getId() !== null ? $this->generateUrl('staff_absence_type_edit', ['absenceType' => $absenceType->getId()]) : $this->generateUrl('staff_absence_type_add');
                    $form = $this->createForm(StaffAbsenceTypeType::class, $absenceType, ['action' => $route]);
                }
            } else {
                $data = ErrorMessageHelper::getInvalidInputsMessage([], true);
            }

            $manager->singlePanel($form->createView());
            $data['form'] = $manager->getFormFromContainer();
            return new JsonResponse($data, 200);
        }

        $manager->setReturnRoute($this->generateUrl('staff_settings', ['tabName' => 'List']));
        if ($absenceType->getId() !== null) {
            $manager->setAddElementRoute($this->generateUrl('staff_absence_type_add'));
        }

        $manager->singlePanel($form->createView());

        return $this->getPageManager()->createBreadcrumbs($absenceType->getId() === null ? 'Add Absence Type' : 'Edit Absence Type')
            ->render(
                [
                    'containers' => $manager->getBuiltContainers(),
                ]
            );
    }

    /**
     * staffAbsenceTypeSort
     * @param StaffAbsenceType $source
     * @param StaffAbsenceType $target
     * @param StaffAbsenceTypePagination $pagination
     * @return JsonResponse
     * @Route("/staff/absence/type/{source}/{target}/sort/", name="staff_absence_type_sort")
     * @IsGranted("ROLE_ROUTE")
     */
    public function staffAbsenceTypeSort(StaffAbsenceType $source, StaffAbsenceType $target, StaffAbsenceTypePagination $pagination)
    {
        $manager = new EntitySortManager($source, $target, $pagination, 'sequenceNumber', 'sequence_number');
        $manager->setContent($manager->getContent());

        return new JsonResponse($manager->getDetails());
    }
}
