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
use App\Modules\System\Entity\Locale;
use App\Modules\System\Form\Entity\MySQLSettings;
use App\Modules\System\Form\Entity\SystemSettings;
use App\Modules\System\Form\LanguageType;
use App\Modules\System\Form\MySQLType;
use App\Modules\System\Form\SystemType;
use App\Modules\System\Manager\CreateManager;
use App\Modules\System\Manager\InstallationManager;
use App\Util\TranslationHelper;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\JsonResponse;
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
     *
     * 25/08/2020 13:02
     * @param PageManager $pageManager
     * @param InstallationManager $manager
     * @param ValidatorInterface $validator
     * @Route("/installation/check/",name="installation_check")
     * @return JsonResponse
     */
    public function installationCheck(PageManager $pageManager, InstallationManager $manager, ValidatorInterface $validator)
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
                new Choice(['choices' => Locale::getLanguages()]),
            ]);

            if ($list->count() === 0) {
                $manager->setLocale($form->get('code')->getData());
                $manager->setInstallationStatus('mysql');
                $this->getStatusManager()
                    ->success()->setReDirect($this->generateUrl('installation_mysql', [], UrlGeneratorInterface::ABSOLUTE_URL), true);
                $fs = new Filesystem();
                $fs->remove(__DIR__ . '/../../../../var/cache/*');
            } else {
                $this->getStatusManager()->invalidInputs();
            }
            return $this->singleForm($form);
        }

        return $pageManager->render(
            [
                'content' => $manager->check($this->getParameter('systemRequirements')),
                'containers' => $this->getContainerManager()->singlePanel($form)->getBuiltContainers(),
            ]
        );
    }

    /**
     * installationMySQLSettings
     *
     * 25/08/2020 13:14
     * @param InstallationManager $manager
     * @param string $proceed
     * @Route("/installation/mysql/{proceed}", name="installation_mysql")
     * @return JsonResponse
     */
    public function installationMySQLSettings(InstallationManager $manager, string $proceed = '0')
    {
        $mysql = new MySQLSettings();
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
                $manager->setMySQLSettings($form,$this->getStatusManager());
                $this->getStatusManager()->setReDirect($this->generateUrl('installation_mysql', ['proceed' => 'proceed'], UrlGeneratorInterface::ABSOLUTE_URL));
                if ($proceed !== '0' && key_exists('proceedFlag', $content) && $content['proceedFlag'] === 'Ready to Go') {
                    $this->getStatusManager()->setReDirect($this->generateUrl('installation_table_create', [], UrlGeneratorInterface::ABSOLUTE_URL),true);
                }
            } else {
                $this->getStatusManager()->invalidInputs();
            }
            return $this->getStatusManager()->toJsonResponse();
        }

        $container = new Container();
        $panel = new Panel('single','System', new Section('html', $this->renderView('installation/mysql_settings.html.twig',
            [
                'message' => $this->getStatusManager()->getFirstMessageTranslated(),
                'proceed' => $proceed,
            ]
        )));

        $container->addForm('single', $form->createView())
            ->addPanel($panel->addSection(new Section('form', 'single')));

        return $this->getPageManager()->render(
            [
                'containers' => $this->getContainerManager()
                    ->addContainer($container)
                    ->getBuiltContainers(),
            ]
        );
    }

    /**
     * createTables
     *
     * 25/08/2020 13:21
     * @param CreateManager $manager
     * @Route("/installation/table/create/", name="installation_table_create")
     * @return JsonResponse
     */
    public function createTables(CreateManager $manager)
    {
        TranslationHelper::setDomain('System');

        $manager->getLogger()->notice(TranslationHelper::translate('The creation of tables for the database has commenced.'));

        $container = new Container();
        $panel = new Panel('single', 'System', new Section('html', $this->renderView('installation/table_complete.html.twig',
            [
                'tableCount' => $manager->createTables($this->getDoctrine()->getManager()),
            ]
        )));
        $container->addPanel($panel);
        $manager->setInstallationStatus('Create');

        return $this->getPageManager()->render(
            [
                'containers' => $this->getContainerManager()
                    ->addContainer($container)
                    ->getBuiltContainers(),
            ]
        );
    }

    /**
     * coreData
     *
     * 25/08/2020 13:21
     * @param CreateManager $createManager
     * @Route("/installation/core/data/",name="installation_core_data")
     * @return JsonResponse
     */
    public function coreData(CreateManager $createManager)
    {
        TranslationHelper::setDomain('System');

        $createManager->getLogger()->notice(TranslationHelper::translate('Core Data will be added to tables.'));
        $count = $createManager->coreData();
        $container = new Container();
        $panel = new Panel('single', 'System', new Section('html', $this->renderView('installation/core_data_complete.html.twig',
            [
                'tableCount' => $count,
                'itemCount' => $createManager->getTotalItemCount(),
            ]
        )));
        $container->addPanel($panel);

        $createManager->setInstallationStatus('Core Data');
        return $this->getPageManager()->render(
            [
                'containers' => $this->getContainerManager()->addContainer($container)->getBuiltContainers(),
            ]
        );
    }

    /**
     * systemSettings
     *
     * 27/08/2020 08:41
     * @param InstallationManager $installationManager
     * @param string $tabName
     * @Route("/installation/system/settings/{tabName}",name="installation_system_settings")
     * @return JsonResponse
     */
    public function systemSettings(InstallationManager $installationManager, string $tabName = 'System User')
    {
        $settings = new SystemSettings();
        $settings->injectRequest($this->getRequest());
        $message = null;

        $form = $this->createForm(SystemType::class, $settings, ['action' => $this->generateUrl('installation_system_settings', ['tabName' => $tabName])]);

        if ($this->getRequest()->getContent() !== '') {
            $content = json_decode($this->getRequest()->getContent(), true);
            $form->submit($content);
            if ($form->isValid()) {
                $installationManager->setAdministrator($form);
                $installationManager->setSystemSettings($form);
                $this->getStatusManager()->setReDirect($this->generateUrl('home'));
            }
            return $this->singleForm($form);
        }

        $container = new Container($tabName);
        $container->addForm('single', $form->createView());
        $panel = new Panel('System User', 'People', new Section('form', 'single'));
        $container->addPanel($panel);
        $panel = new Panel('Settings', 'System', new Section('form', 'single'));
        $container->addPanel($panel);
        $panel = new Panel('Organisation', 'System', new Section('form', 'single'));
        $container->addPanel($panel);

        return $this->getPageManager()->render(
            [
                'containers' => $this->getContainerManager()->addContainer($container)->getBuiltContainers(),
            ]
        );
    }
}
