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

use App\Controller\AbstractPageController;
use App\Manager\EntitySortManager;
use App\Manager\MessageStatusManager;
use App\Modules\School\Entity\YearGroup;
use App\Modules\School\Form\YearGroupType;
use App\Modules\School\Pagination\YearGroupPagination;
use App\Provider\ProviderFactory;
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
     *
     * 17/08/2020 11:36
     * @param YearGroupPagination $pagination
     * @Route("/year/group/list/",name="year_group_list")
     * @IsGranted("ROLE_ROUTE")
     * @return JsonResponse
     */
    public function list(YearGroupPagination $pagination)
    {
        $content = ProviderFactory::getRepository(YearGroup::class)->findBy([],['sortOrder' => 'ASC']);
        $pagination->setContent($content)
            ->setAddElementRoute($this->generateUrl('year_group_add'))
            ->setDraggableSort()
            ->setDraggableRoute('year_group_sort')
        ;

        return $this->getPageManager()
            ->setMessages($this->getMessageStatusManager()->getMessageArray())
            ->createBreadcrumbs('Year Groups')
            ->render(['pagination' => $pagination->toArray()]);
    }

    /**
     * edit
     *
     * 17/08/2020 11:17
     * @param YearGroup|null $year
     * @Route("/year/group/{year}/edit/", name="year_group_edit")
     * @Route("/year/group/add/", name="year_group_add")
     * @IsGranted("ROLE_ROUTE")
     * @return JsonResponse
     */
    public function edit(?YearGroup $year = null)
    {
        $manager = $this->getContainerManager();
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
                ProviderFactory::create(YearGroup::class)->persistFlush($year);
                if ($this->getMessageStatusManager()->isStatusSuccess() && $id !== $year->getId()) {
                    $this->getMessageStatusManager()->setRedirect($this->generateUrl('year_group_edit', ['year' => $year->getId()]));
                }
            } else {
                $this->getMessageStatusManager()->error(MessageStatusManager::INVALID_INPUTS);
            }

            $manager->singlePanel($form->createView());
            return $this->getMessageStatusManager()->toJsonResponse(['form' => $manager->getFormFromContainer()]);
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
     *
     * 17/08/2020 11:37
     * @param YearGroup $year
     * @param YearGroupPagination $pagination
     * @Route("/year/group/{year}/delete/", name="year_group_delete")
     * @IsGranted("ROLE_ROUTE")
     * @return JsonResponse
     */
    public function delete(YearGroup $year, YearGroupPagination $pagination)
    {
        $provider = ProviderFactory::create(YearGroup::class);

        $provider->delete($year);

        return $this->list($pagination);
    }

    /**
     * sort
     *
     * 17/08/2020 11:43
     * @param YearGroup $source
     * @param YearGroup $target
     * @param YearGroupPagination $pagination
     * @param EntitySortManager $sort
     * @Route("/year/group/{source}/{target}/sort/",name="year_group_sort")
     * @IsGranted("ROLE_ROUTE")
     * @return JsonResponse
     */
    public function sort(YearGroup $source, YearGroup $target, YearGroupPagination $pagination, EntitySortManager $sort)
    {
        $sort->setIndexName('sort_order')
            ->setSortField('sortOrder')
            ->execute($source, $target, $pagination);

        return $sort->getMessages()->toJsonResponse(['content' => $sort->getPaginationContent()]);
    }
}