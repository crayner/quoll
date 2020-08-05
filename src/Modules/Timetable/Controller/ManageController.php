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
 * Date: 3/08/2020
 * Time: 13:43
 */
namespace App\Modules\Timetable\Controller;

use App\Container\Container;
use App\Container\ContainerManager;
use App\Container\Panel;
use App\Container\Section;
use App\Controller\AbstractPageController;
use App\Modules\School\Util\AcademicYearHelper;
use App\Modules\Timetable\Entity\Timetable;
use App\Modules\Timetable\Entity\TimetableDay;
use App\Modules\Timetable\Form\TimetableType;
use App\Modules\Timetable\Pagination\TimetableDayPagination;
use App\Modules\Timetable\Pagination\TimetablePagination;
use App\Provider\ProviderFactory;
use App\Util\ErrorMessageHelper;
use App\Util\TranslationHelper;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class ListController
 * @package App\Modules\Timetable\Controller
 * @author Craig Rayner <craig@craigrayner.com>
 */
class ManageController extends AbstractPageController
{
    /**
     * list
     * 3/08/2020 13:44
     * @Route("/timetable/list/",name="timetable_list")
     * @IsGranted("ROLE_ROUTE")
     * @param TimetablePagination $pagination
     */
    public function list(TimetablePagination $pagination, array $messages = [])
    {
        $currentYear = AcademicYearHelper::getCurrentAcademicYear();
        $pagination->setContent(ProviderFactory::getRepository(Timetable::class)->findBy(['academicYear' => $currentYear],['name' => 'ASC']))
            ->setAddElementRoute($this->generateUrl('timetable_add'));

        return $this->getPageManager()
            ->createBreadcrumbs(['Timetables in Academic Year {name}', ['{name}' => $currentYear->getName()], 'Timetable'])
            ->setMessages($messages)
            ->render([
                'pagination' => $pagination->toArray(),
                'url' => $this->generateUrl('timetable_list')
            ]);
    }

    /**
     * edit
     * @param ContainerManager $manager
     * @param Timetable|null $timetable
     * @Route("/timetable/{timetable}/edit/{tabName}",name="timetable_edit")
     * @Route("/timetable/add/",name="timetable_add")
     * @IsGranted("ROLE_ROUTE")
     * 3/08/2020 14:37
     */
    public function edit(ContainerManager $manager, TimetableDayPagination $pagination, ?Timetable $timetable = null, string $tabName = 'Details')
    {
        if (null === $timetable) {
            $action = $this->generateUrl('timetable_add');
            $timetable = new Timetable();
            $timetable->setAcademicYear(AcademicYearHelper::getCurrentAcademicYear(true));
        } else {
            $action = $this->generateUrl('timetable_edit', ['timetable' => $timetable->getId()]);
        }

        $form = $this->createForm(TimetableType::class, $timetable, ['action' => $action]);

        if ($this->getRequest()->getContent() !== '') {
            $content = json_decode($this->getRequest()->getContent(), true);
            $form->submit($content);
            if ($form->isValid()) {
                $id = $timetable->getId();
                $data = ProviderFactory::create(Timetable::class)->persistFlush($timetable,[]);
                if ($timetable->getId() !== $id) {
                    $data['status'] = 'redirect';
                    $data['redirect'] = $this->generateUrl('timetable_edit', ['timetable' => $timetable->getId()]);
                }
            } else {
                $data = ErrorMessageHelper::getDatabaseErrorMessage([], true);
            }
            $manager->singlePanel($form->createView());
            $data['form'] = $manager->getFormFromContainer();
            return new JsonResponse($data);
        }

        if ($timetable->getId() === null) {
            $manager->singlePanel($form->createView())
                ->setReturnRoute($this->generateUrl('timetable_list'));
        } else {
            $container = new Container($tabName);
            $panel = new Panel('Details', 'Timetable', new Section('form','Details'));
            $container->addForm('Details', $form->createView())
                ->addPanel($panel);

            $content = ProviderFactory::getRepository(TimetableDay::class)->findBy(['timetable' => $timetable],['name' => 'ASC']);
            $pagination->setContent($content)
                ->setAddElementRoute($this->generateUrl('timetable_day_add', ['timetable' => $timetable->getId()]), TranslationHelper::translate('Add Timetable Day'))
            ;
            $panel = new Panel('Timetable Days', 'Timetable', new Section('pagination', $pagination));
            $container->addPanel($panel);

            $manager->addContainer($container)
                ->setReturnRoute($this->generateUrl('timetable_list'))
                ->setAddElementRoute($this->generateUrl('timetable_add'), TranslationHelper::translate('Add Timetable'));

        }
        return $this->getPageManager()
            ->createBreadcrumbs($timetable->getId() === null ? 'Add Timetable' : ['Edit Timetable {name}', ['{name}' => $timetable->getName()], 'Timetable'])
            ->render(['containers' => $manager->getBuiltContainers()])
        ;
    }

    /**
     * delete
     * @param TimetablePagination $pagination
     * @param Timetable $timetable
     * @Route("/timetable/{timetable}/delete/",name="timetable_delete")
     * @IsGranted("ROLE_ROUTE")
     * @return JsonResponse
     * 3/08/2020 16:23
     */
    public function delete(TimetablePagination $pagination, Timetable $timetable)
    {
        $provider = ProviderFactory::create(Timetable::class);
        $provider->delete($timetable);

        $data = $provider->getMessageManager()->pushToJsonData();

        return $this->list($pagination, $data['errors'] ?? []);
    }
}
