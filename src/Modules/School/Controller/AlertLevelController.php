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
 * Date: 12/06/2020
 * Time: 12:49
 */
namespace App\Modules\School\Controller;

use App\Container\Container;
use App\Container\ContainerManager;
use App\Container\Panel;
use App\Container\Section;
use App\Controller\AbstractPageController;
use App\Manager\StatusManager;
use App\Modules\School\Entity\AlertLevel;
use App\Modules\School\Form\AlertLevelType;
use App\Provider\ProviderFactory;
use App\Util\ErrorMessageHelper;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class AlertLevelController
 * @package App\Modules\School\Controller
 * @author Craig Rayner <craig@craigrayner.com>
 */
class AlertLevelController extends AbstractPageController
{
    /**
     * manage
     * @param ContainerManager $manager
     * @param string|null $tabName
     * @return mixed
     * @Route("/alert/level/list/",name="alert_level_list")
     * @IsGranted("ROLE_ROUTE")
     * 12/06/2020 12:53
     */
    public function manage(ContainerManager $manager, ?string $tabName = null)
    {
        $container = new Container();
        $container->setSelectedPanel($tabName);

        $levels = ProviderFactory::getRepository(AlertLevel::class)->findBy([],['priority' => 'DESC']);
        foreach($levels as $q=>$level) {
            $form = $this->createForm(AlertLevelType::class, $level, ['action' => $this->generateUrl('alert_level_change', ['level' => $level->getId()])]);
            $panel = new Panel($level->getName(), 'School', new Section('form', $level->getName()));
            $container
                ->addForm($level->getName(), $form->createView())
                ->addPanel($panel);
        }

        $manager->addContainer($container)->buildContainers();

        return $this->getPageManager()
            ->createBreadcrumbs('Alert Levels')
            ->render(['containers' => $manager->getBuiltContainers()]);
    }

    /**
     * manageChange
     * @param AlertLevel $level
     * @param ContainerManager $manager
     * @return JsonResponse
     * @Route("/alert/level/{level}/change/",name="alert_level_change")
     * @IsGranted("ROLE_ROUTE")
     * 12/06/2020 12:54
     */
    public function manageChange(AlertLevel $level, ContainerManager $manager)
    {
        $form = $this->createForm(AlertLevelType::class,$level, ['action' => $this->generateUrl('alert_level_change', ['level' => $level->getId()])]);
        $name = $level->getName();

        $content = json_decode($this->getRequest()->getContent(), true);

        $form->submit($content);
        if ($form->isValid()) {
            ProviderFactory::create(AlertLevel::class)->persistFlush($level);
        } else {
            $this->getStatusManager()->error(StatusManager::INVALID_INPUTS);
        }
        $container = new Container($name);
        $panel = new Panel($name, 'School');
        $container
            ->addForm($name, $form->createView())
            ->addPanel($panel);
        $manager->addContainer($container);
        return $this->getStatusManager()->toJsonResponse(['form' => $manager->getFormFromContainer('formContent',$name)]);
    }
}
