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
 * Date: 17/04/2020
 * Time: 08:57
 */

namespace App\Modules\Security\Controller;

use App\Controller\AbstractPageController;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class PasswordController
 * @package App\Modules\Security\Controller
 */
class PasswordController extends AbstractPageController
{
    /**
     * reset
     * @Route("/password/reset/", name="password_reset")
     */
    public function reset()
    {

    }
}