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
 * Time: 10:56
 */
namespace App\Modules\Student\Controller;

use App\Controller\AbstractPageController;
use App\Manager\StatusManager;
use App\Modules\Student\Entity\StudentNoteCategory;
use App\Modules\Student\Form\NoteCategoryType;
use App\Provider\ProviderFactory;
use Doctrine\DBAL\Driver\PDOException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class StudentNoteController
 * @package App\Modules\Student\Controller
 */
class StudentNoteController extends AbstractPageController
{
    /**
     * noteCategoryEdit
     *
     * 18/08/2020 15:38
     * @param StudentNoteCategory|null $category
     * @Route("/student/note/category/add", name="student_note_category_add")
     * @Route("/student/note/{category}/category/edit/", name="student_note_category_edit")
     * @IsGranted("ROLE_ROUTE")
     * @return JsonResponse
     */
    public function noteCategoryEdit(?StudentNoteCategory $category = null)
    {
        $category = $category ?: new StudentNoteCategory();

        $route = intval($category->getId()) > 0 ? $this->generateUrl('student_note_category_edit', ['category' => $category->getId()]) : $this->generateUrl('student_note_category_add');

        $form = $this->createForm(NoteCategoryType::class, $category, ['action' => $route]);

        if ($this->getRequest()->getContent() !== '') {
            $content = json_decode($this->getRequest()->getContent(), true);
            $form->submit($content);
            if ($form->isValid()) {
                $id = $category->getId();
               ProviderFactory::create(StudentNoteCategory::class)
                   ->persistFlush($category);
                if ($id !== $category->getId() && $this->getStatusManager()->isStatusSuccess())
                    $form = $this->createForm(NoteCategoryType::class, $category, ['action' => $this->generateUrl('student_note_category_edit', ['category' => $category->getId()])]);
            } else {
                $this->getStatusManager()->error(StatusManager::INVALID_INPUTS);
            }

            return $this->generateJsonResponse(
                [
                    'form' => $this->getContainerManager()
                        ->setReturnRoute($this->generateUrl('student_settings'))
                        ->singlePanel($form->createView())
                        ->getFormFromContainer()
                ]
            );
        }

        return $this->getPageManager()->createBreadcrumbs($category->getId() !== null ? 'Edit Student Note Category' : 'Add Student Note Category',
                [
                    ['uri' => 'student_settings', 'name' => 'Student Settings'],
                ]
            )
            ->render(
                [
                    'containers' => $this->getContainerManager()
                        ->setReturnRoute($this->generateUrl('student_settings'))
                        ->singlePanel($form->createView())
                        ->getBuiltContainers()
                ]
            )
        ;
    }

    /**
     * noteCategoryDelete
     *
     * 18/08/2020 15:38
     * @param StudentNoteCategory $category
     * @Route("/student/note/{category}/category/delete/", name="student_note_category_delete")
     * @IsGranted("ROLE_ROUTE")
     * @return Response
     */
    public function noteCategoryDelete(StudentNoteCategory $category)
    {
        if ($category instanceof StudentNoteCategory) {
            try {
                $em = ProviderFactory::getEntityManager();
                $em->remove($category);
                $em->flush();
                $this->getStatusManager()->success();
            } catch (\PDOException | PDOException $e) {
                $this->getStatusManager()->error(StatusManager::DATABASE_ERROR);
            }
        } else {
            $this->getStatusManager()->warning(StatusManager::INVALID_INPUTS);
        }
        $this->getStatusManager()->convertToFlash();
        return $this->forward(SettingController::class.'::studentSettings');
    }
}