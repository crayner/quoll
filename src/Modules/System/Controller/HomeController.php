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

namespace App\Modules\System\Controller;

use App\Controller\AbstractPageController;
use App\Modules\People\Manager\PhoneCodes;
use App\Modules\System\Entity\Country;
use App\Modules\System\Entity\Hook;
use App\Modules\System\Entity\Setting;
use App\Provider\ProviderFactory;
use App\Twig\Sidebar\Flash;
use App\Twig\Sidebar\Login;
use App\Twig\Sidebar\Register;
use Doctrine\DBAL\Driver\PDOException;
use Doctrine\DBAL\Exception\DriverException;
use Symfony\Component\Intl\Countries;
use Symfony\Component\Intl\Intl;
use Symfony\Component\Intl\Locale;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Yaml\Yaml;

/**
 * Class HomeController
 * @package App\Modules\System\Controller
 */
class HomeController extends AbstractPageController
{
    /**
     * @Route("/home/{timeout}", name="home")
     * @Route("/", name="unauthenticated")
     */
    public function home(string $timeout = '')
    {
        if ($this->isGranted('IS_AUTHENTICATED_FULLY'))
            return $this->redirectToRoute('personal_page');

        if (!$this->getParameter('installed')) {
            return $this->redirectToRoute('installation_check');
        }

        $pageManager = $this->getPageManager();
        $request = $pageManager->getRequest();

        if ($request->getContentType() !== 'json')
            return $this->render('react_base.html.twig',
                [
                    'page' => $pageManager,
                ]
            );

        if ($timeout !== '')
            $this->addFlash('warning', 'Your session expired, so you were automatically logged out of the system.');

        $sidebar = $pageManager->getSidebar();
        $sidebar->addContent(new Flash());
        $login = new Login();
        $login->setToken($this->get('security.csrf.token_manager')->getToken('authenticate'));
        $sidebar->addContent($login);

        if (ProviderFactory::create(Setting::class)->getSettingByScopeAsBoolean('User Admin', 'enablePublicRegistration'))
            $sidebar->addContent(new Register())->setDocked();

        try {
            $hooks = ProviderFactory::getRepository(Hook::class)->findBy(['type' => 'Public Home Page'], ['name' => 'ASC']);
        } catch (\PDOException | PDOException | DriverException $e) {
            $hooks = [];
        }
        return $pageManager->render(
            [
                'content' => trim($this->renderView('home/welcome.html.twig',
                        [
                            'hooks' => $hooks,
                        ]
                    )
                )
            ]
        );
    }

    /**
     * legacy
     * @Route("/personal/page/", name="personal_page")
     */
    public function personalPage()
    {
        if (!$this->isGranted('IS_AUTHENTICATED_FULLY'))
            return $this->redirectToRoute('home');

        return $this->getPageManager()->render(
            [
                'content' => '<h3 key="personal_page">Personal Page</h3>',
            ]
        );
    }
}
