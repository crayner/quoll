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
 * Date: 17/04/2020
 * Time: 15:13
 */

namespace App\Modules\People\Controller;

use App\Controller\AbstractPageController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class PeopleController
 * @package App\Modules\People\Controller
 */
class PeopleController extends AbstractPageController
{
    /**
     * list
     * @Route("/people/list/",name="people_list")
     * @IsGranted("ROLE_ROUTE")
     */
    public function list()
    {}
}