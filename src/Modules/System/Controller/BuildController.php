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
 * Date: 1/07/2020
 * Time: 10:03
 */
namespace App\Modules\System\Controller;

use App\Modules\Enrolment\Entity\StudentRollGroup;
use App\Modules\Security\Controller\ActionPermissionController;
use App\Provider\ProviderFactory;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Yaml\Yaml;

/**
 * Class BuildController
 * @package App\Modules\System\Controller
 * @author Craig Rayner <craig@craigrayner.com>
 */
class BuildController extends AbstractController
{
    /**
     * build
     * @return Response
     * @Route("/initialise/build/data/",name="initialise_build_data")
     * @IsGranted("ROLE_SYSTEM_ADMIN")
     * 1/07/2020 10:04
     */
    public function build()
    {
        $content = "<h3>Yes Built!!!</h3><ul>";
        $content .= ActionPermissionController::writeSecurityLinks();

        $this->doStuff();

        return new Response($content.'</ul><p><a href="/">Return</a></p>');
    }

    private function doStuff()
    {
       $result = [];
        foreach (ProviderFactory::getRepository(StudentRollGroup::class)->findAll() as $se) {
            $x = [];
            $x['student'] = $se->getStudent()->getPerson()->getSecurityUser()->getUsername();
            $x['rollGroup'] = $se->getRollGroup()->getAbbreviation();
            $result[] = $x;
        }
        file_put_contents(__DIR__ . '/../../../../Demo/student_roll_group.yaml', Yaml::dump($result, 8));
    }
}
