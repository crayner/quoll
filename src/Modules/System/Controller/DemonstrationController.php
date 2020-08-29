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
 * Date: 28/04/2020
 * Time: 08:50
 */

namespace App\Modules\System\Controller;

use App\Controller\AbstractPageController;
use App\Modules\System\Manager\DemoDataManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class DemonstrationController
 * @package App\Modules\System\Controller
 */
class DemonstrationController extends AbstractPageController
{
    /**
     * load
     * @param DemoDataManager $manager
     * @param string $table
     * @return JsonResponse
     * @Route("/demonstration/load/{table}", name="demonstration_load")
     * @IsGranted("ROLE_ROUTE")
     */
    public function load(DemoDataManager $manager, string $table = '')
    {
        $rotating = false;
        $next = '';
        $session = $this->getRequest()->getSession();
        if ($table === '') {
            if ($session->has('demo_table')) $table = $session->get('demo_table');
            $grab = false;
            if ($table === '') $grab = true;
            foreach ($manager->getEntities() as $q=>$w) {
                if ($grab) {
                    $table = $q;
                    break;
                }
                if ($table === $q) $grab = true;
            }
            $rotating = true;
            $next = '';
            foreach ($manager->getEntities() as $q=>$w) {
                if ($next === $table) {
                    $next = $q;
                    break;
                }
                if ($table === $q) $next = $table;
            }
        }
        $manager->execute($table);

        if ($next === $table) {
            $rotating = false;
            $next = '';
            $session->remove('demo_table');
        }

        if ($rotating) $session->set('demo_table', $table);

        return $this->getPageManager()
            ->render(
                [
                    'content' => $this->renderView('installation/demonstration_data.html.twig', ['rotating' => $rotating, 'table' => basename($manager->getEntities()[$table]), 'next' => key_exists($next, $manager->getEntities()) ? basename($manager->getEntities()[$next]) : ''])
                ]
            );
    }
}
