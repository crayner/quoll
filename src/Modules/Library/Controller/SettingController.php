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
 * Date: 7/06/2020
 * Time: 09:37
 */
namespace App\Modules\Library\Controller;

use App\Container\ContainerManager;
use App\Controller\AbstractPageController;
use App\Modules\Library\Entity\Library;
use App\Modules\Library\Form\LibraryType;
use App\Modules\Library\Manager\LibraryHelper;
use App\Provider\ProviderFactory;
use App\Util\ErrorMessageHelper;
use App\Util\TranslationHelper;
use PhpParser\Node\Expr\Instanceof_;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class SettingController
 * @package App\Modules\Library\Controller
 * @author Craig Rayner <craig@craigrayner.com>
 */
class SettingController extends AbstractPageController
{
    /**
     * settings
     * @param ContainerManager $manager
     * @param LibraryHelper $helper
     * @param Library $library
     * @return JsonResponse|Response
     * @Route("/library/settings/",name="library_settings")
     * @Route("/library/add/",name="library_add")
     * @Route("/library/{library}/edit/",name="library_edit")
     * @IsGranted("ROLE_ROUTE")
     * 8/06/2020 08:40
     */
    public function edit(ContainerManager $manager, LibraryHelper $helper, Library $library = null)
    {
        $action = $this->generateUrl('library_add');

        if ($this->getRequest()->get('_route') === 'library_add') {
            $library = new Library();
        } else if ($this->getRequest()->getMethod() === 'POST') {
            $content = json_decode($this->getRequest()->getContent(), true);
            if ($content['submit_clicked'] === 'workingOn') {
                $library = ProviderFactory::getRepository(Library::class)->find($content['workingOn']) ?: new Library();
                LibraryHelper::setCurrentLibrary($library);
            }
        }

        $library = $library ?: (LibraryHelper::getCurrentLibrary() ?: new Library());
        if ($library->getId() !== null) {
            $action = $this->generateUrl('library_edit', ['library' => $library->getId()]);
        }

        $manager->setShowSubmitButton(true);
        TranslationHelper::setDomain('Library');

        $form = $this->createForm(LibraryType::class, $library, ['action' => $action]);

        if ($this->getRequest()->getContent() !== '') {
            $content = json_decode($this->getRequest()->getContent(), true);
/*            if ($content['workingOn'] !== $library->getId() && $content['submit_clicked'] === 'workingOn') {
                $library = ProviderFactory::getRepository(Library::class)->find($content['workingOn']) ?: new Library();
                $form = $this->createForm(LibraryType::class, $library, ['action' => $action]);
                $manager->singlePanel($form->createView());
                $this->getRequest()->getSession()->getBag('flashes')->clear();
                if ($library->getId() !== null) {
                    $helper::setCurrentLibrary($library);
                    $this->addFlash('warning', TranslationHelper::translate("The current library has been switched to '{name}'", ['{name}' => $library->getName()], 'Library'));
                }

                return new JsonResponse(
                    [
                        'errors' => [],
                        'status' => 'redirect',
                        'redirect' => $this->generateUrl('library_edit', ['library' => $library->getId()]),
                    ]
                );
            }
*/

            if ($content['submit_clicked'] !== 'workingOn') {
                unset($content['submit_clicked']);
                $form->submit($content);

                if ($form->isValid()) {
                    $id = $library->getId();
                    $data = ProviderFactory::create(Library::class)->persistFlush($library, []);
                    if ($data['status'] === 'success' && $id !== $library->getId()) {
                        $data['status'] = 'redirect';
                        $data['redirect'] = $this->generateUrl('library_edit', ['library' => $library->getId()]);
                        $this->addFlash('success', ErrorMessageHelper::onlySuccessMessage(true));
                        LibraryHelper::setCurrentLibrary($library);
                    }
                } else {
                    $data = ErrorMessageHelper::getInvalidInputsMessage([], true);
                }
            }
            $manager->singlePanel($form->createView());
            $data['form'] = $manager->getFormFromContainer();
            return new JsonResponse($data);
        }
        $manager->setAddElementRoute($this->generateUrl('library_add'))->singlePanel($form->createView());

        return $this->getPageManager()
            ->createBreadcrumbs('Library Settings')
            ->render(['containers' => $manager->getBuiltContainers()]);
    }

}