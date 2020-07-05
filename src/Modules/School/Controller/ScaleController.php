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
namespace App\Modules\School\Controller;

use App\Container\Container;
use App\Container\ContainerManager;
use App\Container\Panel;
use App\Controller\AbstractPageController;
use App\Modules\School\Entity\Scale;
use App\Modules\School\Entity\ScaleGrade;
use App\Modules\School\Form\ScaleType;
use App\Modules\School\Pagination\ScaleGradePagination;
use App\Modules\School\Pagination\ScalePagination;
use App\Provider\ProviderFactory;
use App\Util\ErrorMessageHelper;
use App\Util\TranslationHelper;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class ScaleController
 * @package App\Modules\School\Controller
 * @author Craig Rayner <craig@craigrayner.com>
 */
class ScaleController extends AbstractPageController
{
    /**
     * list
     * @param ScalePagination $pagination
     * @param array $data
     * @return JsonResponse
     * 1/06/2020 10:56
     * @Route("/scale/list/", name="scale_list")
     * @IsGranted("ROLE_ROUTE")
     */
    public function list(ScalePagination $pagination, array $data = [])
    {
        $content = ProviderFactory::getRepository(Scale::class)->findBy([], ['name' => 'ASC']);
        $pagination->setContent($content)
            ->setAddElementRoute($this->generateUrl('scale_add'));

        return $this->getPageManager()
            ->setMessages(isset($data['errors']) ? $data['errors'] : [])
            ->createBreadcrumbs('Scales', [])
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
     * @param ContainerManager $manager
     * @param ScaleGradePagination $pagination
     * @param Scale|null $scale
     * @param string $tabName
     * @return JsonResponse
     * 1/06/2020 11:07
     * @Route("/scale/{scale}/edit/{tabName}", name="scale_edit")
     * @Route("/scale/add/{tabName}", name="scale_add")
     * @IsGranted("ROLE_ROUTE")
     */
    public function edit(ContainerManager $manager,ScaleGradePagination $pagination, ?Scale $scale = null, string $tabName = 'Details')
    {
        $request = $this->getRequest();
        if (!$scale instanceof Scale) {
            $scale = new Scale();
            $action = $this->generateUrl('scale_add', ['tabName' => $tabName]);
        } else {
            $action = $this->generateUrl('scale_edit', ['scale' => $scale->getId(), 'tabName' => $tabName]);
        }

        $form = $this->createForm(ScaleType::class, $scale, ['action' => $action]);

        if ($request->getContent() !== '') {
            $content = json_decode($request->getContent(), true);
            $id = $scale->getId();
            $form->submit($content);
            $data = [];
            $data['status'] = 'success';
            if ($form->isValid()) {
                $provider = ProviderFactory::create(Scale::class);
                $data = $provider->persistFlush($scale, $data);
                if ($data['status'] === 'success') {
                    $form = $this->createForm(ScaleType::class, $scale, ['action' => $this->generateUrl('scale_edit', ['scale' => $scale->getId(), 'tabName' => $tabName])]);
                    if ($id !== $scale->getId()) {
                        $data['redirect'] = $this->generateUrl('scale_edit', ['scale' => $scale->getId(), 'tabName' => $tabName]);
                        $data['status'] = 'redirect';
                        ErrorMessageHelper::convertToFlash($data, $request->getSession()->getBag('flashes'));
                    } else if ($data['status'] !== 'success') {
                        $data = ErrorMessageHelper::getDatabaseErrorMessage([],true);
                    }
                }
            } else {
                $data = ErrorMessageHelper::getInvalidInputsMessage($data, true);
            }

            $manager->singlePanel($form->createView());
            $data['form'] = $manager->getFormFromContainer();
            return new JsonResponse($data);
        }

        $container = new Container();
        $container->setSelectedPanel($tabName);

        $panel = new Panel('Details', 'School');
        $container->addForm('Details', $form->createView())->addPanel($panel);
        if ($scale->getId() !== null) {
            $panel = new Panel('Grades', 'School');
            $content = ProviderFactory::getRepository(ScaleGrade::class)->findBy(['scale' => $scale], ['sequenceNumber' => 'ASC']);
            $pagination->setContent($content)->setAddElementRoute($this->generateUrl('scale_grade_add', ['scale' => $scale->getId()]));

            $panel->setPagination($pagination);
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
     * @param Scale $scale
     * @param ScalePagination $pagination
     * @return JsonResponse
     * @Route("/scale/{scale}/delete/", name="scale_delete")
     * @IsGranted("ROLE_ROUTE")
     * 2/06/2020 11:23
     */
    public function delete(Scale $scale, ScalePagination $pagination)
    {
        $provider = ProviderFactory::create(Scale::class);

        $provider->delete($scale);

        $data = $provider->getMessageManager()->pushToJsonData();

        return $this->list($pagination, $data);
    }
}