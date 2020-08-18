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
 * Date: 3/05/2020
 * Time: 14:44
 */
namespace App\Modules\Staff\Controller;

use App\Container\Container;
use App\Container\Panel;
use App\Container\Section;
use App\Controller\AbstractPageController;
use App\Manager\EntitySortManager;
use App\Manager\StatusManager;
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
 * @author Craig Rayner <craig@craigrayner.com>
 */
class StaffController extends AbstractPageController
{
    /**
     * view
     *
     * 19/08/2020 08:47
     * @Route("/staff/view/", name="staff_view")
     * @IsGranted("ROLE_ROUTE")
     * @return JsonResponse
     */
    public function view()
    {
        $container = new Container();
        $panel = new Panel('single', 'Staff', new Section('html', $this->renderView('components/todo.html.twig')));
        $container->addPanel($panel);

        return $this->getPageManager()
            ->createBreadcrumbs('View Staff')
            ->render(
                [
                    'containers' => $this->getContainerManager()
                        ->addContainer($container)
                        ->getBuiltContainers()
                ]
            );
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
     *
     * 19/08/2020 08:42
     * @param StaffAbsenceType|null $absenceType
     * @Route("/staff/absence/{absenceType}/type/edit/", name="staff_absence_type_edit")
     * @Route("/staff/absence/type/add/", name="staff_absence_type_add")
     * @IsGranted("ROLE_ROUTE")
     * @return JsonResponse
     */
    public function staffAbsenceTypeEdit(?StaffAbsenceType $absenceType = null)
    {
        $absenceType = $absenceType ?: new StaffAbsenceType();

        $route = $absenceType->getId() !== null ? $this->generateUrl('staff_absence_type_edit', ['absenceType' => $absenceType->getId()]) : $this->generateUrl('staff_absence_type_add');

        $form = $this->createForm(StaffAbsenceTypeType::class, $absenceType, ['action' => $route]);

        if ($this->getRequest()->getContent() !== '') {
            $content = json_decode($this->getRequest()->getContent(), true);
            $form->submit($content);
            if ($form->isValid()) {
                $id = $absenceType->getId();
                ProviderFactory::create(StaffAbsenceType::class)->persistFlush($absenceType);
                if ($this->isStatusSuccess()) {
                    if ($id !== $absenceType->getId()) {
                        $this->getStatusManager()
                            ->setReDirect($this->generateUrl('staff_absence_type_edit', ['absenceType' => $absenceType->getId()]))
                            ->convertToFlash();
                    }
                    $route = $absenceType->getId() !== null ? $this->generateUrl('staff_absence_type_edit', ['absenceType' => $absenceType->getId()]) : $this->generateUrl('staff_absence_type_add');
                    $form = $this->createForm(StaffAbsenceTypeType::class, $absenceType, ['action' => $route]);
                }
            } else {
                $this->getStatusManager()->error(StatusManager::INVALID_INPUTS);
            }

            return $this->generateJsonResponse(
                [
                    'form' => $this->getContainerManager()
                        ->singlePanel($form->createView())
                        ->getFormFromContainer()
                ]
            );
        }

        $this->getContainerManager()
            ->setReturnRoute($this->generateUrl('staff_settings', ['tabName' => 'List']));

        if ($absenceType->getId() !== null) {
            $this->getContainerManager()
                ->setAddElementRoute($this->generateUrl('staff_absence_type_add'));
        }

        return $this->getPageManager()->createBreadcrumbs($absenceType->getId() === null ? 'Add Absence Type' : 'Edit Absence Type')
            ->render(
                [
                    'containers' => $this->getContainerManager()
                        ->singlePanel($form->createView())
                        ->getBuiltContainers(),
                ]
            );
    }

    /**
     * staffAbsenceTypeSort
     *
     * 18/08/2020 16:06
     * @param StaffAbsenceType $source
     * @param StaffAbsenceType $target
     * @param StaffAbsenceTypePagination $pagination
     * @param EntitySortManager $manager
     * @Route("/staff/absence/type/{source}/{target}/sort/", name="staff_absence_type_sort")
     * @IsGranted("ROLE_ROUTE")
     * @return JsonResponse
     */
    public function staffAbsenceTypeSort(StaffAbsenceType $source, StaffAbsenceType $target, StaffAbsenceTypePagination $pagination, EntitySortManager $manager)
    {
        $manager->setIndexName('sequence_number')
            ->setSortField('sequenceNumber')
            ->execute($source, $target, $pagination);

        return $this->generateJsonResponse(['content' => $manager->getPaginationContent()]);
    }
}

