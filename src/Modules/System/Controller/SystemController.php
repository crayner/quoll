<?php
/**
 * Created by PhpStorm.
 *
 * quoll
 * (c) 2020 Craig Rayner <craig@craigrayner.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * User: craig
 * Date: 24/04/2020
 * Time: 12:55
 */

namespace App\Modules\System\Controller;

use App\Controller\AbstractPageController;
use App\Manager\VersionManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class SystemController
 * @package App\Modules\System\Controller
 */
class SystemController extends AbstractPageController
{
    /**
     * check
     * @param VersionManager $manager
     * @return \Symfony\Component\HttpFoundation\Response
     * @Route("/system/check/", name="system_check")
     * @IsGranted("ROLE_ROUTE")
     */
    public function check(VersionManager $manager)
    {
        return $this->getPageManager()->createBreadcrumbs('System Check')
            ->render(['content' => $this->renderView('system/check.html.twig', [
                'manager' => $manager->setEm($this->getDoctrine()->getManager()),
            ] )]);
    }

}