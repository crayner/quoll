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

use App\Manager\PageManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class HomeController
 * @package App\Controller
 */
class HomeController extends AbstractController
{
    /**
     * @Route("/", name="unauthenticated")
     * @param PageManager $pageManager
     * @return \Symfony\Component\HttpFoundation\JsonResponse|\Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function index(PageManager $pageManager)
    {
        if (!$this->isGranted('IS_AUTHENTICATED_FULLY')) {
            return $this->redirectToRoute('home');
        }

        return $pageManager->render('home/index.html.twig');

        $result = $manager->execute($request, $gibbonManager->getPage());

        if ($result instanceof Response){
            return $result;
        }

        return $this->render('index.html.twig',
            [
                'controller_name' => 'LegacyController',
                'manager' => $result,
            ]
        );
    }

    /**
     * @Route("/home/", name="home")
     * @param PageManager $pageManager
     * @return \Symfony\Component\HttpFoundation\JsonResponse|\Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function home(PageManager $pageManager)
    {
        if ($this->isGranted('IS_AUTHENTICATED_FULLY'))
            return $this->redirectToRoute('legacy');

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

        if (ProviderFactory::create(Setting::class)->getSettingByScopeAsBoolean('User Admin', 'enablePublicRegistration'))
            $sidebar->addContent(new Register())->setDocked();


        return $pageManager->render(['content' => trim($this->renderView('default/welcome.html.twig',
            [
                'hooks' => ProviderFactory::getRepository(Hook::class)->findBy(['type' => 'Public Home Page'],['name' => 'ASC']),
            ]
        ))]);
    }
}