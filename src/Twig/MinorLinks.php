<?php
/**
 * Created by PhpStorm.
 *
 * Project: Kookaburra
 * Build: Quoll
 *
 * (c) 2019 Craig Rayner <craig@craigrayner.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * User: craig
 * Date: 29/07/2019
 * Time: 14:32
 */
namespace App\Twig;

use App\Modules\People\Entity\Person;
use App\Modules\School\Entity\House;
use App\Modules\Security\Util\SecurityHelper;
use App\Modules\System\Entity\Locale;
use App\Modules\System\Manager\SettingFactory;
use App\Provider\ProviderFactory;
use App\Util\TranslationHelper;
use App\Util\UrlGeneratorHelper;

/**
 * Class MinorLinks
 * @package App\Twig
 * @author Craig Rayner <craig@craigrayner.com>
 */
class MinorLinks implements ContentInterface
{
    use ContentTrait;

    /**
     * @var array
     */
    private array $content;

    /**
     * @var array
     */
    private $houseLogo = [];

    /**
     * execute
     * @throws \Exception
     */
    public function execute(): void
    {
        if (!$this->hasSession())
            return;

        if (isset($this->content) && !empty($this->content)) return;

        $links = [];
        $person = $this->getPerson();

        if (!SecurityHelper::isGranted('IS_AUTHENTICATED_FULLY')) {
            if (SettingFactory::getSettingManager()->has('System', 'webLink', true)) {
                $links[] = [
                    'url' => SettingFactory::getSettingManager()->get('System', 'webLink'),
                    'text' => SettingFactory::getSettingManager()->get('System', 'organisationAbbreviation', 'Quoll'),
                    'translation_domain' => 'messages',
                    'target' => '_blank',
                    'class' => 'link-white',
                ];
            }
        } else {
            $name = $person->getFullName();
            if ($person->isStudent()) {
                $highestAction = SecurityHelper::getHighestGroupedAction('student_view');
                if ($highestAction == 'View Student Profile_brief') {
                    $name = [
                        'class' => 'link-white',
                        'text' => $name,
                        'translation_domain' => false,
                        'url' => UrlGeneratorHelper::getUrl('student_view'),
                    ];
                }
            }

            if (is_string($name) && '' !== $name){
                $name = [
                    'text' => $name,
                    'url' => '',
                    'translation_domain' => false,
                ];
            }
            $links[] = $name;
            $provider = SettingFactory::getSettingManager();

            if (SecurityHelper::isGranted('IS_IMPERSONATOR')) {
                $links[] = [
                    'class' => 'link-white',
                    'text' => TranslationHelper::translate('Restore Self', [], 'Security'),
                    'url' => UrlGeneratorHelper::getUrl('personal_page', ['_switch_user' => '_exit']),
                    'translation_domain' => 'Security',
                ];
            } else {
                $links[] = [
                    'class' => 'link-white',
                    'text' => TranslationHelper::translate('Logout', [], 'Security'),
                    'url' => UrlGeneratorHelper::getUrl('logout'),
                    'translation_domain' => 'Security',
                ];
            }
            $links[] = [
                'class' => 'link-white',
                'text' => TranslationHelper::translate('Preferences', [], 'People'),
                'url' => UrlGeneratorHelper::getUrl('preferences'),
                'translation_domain' => 'People',
            ];
            if ($provider->has('System','emailLink', true)) {
                $links[] = [
                    'class' => 'link-white',
                    'text' => TranslationHelper::translate('Email', [], 'People'),
                    'url' => $provider->get('System','emailLink'),
                    'target' => '_blank',
                    'wrapper' => ['type' => 'span', 'class' => 'hidden sm:inline'],
                ];
            }
            if ($provider->has('System','webLink', true)) {
                $links[] = [
                    'url' => $provider->get('System','webLink'),
                    'text' => $provider->get('System', 'organisationAbbreviation'),
                    'translation_domain' => 'School',
                    'target' => '_blank',
                    'class' => 'link-white',
                    'wrapper' => ['type' => 'span', 'class' => 'hidden sm:inline'],
                ];
            }

            $links = $this->getLocaleLinks($links);

            // Check for house logo (needed to get bubble, below, in right spot)
            if (($person->isStudent() && ($house = $person->getStudent()->getHouse()) instanceof House) || ($person->isStaff() && ($house = $person->getStaff()->getHouse()) instanceof House)) {
                if ($house->getLogo() !== '') {
                    $this->houseLogo = [
                        'class' => 'ml-1 w-10 h-10 sm:w-12 sm:h-12 lg:w-16 lg:h-16',
                        'title' => $house->getName(),
                        'style' => 'vertical-align: -75%;',
                        'src' => $house->getLogo(),
                    ];
                }
            }
        }

        $this->content = $links;
    }

    /**
     * @return array
     */
    public function getContent(): array
    {
        $this->execute();
        return $this->content ?: [];
    }

    /**
     * Content.
     *
     * @param array $content
     * @return MinorLinks
     */
    public function setContent(array $content): MinorLinks
    {
        $this->content = $content;
        return $this;
    }

    /**
     * @return array
     */
    public function getHouseLogo(): array
    {
        return $this->houseLogo;
    }

    /**
     * getPerson
     * @return Person|null
     * 28/06/2020 09:40
     */
    private function getPerson(): ?Person
    {
        return SecurityHelper::getCurrentUser() ? SecurityHelper::getCurrentUser()->getPerson() : null;
    }

    /**
     * getLocaleLinks
     * @param array $links
     * @return array
     * 28/06/2020 09:54
     */
    private function getLocaleLinks(array $links)
    {
        // Add a link to go back to the system/personal default language, if we're not using it.
        $languageLinks = false;
        if ($this->getSession()->has('i18n') && $this->getSession()->get('i18n')->getPerson()->isEqualTo($this->getPerson())) {
            $locale = $this->getSession()->get('i18n');
        }
        if (isset($locale) && $locale->getCode() !== $this->getRequest()->getDefaultLocale()) {
            $defaultLocale = ProviderFactory::getRepository(Locale::class)->findOneByCode($this->getRequest()->getDefaultLocale());
            $links[] =
                [
                    'url' => [
                        'route' => 'locale_switch',
                        'params' => ['i18n' => $defaultLocale->getCode()],
                    ],
                    'text' => $defaultLocale->getCode(),
                    'class' => 'link-white',
                    'translation_domain' => false,
                ];
        }
        return $links;
    }
}