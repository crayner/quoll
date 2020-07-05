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
 * Date: 2/06/2020
 * Time: 15:40
 */
namespace App\Modules\School\Controller;

use App\Container\ContainerManager;
use App\Controller\AbstractPageController;
use App\Manager\AbstractEntity;
use App\Manager\EntitySortManager;
use App\Modules\School\Entity\YearGroup;
use App\Modules\School\Form\YearGroupType;
use App\Modules\School\Pagination\YearGroupPagination;
use App\Provider\ProviderFactory;
use App\Util\ErrorMessageHelper;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class YearGroupController
 * @package App\Modules\School\Controller
 * @author Craig Rayner <craig@craigrayner.com>
 */
class YearGroupController extends AbstractPageController
{
    /**
     * list
     * @param YearGroupPagination $pagination
     * @param array $data
     * @return JsonResponse
     * @Route("/year/group/list/",name="year_group_list")
     * @IsGranted("ROLE_ROUTE")
     * 2/06/2020 15:47
     */
    public function list(YearGroupPagination $pagination, array $data = [])
    {
        $content = ProviderFactory::getRepository(YearGroup::class)->findBy([],['sequenceNumber' => 'ASC']);
        $pagination->setContent($content)
            ->setAddElementRoute($this->generateUrl('year_group_add'))
            ->setDraggableSort()
            ->setDraggableRoute('year_group_sort')
            ;

        return $this->getPageManager()
            ->setMessages(isset($data['errors']) ? $data['errors'] : [])
            ->createBreadcrumbs('Year Groups')
            ->render(['pagination' => $pagination->toArray()]);
    }

    /**
     * edit
     * @param ContainerManager $manager
     * @param YearGroup|null $year
     * @return JsonResponse
     * @Route("/year/group/{year}/edit/", name="year_group_edit")
     * @Route("/year/group/add/", name="year_group_add")
     * @IsGranted("ROLE_ROUTE")
     * 2/06/2020 16:31
     */
    public function edit(ContainerManager $manager, ?YearGroup $year = null)
    {
        $request = $this->getRequest();
        
        if (!$year instanceof YearGroup) {
            $year = new YearGroup();
            $action = $this->generateUrl('year_group_add');
        } else {
            $action = $this->generateUrl('year_group_edit', ['year' => $year->getId()]);
        }

        $form = $this->createForm(YearGroupType::class, $year, ['action' => $action]);

        if ($request->getContent() !== '') {
            $content = json_decode($request->getContent(), true);
            $form->submit($content);
            if ($form->isValid()) {
                $id = $year->getId();
                $provider = ProviderFactory::create(YearGroup::class);
                $data = $provider->persistFlush($year);
                if ($data['status'] === 'success' && $id !== $year->getId()) {
                    $data['status'] = 'redirect';
                    $data['redirect'] = $this->generateUrl('year_group_edit', ['year' => $year->getId()]);
                } else if ($data['status'] !== 'success') {
                    $data = ErrorMessageHelper::getDatabaseErrorMessage([], true);
                }
            } else {
                $data = ErrorMessageHelper::getInvalidInputsMessage([], true);
            }

            $manager->singlePanel($form->createView());
            $data['form'] = $manager->getFormFromContainer();

            return new JsonResponse($data);
        }

        if ($year->getId() !== null) {
            $manager->setAddElementRoute($this->generateUrl('year_group_add'));
        }
        $manager->setReturnRoute($this->generateUrl('year_group_list'))
            ->singlePanel($form->createView());

        return $this->getPageManager()
            ->createBreadcrumbs($year->getId() !== null ? 'Edit Year Group' : 'Add Year Group',
                [
                    ['uri' => 'year_group_list', 'name' => 'Year Groups']
                ]
            )
            ->render(['containers' => $manager->getBuiltContainers()]);
    }

    /**
     * delete
     * @param YearGroup $year
     * @param YearGroupPagination $pagination
     * @return mixed
     * @Route("/year/group/{year}/delete/", name="year_group_delete")
     * @IsGranted("ROLE_ROUTE")
     * 2/06/2020 16:41
     */
    public function delete(YearGroup $year, YearGroupPagination $pagination)
    {
        $provider = ProviderFactory::create(YearGroup::class);

        $provider->delete($year);

        $data = $provider->getMessageManager()->pushToJsonData();

        return $this->list($pagination, $data);
    }

    /**
     * sort
     * @param YearGroup $source
     * @param YearGroup $target
     * @param YearGroupPagination $pagination
     * @return JsonResponse
     * @Route("/year/group/{source}/{target}/sort/",name="year_group_sort")
     * @IsGranted("ROLE_ROUTE")
     * 2/06/2020 16:12
     */
    public function sort(YearGroup $source, YearGroup $target, YearGroupPagination $pagination)
    {
        $sort = new EntitySortManager();
        $sort->setIndexName('sequence_number')
            ->setSortField('sequenceNumber')
            ->execute($source, $target, $pagination);
        
        return new JsonResponse($sort->getDetails(), 200);
    }
}