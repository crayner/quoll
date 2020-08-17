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
 * Date: 1/06/2020
 * Time: 11:20
 */
namespace App\Modules\Assess\Controller;

use App\Container\ContainerManager;
use App\Controller\AbstractPageController;
use App\Manager\EntitySortManager;
use App\Manager\StatusManager;
use App\Modules\Assess\Entity\Scale;
use App\Modules\Assess\Entity\ScaleGrade;
use App\Modules\School\Form\ScaleGradeType;
use App\Modules\Assess\Pagination\ScaleGradePagination;
use App\Provider\ProviderFactory;
use App\Util\ErrorMessageHelper;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class ScaleGradeController
 * @package App\Modules\Assess\Controller
 * @author Craig Rayner <craig@craigrayner.com>
 */
class ScaleGradeController extends AbstractPageController
{
    /**
     * list
     *
     * 17/08/2020 15:48
     * @param ScaleGradePagination $pagination
     * @param Scale $scale
     * @Route("/scale/{scale}/grade/list/",name="scale_grade_list")
     * @IsGranted("ROLE_ROUTE")
     * @return JsonResponse
     */
    public function list(ScaleGradePagination $pagination, Scale $scale)
    {
        $repository = ProviderFactory::getRepository(ScaleGrade::class);
        $content = $repository->findBy(['scale' => $scale],['sequenceNumber' => 'ASC']);
        $pagination->setContent($content)
            ->setReturnRoute($this->generateUrl('scale_edit', ['scale' => $scale->getId(), 'tabName' => 'Grades']))
            ->setAddElementRoute($this->generateUrl('scale_grade_add', ['scale' => $scale->getId()]));
        return $this->getPageManager()
            ->setMessages($this->getStatusManager()->getMessageArray())
            ->createBreadcrumbs('Scale Grade', [
                ['uri' => 'scale_list', 'name' => 'Scales'],
                ['uri' => 'scale_edit', 'name' => 'Edit Scale ({name})', 'trans_params' => ['{name}' => $scale->getName()], 'uri_params' => ['scale' => $scale->getId(), 'tabName' => 'Grades']]
            ])
            ->render(['pagination' => $pagination->toArray()]);
    }

    /**
     * edit
     *
     * 17/08/2020 15:51
     * @param Scale $scale
     * @param ScaleGrade|null $grade
     * @Route("/scale/{scale}/grade/{grade}/edit/",name="scale_grade_edit")
     * @Route("/scale/{scale}/grade/add/",name="scale_grade_add")
     * @IsGranted("ROLE_ROUTE")
     * @return JsonResponse
     */
    public function edit(Scale $scale, ?ScaleGrade $grade = null)
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
        $manager = $this->getContainerManager();

        if ($request->getContent() !== '') {
            $content = json_decode($request->getContent(), true);
            $form->submit($content);
            if ($form->isValid()) {
                $id = $grade->getId();
                $provider = ProviderFactory::create(ScaleGrade::class);
                $provider->persistFlush($grade);
                if ($this->getStatusManager()->isStatusSuccess()) {
                    $form = $this->createForm(ScaleGradeType::class, $grade, ['action' => $action]);
                    if ($id !== $grade->getId()) {
                        $this->getStatusManager()
                            ->setReDirect($this->generateUrl('scale_grade_edit', ['scale' => $scale->getId(), 'grade' => $grade->getId()]))
                            ->convertToFlash();
                    }
                }
            } else {
                $this->getStatusManager()->error(StatusManager::INVALID_INPUTS);
            }

            $manager->singlePanel($form->createView());
            return $this->generateJsonResponse(['form' => $manager->getFormFromContainer()]);
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
     *
     * 17/08/2020 15:52
     * @param Scale $scale
     * @param ScaleGrade $grade
     * @param ScaleGradePagination $pagination
     * @param EntitySortManager $manager
     * @return JsonResponse
     * @Route("/scale/{scale}/grade/{grade}/delete/",name="scale_grade_delete")
     * @IsGranted("ROLE_ROUTE")
     */
    public function delete(Scale $scale, ScaleGrade $grade, ScaleGradePagination $pagination, EntitySortManager $manager)
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

        } else {
            $this->getStatusManager()->error(StatusManager::INVALID_INPUTS);
        }

        if ($this->getStatusManager()->isStatusSuccess()) {
            $provider->delete($scale);
        }

        $manager->setSortField('sequenceNumber')
            ->setFindBy(['scale' => $scale->getId()])
            ->setSource($grade)
            ->setIndexColumns(['sequenceNumber','scale'])
            ->setIndexName('scale_sequence')
            ->refreshSequences();

        return $this->list($pagination, $scale);
    }

    /**
     * sort
     *
     * 17/08/2020 15:54
     * @param ScaleGradePagination $pagination
     * @param ScaleGrade $source
     * @param ScaleGrade $target
     * @param EntitySortManager $manager
     * @Route("/scale/grade/{source}/{target}/sort/",name="scale_grade_sort")
     * @IsGranted("ROLE_ROUTE")
     * @return JsonResponse
     */
    public function sort(ScaleGradePagination $pagination, ScaleGrade $source, ScaleGrade $target, EntitySortManager $manager)
    {
        $manager->setSortField('sequenceNumber')
            ->setFindBy(['scale' => $source->getScale()->getId()])
            ->setIndexColumns(['sequenceNumber','scale'])
            ->setIndexName('scale_sequence')
            ->execute($source, $target, $pagination);

        return $this->generateJsonResponse(['content' => $manager->getPaginationContent()]);
    }
}