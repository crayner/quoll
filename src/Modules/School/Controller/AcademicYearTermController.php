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
 * Date: 31/05/2020
 * Time: 12:15
 */
namespace App\Modules\School\Controller;

use App\Container\ContainerManager;
use App\Controller\AbstractPageController;
use App\Modules\School\Entity\AcademicYearTerm;
use App\Modules\School\Form\AcademicYearTermType;
use App\Modules\School\Pagination\AcademicYearTermPagination;
use App\Provider\ProviderFactory;
use App\Util\ErrorMessageHelper;
use App\Util\TranslationHelper;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class AcademicYearTermController
 * @package App\Modules\School\Controller
 * @author Craig Rayner <craig@craigrayner.com>
 */
class AcademicYearTermController extends AbstractPageController
{
    /**
     * list
     * @param AcademicYearTermPagination $pagination
     * @param array $data
     * @return JsonResponse
     * @Route("/academic/year/term/list/", name="academic_year_term_list")
     * @IsGranted("ROLE_ROUTE")
     * 31/05/2020 12:26
     */
    public function list(AcademicYearTermPagination $pagination, array $data = [])
    {
        $content = ProviderFactory::getRepository(AcademicYearTerm::class)->findByPaginationList();
        $pagination->setContent($content)
            ->setAddElementRoute($this->generateUrl('academic_year_term_add'));

        return $this->getPageManager()->setMessages(isset($data['errors']) ? $data['errors'] : [])->createBreadcrumbs('Academic Year Terms', [])
            ->render(
                [
                    'pagination' => $pagination->toArray(),
                    'url' => $this->generateUrl('academic_year_term_list'),
                    'title' => TranslationHelper::translate('Academic Year Terms')
                ]
            );
    }

    /**
     * edit
     * @param ContainerManager $manager
     * @param AcademicYearTerm|null $term
     * @Route("/academic/year/term/{term}/edit/", name="academic_year_term_edit")
     * @Route("/academic/year/term/add/", name="academic_year_term_add")
     * @IsGranted("ROLE_ROUTE")
     * @return JsonResponse
     * 31/05/2020 12:34
     */
    public function edit(ContainerManager $manager, ?AcademicYearTerm $term = null)
    {
        $request = $this->getRequest();

        if (!$term instanceof AcademicYearTerm) {
            $term = new AcademicYearTerm();
            $action = $this->generateUrl('academic_year_term_add');
        } else {
            $action = $this->generateUrl('academic_year_term_edit', ['term' => $term->getId()]);
        }

        $form = $this->createForm(AcademicYearTermType::class, $term, ['action' => $action]);

        if ($request->getContent() !== '') {
            $content = json_decode($request->getContent(), true);
            $form->submit($content);
            if ($form->isValid()) {
                $id = $term->getId();
                $provider = ProviderFactory::create(AcademicYearTerm::class);
                $data = $provider->persistFlush($term, []);
                if ($id !== $term->getId() && $data['status'] === 'success') {
                    $data['status'] = 'redirect';
                    $data['redirect'] = $this->generateUrl('academic_year_term_edit', ['term' => $term->getId()]);
                    $this->addFlash('success', ErrorMessageHelper::onlySuccessMessage());
                } else if ($data['status'] !== 'success') {
                    $data = ErrorMessageHelper::getDatabaseErrorMessage([], true);
                }
            } else {
                $data = ErrorMessageHelper::getInvalidInputsMessage([], true);
            }

            $manager->singlePanel($form->createView());
            $data['form'] = $manager->getFormFromContainer('formContent', 'single');

            return new JsonResponse($data, 200);
        }
        $manager->setAddElementRoute($this->generateUrl('academic_year_term_add'))
            ->setReturnRoute($this->generateUrl('academic_year_term_list'))
            ->singlePanel($form->createView());
        return $this->getPageManager()->createBreadcrumbs($term->getId() > 0 ? 'Edit Academic Year Term' : 'Add Academic Year Term', [['uri' => 'academic_year_term_list', 'name' => 'Academic Year Terms']])
            ->render(['containers' => $manager->getBuiltContainers()]);
    }

    /**
     * delete
     * @param AcademicYearTerm $term
     * @param AcademicYearTermPagination $pagination
     * @return JsonResponse
     * @Route("/academic/year/term/{term}/delete/", name="academic_year_term_delete")
     * @IsGranted("ROLE_ROUTE")
     * 31/05/2020 13:32
     */
    public function delete(AcademicYearTerm $term, AcademicYearTermPagination $pagination)
    {
        ProviderFactory::create(AcademicYearTerm::class)->delete($term);

        $data = ProviderFactory::create(AcademicYearTerm::class)->getMessageManager()->pushToJsonData();

        return $this->list($pagination, $data);
    }
}