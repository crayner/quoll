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
 * Date: 11/04/2020
 * Time: 14:21
 */
namespace App\Modules\System\Controller;

use App\Controller\AbstractPageController;
use App\Modules\System\Entity\Hook;
use App\Modules\System\Manager\SettingFactory;
use App\Provider\ProviderFactory;
use App\Twig\Sidebar\Flash;
use App\Twig\Sidebar\Login;
use App\Twig\Sidebar\Register;
use App\Util\TranslationHelper;
use Doctrine\DBAL\Driver\PDOException;
use Doctrine\DBAL\Exception\DriverException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class HomeController
 * @package App\Modules\System\Controller
 * @author Craig Rayner <craig@craigrayner.com>
 */
class HomeController extends AbstractPageController
{
    /**
     * @Route("/home/{timeout}", name="home")
     * @Route("/craig/{timeout}", name="craig")
     * @Route("/", name="unauthenticated")
     * @param string $timeout
     * @return JsonResponse|RedirectResponse|Response
     */
    public function home(string $timeout = '')
    {
        if ($this->isGranted('IS_AUTHENTICATED_FULLY')) {
            if ($this->hasParameter('base_route') && $this->getParameter('base_route') !== null && $this->getParameter('environment') !== 'prod') {
                $this->addFlash('info', 'A base route redirection was applied.');
                return $this->redirectToRoute($this->getParameter('base_route'));
            }

            return $this->redirectToRoute('personal_page');
        }
        if (!$this->getParameter('installed')) {
            return $this->redirectToRoute('installation_check');
        }

        $pageManager = $this->getPageManager();
        $pageManager->setTitle(TranslationHelper::translate('Personal Page', [], 'System'));
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
        $login->setGoogleOn($this->getParameter('google_oauth'));
        $login->setToken($this->get('security.csrf.token_manager')->getToken('authenticate'));
        $sidebar->addContent($login);

        if (SettingFactory::getSettingManager()->get('People', 'enablePublicRegistration'))
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
     * personalPage
     *
     * 22/11/2020 08:33
     * @Route("/personal/page/", name="personal_page")
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     * @return JsonResponse|RedirectResponse
     */
    public function personalPage()
    {
        if ($this->getRequest()->attributes->has('_switch_user')) {
            return $this->getPageManager()->render(
                [
                    'content' => $this->renderView('components/redirect_on_page_load.html.twig', ['page' => $this->generateUrl('personal_page')]),
                ]
            );
        } else {
            if ($this->hasParameter('base_route') && $this->getParameter('base_route') !== null && $this->getParameter('environment') !== 'prod') {
                $this->addFlash('info', 'A base route redirection was applied.');
                return $this->redirectToRoute($this->getParameter('base_route'));
            }

            return $this->getPageManager()->render(
                [
                    'content' => '<h3 key="personal_page">Personal Page</h3>',
                ]
            );
        }
    }
}
