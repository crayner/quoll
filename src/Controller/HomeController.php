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
 * Date: 11/04/2020
 * Time: 14:21
 */

namespace App\Controller;

use App\Modules\System\Entity\Hook;
use App\Modules\System\Entity\Setting;
use App\Provider\ProviderFactory;
use App\Twig\Sidebar\Flash;
use App\Twig\Sidebar\Login;
use App\Twig\Sidebar\Register;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class HomeController
 * @package App\Controller
 */
class HomeController extends AbstractPageController
{
    /**
     * @Route("/home/", name="home")
     * @Route("/", name="unauthenticated")
     */
    public function home()
    {
        if ($this->isGranted('IS_AUTHENTICATED_FULLY'))
            return $this->redirectToRoute('personal_page');

        $pageManager = $this->getPageManager();
        $request = $pageManager->getRequest();

        if ($request->getContentType() !== 'json')
            return $this->render('react_base.html.twig',
                [
                    'page' => $pageManager,
                ]
            );

        if ($request->query->get('timeout') === 'true')
            $this->addFlash('warning', 'Your session expired, so you were automatically logged out of the system.');

        $sidebar = $pageManager->getSidebar();
        $sidebar->addContent(new Flash());
        $sidebar->addContent(new Login());

        if (ProviderFactory::create(Setting::class)->getSettingByScopeAsBoolean('User Admin', 'enablePublicRegistration'))
            $sidebar->addContent(new Register())->setDocked();

        return $pageManager->render(
            [
                'content' => trim($this->renderView('home/welcome.html.twig',
                        [
                            'hooks' => ProviderFactory::getRepository(Hook::class)->findBy(['type' => 'Public Home Page'],['name' => 'ASC']),
                        ]
                    )
                )
            ]
        );
    }

    /**
     * legacy
     * @Route("/personal/page/", name="personal_page")
     * @Route("/legacy/{q}/", name="legacy")
     */
    public function personalPage(string $q = 'nothing')
    {
        if (!$this->isGranted('IS_AUTHENTICATED_FULLY'))
            return $this->redirectToRoute('home');

        if ($q !== 'nothing')
            dd($q);


        return $this->getPageManager()->render(
            [
                'content' => '',
            ]
        );

        dd($this->getUser());
    }
}