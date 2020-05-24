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
 * Date: 28/04/2020
 * Time: 08:50
 */

namespace App\Modules\System\Controller;

use App\Controller\AbstractPageController;
use App\Modules\System\Manager\DemoDataManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Yaml\Yaml;

/**
 * Class DemonstrationController
 * @package App\Modules\System\Controller
 */
class DemonstrationController extends AbstractPageController
{
    /**
     * load
     * @param DemoDataManager $manager
     * @Route("/demonstration/load/{raw_page}", name="demonstration_load")
     * @IsGranted("ROLE_ROUTE")
     */
    public function load(DemoDataManager $manager, string $raw_page = 'true')
    {
        $manager->execute();

        return $this->getPageManager()
            ->render(['content' => $this->renderView('installation/demonstration_data.html.twig')]);
    }
}