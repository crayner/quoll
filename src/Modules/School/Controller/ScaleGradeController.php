<?php
/**
 * Created by PhpStorm.
 *
 * Quoll
 * (c) 2020 Craig Rayner <craig@craigrayner.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * User: craig
 * Date: 1/06/2020
 * Time: 11:20
 */
namespace App\Modules\School\Controller;

use App\Container\ContainerManager;
use App\Controller\AbstractPageController;
use App\Manager\EntitySortManager;
use App\Modules\School\Entity\Scale;
use App\Modules\School\Entity\ScaleGrade;
use App\Modules\School\Form\ScaleGradeType;
use App\Modules\School\Pagination\ScaleGradePagination;
use App\Provider\ProviderFactory;
use App\Util\ErrorMessageHelper;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class ScaleGradeController
 * @package App\Modules\School\Controller
 * @author Craig Rayner <craig@craigrayner.com>
 */
class ScaleGradeController extends AbstractPageController
{
    /**
     * list
     * @param ScaleGradePagination $pagination
     * @param Scale $scale
     * @param array $data
     * @return JsonResponse
     * @Route("/scale/{scale}/grade/list/",name="scale_grade_list")
     * @IsGranted("ROLE_ROUTE")
     * 1/06/2020 11:23
     */
    public function list(ScaleGradePagination $pagination, Scale $scale, array $data = [])
    {
        $repository = ProviderFactory::getRepository(ScaleGrade::class);
        $content = $repository->findBy(['scale' => $scale],['sequenceNumber' => 'ASC']);
        $pagination->setContent($content)
            ->setReturnRoute($this->generateUrl('scale_edit', ['scale' => $scale->getId(), 'tabName' => 'Grades']))
            ->setAddElementRoute($this->generateUrl('scale_grade_add', ['scale' => $scale->getId()]));
        return $this->getPageManager()
            ->setMessages(isset($data['errors']) ? $data['errors'] : [])
            ->createBreadcrumbs('Scale Grade', [
            ['uri' => 'scale_list', 'name' => 'Scales'],
            ['uri' => 'scale_edit', 'name' => 'Edit Scale ({name})', 'trans_params' => ['{name}' => $scale->getName()], 'uri_params' => ['scale' => $scale->getId(), 'tabName' => 'Grades']]
        ])
            ->render(['pagination' => $pagination->toArray()]);
    }

    /**
     * edit
     * @param ContainerManager $manager
     * @param Scale $scale
     * @param ScaleGrade|null $grade
     * @return JsonResponse
     * @Route("/scale/{scale}/grade/{grade}/edit/",name="scale_grade_edit")
     * @Route("/scale/{scale}/grade/add/",name="scale_grade_add")
     * @IsGranted("ROLE_ROUTE")
     * 2/06/2020 09:07
     */
    public function edit(ContainerManager $manager, Scale $scale, ?ScaleGrade $grade = null)
    {
        $request = $this->getRequest();

        if ($request->get('_route') === 'scale_grade_add') {
            $grade = new ScaleGrade();
            $action = $this->generateUrl('scale_grade_add', ['scale' => $scale->getId()]);
        } else {
            $action = $this->generateUrl('scale_grade_edit', ['scale' => $scale->getId(), 'grade' => $grade->getId()]);
        }
        $grade->setScale($scale);

        $form = $this->createForm(ScaleGradeType::class, $grade, ['action' => $action]);

        if ($request->getContent() !== '') {
            $content = json_decode($request->getContent(), true);
            $form->submit($content);
            dump($form,$content);
            if ($form->isValid()) {
                $id = $grade->getId();
                $provider = ProviderFactory::create(ScaleGrade::class);
                $data = $provider->persistFlush($grade, []);
                if ($data['status'] === 'success') {
                    $form = $this->createForm(ScaleGradeType::class, $grade, ['action' => $action]);
                    if ($id !== $grade->getId()) {
                        ErrorMessageHelper::convertToFlash($data, $request->getSession()->getBag('flashes'));
                        $data['redirect'] = $this->generateUrl('scale_grade_edit', ['scale' => $scale->getId(), 'grade' => $grade->getId()]);
                        $data['status'] = 'redirect';
                    }
                } else if ($data['status'] !== 'success') {
                    $data = ErrorMessageHelper::getDatabaseErrorMessage([], true);
                }
            } else {
                $data = ErrorMessageHelper::getInvalidInputsMessage([], true);
            }

            $manager->singlePanel($form->createView());
            $data['form'] = $manager->getFormFromContainer();

            return new JsonResponse($data, 200);
        }

        if ($grade->getId() !== null) {
            $manager->setAddElementRoute($this->generateUrl('scale_grade_add', ['scale' => $scale->getId()]));
        }
        $manager->setReturnRoute($this->generateUrl('scale_edit', ['scale' => $scale->getId(), 'tabName' => 'Grades']))
            ->singlePanel($form->createView());

        return $this->getPageManager()
            ->createBreadcrumbs($grade->getId() !== null ? 'Edit Grade Scale' : 'Add Grade Scale',
                [
                    ['uri' => 'scale_list', 'name' => 'Scales'],
                    ['uri' => 'scale_edit', 'name' => 'Edit Scale ({name})', 'trans_params' => ['{name}' => $scale->getName()], 'uri_params' => ['scale' => $scale->getId(), 'tabName' => 'Grades']]
                ]
            )
            ->render(
                ['containers' => $manager->getBuiltContainers()]
            )
        ;
    }

    /**
     * delete
     * @param Scale $scale
     * @param ScaleGrade $grade
     * @param ScaleGradePagination $pagination
     * @return JsonResponse
     * @Route("/scale/{scale}/grade/{grade}/delete/",name="scale_grade_delete")
     * @IsGranted("ROLE_ROUTE")
     * 2/06/2020 11:14
     */
    public function delete(Scale $scale, ScaleGrade $grade, ScaleGradePagination $pagination)
    {
        $provider = ProviderFactory::create(ScaleGrade::class);
        if ($scale === $grade->getScale()) {
            if ($scale->getLowestAcceptable() === $grade)
            {
                $scale->setLowestAcceptable(null);
                $provider->getEntityManager()->persist($scale);
                $provider->getEntityManager()->flush();
            }

            $provider->delete($grade);

            $data = $provider->getMessageManager()->pushToJsonData();
        } else {
            $data = ErrorMessageHelper::getInvalidInputsMessage([],true);
        }

        if ($data['status'] === 'success') {
            $provider->delete($scale);
            $data = $provider->getMessageManager()->pushToJsonData();
        }
        return $this->list($pagination, $scale, $data);
    }

    /**
     * sort
     * @param ScaleGradePagination $pagination
     * @param ScaleGrade $source
     * @param ScaleGrade $target
     * @return JsonResponse
     * @Route("/scale/grade/{source}/{target}/sort/",name="scale_grade_sort")
     * @IsGranted("ROLE_ROUTE")
     * 2/06/2020 10:14
     */
    public function sort(ScaleGradePagination $pagination, ScaleGrade $source, ScaleGrade $target)
    {
        $manager = new EntitySortManager();
        $manager->setSortField('sequenceNumber')
            ->setFindBy(['scale' => $source->getScale()->getId()])
            ->setIndexColumns(['sequenceNumber','scale'])
            ->setIndexName('scale_sequence')
            ->execute($source, $target, $pagination);

        return new JsonResponse($manager->getDetails(), 200);
    }
}