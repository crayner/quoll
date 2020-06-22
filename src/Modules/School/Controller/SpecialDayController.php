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
 * Time: 13:51
 */
namespace App\Modules\School\Controller;

use App\Container\ContainerManager;
use App\Controller\AbstractPageController;
use App\Modules\School\Entity\AcademicYear;
use App\Modules\School\Entity\AcademicYearSpecialDay;
use App\Modules\School\Form\SpecialDayType;
use App\Modules\School\Manager\SpecialDayManager;
use App\Modules\School\Pagination\SpecialDayPagination;
use App\Modules\School\Util\AcademicYearHelper;
use App\Provider\ProviderFactory;
use App\Util\ErrorMessageHelper;
use App\Util\TranslationHelper;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class SpecialDayController
 * @package App\Modules\School\Controller
 * @author Craig Rayner <craig@craigrayner.com>
 */
class SpecialDayController extends AbstractPageController
{
    /**
     * list
     * @param SpecialDayPagination $pagination
     * @param array $data
     * @return JsonResponse
     * @Route("/special/day/list/",name="special_day_list")
     * @IsGranted("ROLE_ROUTE")
     * 31/05/2020 14:01
     */
    public function list(SpecialDayPagination $pagination, array $data = [])
    {
        $content = ProviderFactory::getRepository(AcademicYearSpecialDay::class)->findBy([],['date' => 'ASC']);
        $pagination->setStoreFilterURL($this->generateUrl('special_day_filter_store'))
            ->setContent($content)
            ->setAddElementRoute($this->generateUrl('special_day_add'));

        return $this->getPageManager()->setMessages(isset($data['errors']) ? $data['errors'] : [])->createBreadcrumbs('Academic Year Special Days')
            ->render(
                [
                    'pagination' => $pagination->toArray(),
                    'url' => $this->generateUrl('special_day_list'),
                    'title' => TranslationHelper::translate('Special Days'),
                ]
            );
    }

    /**
     * edit
     * @param ContainerManager $manager
     * @param AcademicYearSpecialDay|null $day
     * @return JsonResponse
     * @throws \Exception
     * @Route("/special/day/{day}/edit/", name="special_day_edit")
     * @Route("/special/day/add/", name="special_day_add")
     * @Route("/special/day/{day}/duplicate/", name="special_day_duplicate")
     * @IsGranted("ROLE_ROUTE")
     * 31/05/2020 14:52
     */
    public function edit(ContainerManager $manager, ?AcademicYearSpecialDay $day = null)
    {
        $request = $this->getRequest();

        if ($request->attributes->get('_route') === 'special_day_duplicate') {
            $copy = clone $day;
            $day = new AcademicYearSpecialDay();
            $day->setName($copy->getName())
                ->setDescription($copy->getDescription())
                ->setAcademicYear(AcademicYearHelper::getNextAcademicYear($copy->getAcademicYear()))
                ->setDate(SpecialDayManager::getDuplicateDate($copy))
                ->setType($copy->getType())
                ->setSchoolClose($copy->getSchoolClose())
                ->setSchoolEnd($copy->getSchoolEnd())
                ->setSchoolStart($copy->getSchoolStart())
                ->setSchoolOpen($copy->getSchoolOpen())
            ;
            $action = $this->generateUrl('special_day_add');
        } else if (!$day instanceof AcademicYearSpecialDay) {
            $day = new AcademicYearSpecialDay();
            $action = $this->generateUrl('special_day_add');
            $whichYear = $request->getSession()->get('special_day_pagination');
            if (isset($whichYear['filter'])) {
                foreach($whichYear['filter'] as $w)
                    if (mb_strpos($w, 'Academic Year: ') === 0) {
                        $whichYear = $w;
                        break;
                    }
            } else {
                $whichYear = '';
            }
            $year = ProviderFactory::getRepository(AcademicYear::class)->findOneByName(str_replace('Academic Year: ','', $whichYear)) ?: AcademicYearHelper::getCurrentAcademicYear();
            $day->setAcademicYear($year);
        } else {
            $action = $this->generateUrl('special_day_edit', ['day' => $day->getId()]);
        }

        $form = $this->createForm(SpecialDayType::class, $day, ['action' => $action]);

        if ($request->getContent() !== '') {
            $content = json_decode($request->getContent(), true);
            $form->submit($content);
            $data = [];
            $data['status'] = 'success';
            if ($form->isValid()) {
                $id = $day->getId();
                $data = ProviderFactory::create(AcademicYearSpecialDay::class)->persistFlush($day, $data);
                if ($id !== $day->getId() && $data['status'] === 'success') {
                    $data['status'] = 'redirect';
                    $data['redirect'] = $this->generateUrl('special_day_edit', ['day' => $day->getId()]);
                    ErrorMessageHelper::convertToFlash($data, $request->getSession()->getBag('flashes'));
                } else if ($data['status'] !== 'success') {
                    $data = ErrorMessageHelper::getDatabaseErrorMessage([],true);
                }
            } else {
                $data = ErrorMessageHelper::getInvalidInputsMessage([], true);
            }

            $manager->singlePanel($form->createView());
            $data['form'] = $manager->getFormFromContainer();

            return new JsonResponse($data, 200);
        }
        $manager->setReturnRoute($this->generateUrl('special_day_list'))->setAddElementRoute($this->generateUrl('special_day_add'))->singlePanel($form->createView());

        return $this->getPageManager()->createBreadcrumbs(($day->getId() > 0 ? 'Edit Special Day' : 'Add Special Day'), [['uri' => 'special_day_list', 'name' => 'Special Days']])
            ->render(['containers' => $manager->getBuiltContainers()]);
    }

    /**
     * delete
     * @param AcademicYearSpecialDay $day
     * @param SpecialDayPagination $pagination
     * @return JsonResponse
     * @Route("/special/day/{day}/delete/", name="special_day_delete")
     * @IsGranted("ROLE_ROUTE")
     * 31/05/2020 15:34
     */
    public function delete(AcademicYearSpecialDay $day, SpecialDayPagination $pagination)
    {
        ProviderFactory::create(AcademicYearSpecialDay::class)->delete($day);

        $data = ProviderFactory::create(AcademicYearSpecialDay::class)->getMessageManager()->pushToJsonData();

        return $this->list($pagination, $data);
    }

    /**
     * storeFilter
     * @param SpecialDayPagination $pagination
     * @return JsonResponse
     * @Route("/special/day/filter/store/",name="special_day_filter_store", methods={"POST"})
     * @IsGranted("ROLE_ROUTE")
     * 31/05/2020 15:46
     */
    public function storeFilter(SpecialDayPagination $pagination)
    {
        $content = json_decode($this->getRequest()->getContent(), true);
        $pagination->writeFilter($content);
        return new JsonResponse([],200);
    }

}