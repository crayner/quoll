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
 * Time: 10:52
 */
namespace App\Modules\Assess\Controller;

use App\Container\Container;
use App\Container\Panel;
use App\Container\Section;
use App\Controller\AbstractPageController;
use App\Manager\StatusManager;
use App\Modules\Assess\Entity\Scale;
use App\Modules\Assess\Entity\ScaleGrade;
use App\Modules\Assess\Pagination\ScaleGradePagination;
use App\Modules\Assess\Pagination\ScalePagination;
use App\Modules\School\Form\ScaleType;
use App\Provider\ProviderFactory;
use App\Util\TranslationHelper;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class ScaleController
 * @package App\Modules\Assess\Controller
 * @author Craig Rayner <craig@craigrayner.com>
 */
class ScaleController extends AbstractPageController
{
    /**
     * list
     *
     * 17/08/2020 15:45
     * @param ScalePagination $pagination
     * @Route("/scale/list/", name="scale_list")
     * @Route("/scale/list/", name="scale_list_school")
     * @IsGranted("ROLE_ROUTE")
     * @return JsonResponse
     */
    public function list(ScalePagination $pagination)
    {
        $content = ProviderFactory::getRepository(Scale::class)->findBy([], ['name' => 'ASC']);
        $pagination->setContent($content)
            ->setAddElementRoute($this->generateUrl('scale_add'));

        return $this->getPageManager()
            ->setMessages(isset($data['errors']) ? $data['errors'] : [])
            ->createBreadcrumbs('Scales', [])
            ->setMessages($this->getStatusManager()->getMessageArray())
            ->render(
                [
                    'pagination' => $pagination->toArray(),
                    'url' => $this->generateUrl('scale_list'),
                    'title' => TranslationHelper::translate('Scales', [], 'School'),
                ]
            );
    }

    /**
     * edit
     *
     * 17/08/2020 15:41
     * @param ScaleGradePagination $pagination
     * @param Scale|null $scale
     * @param string $tabName
     * @Route("/scale/{scale}/edit/{tabName}", name="scale_edit")
     * @Route("/scale/add/{tabName}", name="scale_add")
     * @IsGranted("ROLE_ROUTE")
     * @return JsonResponse
     */
    public function edit(ScaleGradePagination $pagination, ?Scale $scale = null, string $tabName = 'Details')
    {
        $request = $this->getRequest();
        if (!$scale instanceof Scale) {
            $scale = new Scale();
            $action = $this->generateUrl('scale_add', ['tabName' => $tabName]);
        } else {
            $action = $this->generateUrl('scale_edit', ['scale' => $scale->getId(), 'tabName' => $tabName]);
        }

        $form = $this->createForm(ScaleType::class, $scale, ['action' => $action]);
        $manager = $this->getContainerManager();

        if ($request->getContent() !== '') {
            $content = json_decode($request->getContent(), true);
            $id = $scale->getId();
            $form->submit($content);
            if ($form->isValid()) {
                ProviderFactory::create(Scale::class)
                    ->persistFlush($scale);
                if ($this->getStatusManager()->isStatusSuccess()) {
                    $form = $this->createForm(ScaleType::class, $scale, ['action' => $this->generateUrl('scale_edit', ['scale' => $scale->getId(), 'tabName' => $tabName])]);
                    if ($id !== $scale->getId()) {
                        $this->getStatusManager()
                            ->setReDirect($this->generateUrl('scale_edit', ['scale' => $scale->getId(), 'tabName' => $tabName]))
                            ->convertToFlash();
                    }
                }
            } else {
                $this->getStatusManager()->error(StatusManager::INVALID_INPUTS);
            }

            $manager->singlePanel($form->createView());
            return $this->generateJsonResponse(['form' => $manager->getFormFromContainer()]);
        }

        $container = new Container();
        $container->setSelectedPanel($tabName);

        $panel = new Panel('Details', 'School', new Section('form', 'Details'));
        $container->addForm('Details', $form->createView())->addPanel($panel);
        if ($scale->getId() !== null) {
            $content = ProviderFactory::getRepository(ScaleGrade::class)->findBy(['scale' => $scale], ['sequenceNumber' => 'ASC']);
            $pagination->setContent($content)->setAddElementRoute($this->generateUrl('scale_grade_add', ['scale' => $scale->getId()]));

            $panel = new Panel('Grades', 'School', new Section('pagination', $pagination));
            $container->addPanel($panel);
        }

        if ($scale->getId() !== null) {
            $manager->setAddElementRoute($this->generateUrl('scale_add'));
        }

        $manager->setReturnRoute($this->generateUrl('scale_list'))
            ->addContainer($container);

        return $this->getPageManager()
            ->createBreadcrumbs([$scale->getId() !== null ? 'Edit Scale ({name})' : 'Add Scale', ['{name}' => $scale->getName()]],
            [
                ['uri' => 'scale_list', 'name' => 'Scales']
            ]
        )
            ->render(['containers' => $manager->getBuiltContainers()]);
    }

    /**
     * delete
     *
     * 17/08/2020 15:46
     * @param Scale $scale
     * @param ScalePagination $pagination
     * @Route("/scale/{scale}/delete/", name="scale_delete")
     * @IsGranted("ROLE_ROUTE")
     * @return JsonResponse
     */
    public function delete(Scale $scale, ScalePagination $pagination)
    {
        ProviderFactory::create(Scale::class)
            ->delete($scale);

        return $this->list($pagination);
    }
}