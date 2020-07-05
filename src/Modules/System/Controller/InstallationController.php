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
 * Date: 20/05/2020
 * Time: 13:22
 */

namespace App\Modules\System\Controller;

use App\Container\Container;
use App\Container\ContainerManager;
use App\Container\Panel;
use App\Container\Section;
use App\Controller\AbstractPageController;
use App\Form\Type\SubmitOnlyType;
use App\Manager\Hidden\Language;
use App\Manager\PageManager;
use App\Modules\System\Entity\I18n;
use App\Modules\System\Form\Entity\MySQLSettings;
use App\Modules\System\Form\Entity\SystemSettings;
use App\Modules\System\Form\LanguageType;
use App\Modules\System\Form\MySQLType;
use App\Modules\System\Form\SystemType;
use App\Modules\System\Manager\CreateManager;
use App\Modules\System\Manager\InstallationManager;
use App\Util\ErrorMessageHelper;
use App\Util\TranslationHelper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Yaml\Yaml;

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
                $fs->remove(__DIR__ . '/../../../../var/cache/*');
                return new JsonResponse($data);
            } else {
                $data = ErrorMessageHelper::getInvalidInputsMessage([], true);
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

    /**
     * installationMySQLSettings
     * @param InstallationManager $manager
     * @param ContainerManager $containerManager
     * @param string $proceed
     * @Route("/installation/mysql/{proceed}", name="installation_mysql")
     */
    public function installationMySQLSettings(InstallationManager $manager, ContainerManager $containerManager, string $proceed = '0')
    {
        $mysql = new MySQLSettings($proceed);
        $manager->readCurrentMySQLSettings($mysql);
        $data = null;

        $form = $this->createForm(MySQLType::class, $mysql, ['action' => $this->generateUrl('installation_mysql', ['proceed' => $proceed], UrlGeneratorInterface::ABSOLUTE_URL), 'proceed' => $proceed]);

        if ($this->getRequest()->getContent() !== '') {
            $content = json_decode($this->getRequest()->getContent(), true);
            $form->submit($content);

            if ($form->isValid()) {
                if ($proceed === 0) {
                    $manager->setInstallationStatus('build');
                }
                $data = $manager->setMySQLSettings($form);
                $data['status'] = 'redirect';
                $data['redirect'] = $this->generateUrl('installation_mysql', ['proceed' => 'proceed'], UrlGeneratorInterface::ABSOLUTE_URL);
                if ($proceed !== '0' && key_exists('proceedFlag', $content) && $content['proceedFlag'] === 'Ready to Go') {
                    $data['redirect'] = $this->generateUrl('installation_table_create', [], UrlGeneratorInterface::ABSOLUTE_URL);
                }
            } else {
                $containerManager->singlePanel($form->createView());
                $data = ErrorMessageHelper::getInvalidInputsMessage([], true);
                $data['form'] = $containerManager->getFormFromContainer();
            }
            return new JsonResponse($data);
        }

        $containerManager->singlePanel($form->createView());

        return $this->getPageManager()->render(
            [
                'content' => $this->renderView('installation/mysql_settings.html.twig',
                    [
                        'message' => $data ? $data['errors'][0] : null,
                        'proceed' => $proceed,
                    ]
                ),
                'containers' => $containerManager->getBuiltContainers(),
            ]
        );
    }

    /**
     * installationBuild
     * @param CreateManager $manager
     * @param ContainerManager $containerManager
     * @return JsonResponse
     * @Route("/installation/table/create/", name="installation_table_create")
     */
    public function createTables(CreateManager $manager, ContainerManager $containerManager)
    {
        TranslationHelper::setDomain('System');

        $form = $this->createForm(SubmitOnlyType::class, null,
            [
                'action' => $this->generateUrl('installation_table_create'),
                'translation_domain' => 'System',
                'submitLabel' => 'Proceed',
            ]
        );

        if ($this->getRequest()->getContent() !== '') {
            $content = json_decode($this->getRequest()->getContent(), true);
            $form->submit($content);
            if ($form->isValid()) {
                $data['redirect'] = $this->generateUrl('installation_core_data');
                $data['status'] = 'redirect';

                return new JsonResponse($data);
            }
        }

        $containerManager->singlePanel($form->createView());
        $manager->getLogger()->notice(TranslationHelper::translate('The creation of tables for the database has commenced.'));
        $manager->setInstallationStatus('Create');
        return $this->getPageManager()->render(
            [
                'content' => $this->renderView('installation/table_complete.html.twig',
                    [
                        'tableCount' => $manager->createTables(),
                    ]
                ),
                'containers' => $containerManager->getBuiltContainers(),
            ]
        );
    }

    /**
     * coreData
     * @param CreateManager $createManager
     * @param ContainerManager $manager
     * @return JsonResponse
     * @Route("/installation/core/data/",name="installation_core_data")
     */
    public function coreData(CreateManager $createManager, ContainerManager $manager)
    {
        TranslationHelper::setDomain('System');

        $form = $this->createForm(SubmitOnlyType::class, null,
            [
                'action' => $this->generateUrl('installation_core_data'),
                'translation_domain' => 'System',
                'submitLabel' => 'Proceed',
            ]
        );

        if ($this->getRequest()->getContent() !== '') {
            $content = json_decode($this->getRequest()->getContent(), true);
            $form->submit($content);
            if ($form->isValid()) {
                $data['redirect'] = $this->generateUrl('installation_foreign_constraints');
                $data['status'] = 'redirect';

                return new JsonResponse($data);
            }
        }

        $manager->singlePanel($form->createView());
        $createManager->getLogger()->notice(TranslationHelper::translate('Core Data will be added to tables.'));
        $createManager->setInstallationStatus('Core Data');
        return $this->getPageManager()->render(
            [
                'content' => $this->renderView('installation/core_data_complete.html.twig',
                    [
                        'tableCount' => $createManager->coreData(),
                    ]
                ),
                'containers' => $manager->getBuiltContainers(),
            ]
        );
    }

    /**
     * demoData
     * @param CreateManager $createManager
     * @param ContainerManager $manager
     * @return JsonResponse
     * @Route("/installation/foreign/constraints/",name="installation_foreign_constraints")
     */
    public function foreignConstraints(CreateManager $createManager, ContainerManager $manager)
    {
        TranslationHelper::setDomain('System');

        $form = $this->createForm(SubmitOnlyType::class, null,
            [
                'action' => $this->generateUrl('installation_foreign_constraints'),
                'translation_domain' => 'System',
                'submitLabel' => 'Proceed',
            ]
        );

        if ($this->getRequest()->getContent() !== '') {
            $content = json_decode($this->getRequest()->getContent(), true);
            $form->submit($content);
            if ($form->isValid()) {
                $data['redirect'] = $this->generateUrl('installation_system_settings');
                $data['status'] = 'redirect';

                return new JsonResponse($data);
            }
        }

        $manager->singlePanel($form->createView());
        $createManager->setInstallationStatus('Foreign Constraints');
        $createManager->getLogger()->notice(TranslationHelper::translate('Foreign Constraints will be added to tables.'));
        return $this->getPageManager()->render(
            [
                'content' => $this->renderView('installation/foreign_constraint_complete.html.twig',
                    [
                        'tableCount' => $createManager->foreignConstraints(),
                    ]
                ),
                'containers' => $manager->getBuiltContainers(),
            ]
        );
    }

    /**
     * systemSettings
     * @Route("/installation/system/settings/{tabName}",name="installation_system_settings")
     * @param InstallationManager $installationManager
     * @param ContainerManager $manager
     * @param string $tabName
     * @return JsonResponse
     */
    public function systemSettings(InstallationManager $installationManager, ContainerManager $manager, string $tabName = 'System User')
    {
        $settings = new SystemSettings();
        $settings->injectRequest($this->getRequest());
        $message = null;

        $form = $this->createForm(SystemType::class, $settings, ['action' => $this->generateUrl('installation_system_settings', ['tabName' => $tabName])]);

        if ($this->getRequest()->getContent() !== '') {
            $content = json_decode($this->getRequest()->getContent(), true);
            $form->submit($content);
            if ($form->isSubmitted()) {
                if ($form->isValid()) {
                    $installationManager->setAdministrator($form);
                    $installationManager->setSystemSettings($form);
                }
                $data['redirect'] = $this->generateUrl('home');
                $data['status'] = 'redirect';
                return new JsonResponse($data);
            }
        }

        $manager->singlePanel($form->createView());
        $container = new Container($tabName);
        $container->addForm('single', $form->createView());
        $panel = new Panel('System User', 'People', new Section('form', 'single'));
        $container->addPanel($panel);
        $panel = new Panel('Settings', 'System', new Section('form', 'single'));
        $container->addPanel($panel);
        $panel = new Panel('Organisation', 'System', new Section('form', 'single'));
        $container->addPanel($panel);
        $manager->addContainer($container);

        return $this->getPageManager()->render(
            [
                'containers' => $manager->getBuiltContainers(),
            ]
        );
    }
}
