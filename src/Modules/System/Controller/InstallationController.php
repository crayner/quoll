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
 * Date: 20/05/2020
 * Time: 13:22
 */

namespace App\Modules\System\Controller;

use App\Container\ContainerManager;
use App\Controller\AbstractPageController;
use App\Manager\Entity\Language;
use App\Manager\PageManager;
use App\Modules\System\Entity\I18n;
use App\Modules\System\Form\LanguageType;
use App\Modules\System\Manager\InstallationManager;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Intl\Languages;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Class InstallationController
 * @package App\Modules\System\Controller
 */
class InstallationController extends AbstractPageController
{
    /**
     * installationCheck
     * @param PageManager $pageManager
     * @param InstallationManager $manager
     * @param ValidatorInterface $validator
     * @param ContainerManager $containerManager
     * @return JsonResponse
     * @Route("/installation/check/",name="installation_check")
     */
    public function installationCheck(PageManager $pageManager, InstallationManager $manager, ValidatorInterface $validator, ContainerManager $containerManager)
    {
        $i18n = new Language();

        foreach(I18n::getLanguages() as $name => $code) {
            dump($code, Languages::getName($code,$code), Languages::getName($code,'en_AU'), $name);
        }

        $form = $this->createForm(LanguageType::class, $i18n, ['action' => $this->generateUrl('installation_check', [], UrlGeneratorInterface::ABSOLUTE_URL)]);

        if ($this->getRequest()->getContent() !== '') {
            $i18n = new Language();
            $content = json_decode($this->getRequest()->getContent(), true);

            $i18n->setCode($content['code']);
            $form = $this->createForm(LanguageType::class, $i18n, ['action' => $this->generateUrl('installation_check', [], UrlGeneratorInterface::ABSOLUTE_URL)]);

            $list = $validator->validate($content['code'], [
                new NotBlank(),
                new Choice(['choices' => I18n::getLanguages()]),
            ]);

            if ($list->count() === 0) {
                $manager->setLocale($form->get('code')->getData());
                $manager->setInstallationStatus('mysql');
                $data = ErrorMessageHelper::getSuccessMessage([], true);
                $data['status'] = 'redirect';
                $data['redirect'] = $this->generateUrl('installation_mysql', [], UrlGeneratorInterface::ABSOLUTE_URL);
                $fs = new Filesystem();
                $fs->remove(__DIR__. '/../../var/cache/*');
                return new JsonResponse($data);
            } else {
                $data = ErrorMessageHelper::getInvalidInputsMessage([],true);
                $containerManager->singlePanel($form->createView());
                $data['form'] = $containerManager->getFormFromContainer();
                return new JsonResponse($data);
            }
        }

        $containerManager->singlePanel($form->createView());

        return $pageManager->render(
            [
                'content' => $manager->check($this->getParameter('systemRequirements')),
                'containers' => $containerManager->getBuiltContainers(),
            ]
        );
    }

}