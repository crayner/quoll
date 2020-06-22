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
 * Time: 15:53
 */
namespace App\Modules\School\Controller;

use App\Container\Container;
use App\Container\ContainerManager;
use App\Container\Panel;
use App\Controller\AbstractPageController;
use App\Modules\School\Entity\DaysOfWeek;
use App\Modules\School\Form\DayOfTheWeekType;
use App\Provider\ProviderFactory;
use App\Util\ErrorMessageHelper;
use App\Util\TranslationHelper;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class DaysOfTheWeekController
 * @package App\Modules\School\Controller
 * @author Craig Rayner <craig@craigrayner.com>
 */
class DaysOfTheWeekController extends AbstractPageController
{
    /**
     * manage
     * @param ContainerManager $manager
     * @param string $tabName
     * @return JsonResponse
     * @Route("/days/of/the/week/{tabName}", name="days_of_the_week")
     * @IsGranted("ROLE_ROUTE")
     * 31/05/2020 15:54
     */
    public function manage(ContainerManager $manager, string $tabName = 'Monday')
    {
        $request = $this->getRequest();

        $container = new Container();
        $container->setSelectedPanel($tabName);
        TranslationHelper::setDomain('School');

        foreach (ProviderFactory::getRepository(DaysOfWeek::class)->findBy([], ['sequenceNumber' => 'ASC']) as $day) {
            $form = $this->createForm(DayOfTheWeekType::class, $day, ['action' => $this->generateUrl('days_of_the_week', ['tabName' => $day->getName()])]);
            $panel = new Panel($day->getName(), 'School');
            $container->addForm($day->getName(), $form->createView())->addPanel($panel);
        }

        if ($request->getContent() !== '') {
            $content = json_decode($request->getContent(), true);
            $day = !empty($content['id']) ? ProviderFactory::getRepository(DaysOfWeek::class)->find($content['id']) : new DaysOfWeek();
            $form = $this->createForm(DayOfTheWeekType::class, $day, ['action' => $this->generateUrl('days_of_the_week', ['tabName' => $tabName])]);
            $form->submit($content);
            $data = [];
            $data['status'] = 'success';
            if ($form->isValid()) {
                $id = $day->getId();
                $data = ProviderFactory::create(DaysOfWeek::class)->persistFlush($day, $data);
                if ($id !== $day->getId() && $data['status'] === 'success') {
                    $data['status'] = 'redirect';
                    $data['redirect'] = $this->generateUrl('days_of_the_week', ['tabName' => $tabName]);
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
        $manager->addContainer($container);
        return $this->getPageManager()->createBreadcrumbs('Days of the Week')
            ->render(['containers' => $manager->getBuiltContainers()]);
    }

}