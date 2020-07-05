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

use App\Container\ContainerManager;
use App\Controller\AbstractPageController;
use App\Modules\Student\Entity\StudentNoteCategory;
use App\Modules\Student\Form\NoteCategoryType;
use App\Modules\Student\Pagination\StudentNoteCategoryPagination;
use App\Provider\ProviderFactory;
use App\Util\ErrorMessageHelper;
use Doctrine\DBAL\Driver\PDOException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class StudentNoteController
 * @package App\Modules\Student\Controller
 */
class StudentNoteController extends AbstractPageController
{
    /**
     * noteCategory
     * @Route("/student/note/category/add", name="student_note_category_add")
     * @Route("/student/note/{category}/category/edit/", name="student_note_category_edit")
     * @IsGranted("ROLE_ROUTE")
     * @param ContainerManager $manager
     * @param StudentNoteCategory|null $category
     * @return JsonResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function noteCategoryEdit(ContainerManager $manager, ?StudentNoteCategory $category = null)
    {
        $category = $category ?: new StudentNoteCategory();
        $request = $this->getRequest();

        $route = intval($category->getId()) > 0 ? $this->generateUrl('student_note_category_edit', ['category' => $category->getId()]) : $this->generateUrl('student_note_category_add');

        $form = $this->createForm(NoteCategoryType::class, $category, ['action' => $route]);

        if ($request->getContent() !== '') {
            $content = json_decode($request->getContent(), true);
            $form->submit($content);
            if ($form->isValid()) {
                $id = $category->getId();
                $provider = ProviderFactory::create(StudentNoteCategory::class);
                $data = $provider->persistFlush($category, []);
                if ($id !== $category->getId() && $data['status'] === 'success')
                    $form = $this->createForm(NoteCategoryType::class, $category, ['action' => $this->generateUrl('student_note_category_edit', ['category' => $category->getId()])]);
            } else {
                $data = ErrorMessageHelper::getDatabaseErrorMessage([],true);
            }

            $manager->setReturnRoute($this->generateUrl('student_settings'))
                ->singlePanel($form->createView());
            $data['form'] = $manager->getFormFromContainer();

            return new JsonResponse($data, 200);
        }

        $manager->setReturnRoute($this->generateUrl('student_settings'))
            ->singlePanel($form->createView());

        return $this->getPageManager()->createBreadcrumbs($category->getId()> 0 ? 'Edit Student Note Category' : 'Add Student Note Category',
            [
                ['uri' => 'student_settings', 'name' => 'Student Settings'],
            ]
        )
            ->render(['containers' => $manager->getBuiltContainers()]);
    }

    /**
     * noteCategoryDelete
     * @Route("/student/note/{category}/category/delete/", name="student_note_category_delete")
     * @IsGranted("ROLE_ROUTE")
     * @param StudentNoteCategory $category
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function noteCategoryDelete(StudentNoteCategory $category)
    {
        if ($category instanceof StudentNoteCategory) {
            try {
                $em = ProviderFactory::getEntityManager();
                $em->remove($category);
                $em->flush();
                $this->addFlash('success', ErrorMessageHelper::onlySuccessMessage());
            } catch (\PDOException | PDOException $e) {
                $this->addFlash('error', ErrorMessageHelper::onlyDatabaseErrorMessage());
            }
        } else {
            $this->addFlash('warning', ErrorMessageHelper::onlyInvalidInputsMessage());
        }

        return $this->forward(SettingController::class.'::studentSettings');
    }
}