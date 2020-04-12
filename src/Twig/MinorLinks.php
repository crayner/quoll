<?php
/**
 * Created by PhpStorm.
 *
 * kookaburra
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

use App\Modules\System\Entity\Setting;
use App\Provider\ProviderFactory;
use App\Util\TranslationsHelper;
use App\Util\UrlGeneratorHelper;
use App\Modules\Security\Util\SecurityHelper;

class MinorLinks implements ContentInterface
{
    use ContentTrait;

    /**
     * @var array
     */
    private $content = [];

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
        $links = [];
        $languageLink = false;
        // Add a link to go back to the system/personal default language, if we're not using it
        if ($this->getSession()->has(['i18n','default','code']) && $this->getSession()->has(['i18n','code'])) {
            if ($this->getSession()->get(['i18n','code']) !== $this->getSession()->get(['i18n','default','code'])) {
                $systemDefaultShortName = trim(strstr($this->getSession()->get(['i18n','default','name']), '-', true));
                $languageLink =
                    [
                        'url' => [
                            'route' => 'locale_switch',
                            'params' => ['i18n' => $this->getSession()->get(['i18n','default','code'])],
                        ],
                        'text' => $systemDefaultShortName,
                        'class' => 'link-white',
                        'translation_domain' => false,
                    ];
            }
        }

        if (!SecurityHelper::isGranted('IS_AUTHENTICATED_FULLY')) {
            if ($languageLink)
                $links[] = $languageLink;

            if (ProviderFactory::create(Setting::class)->hasSettingByScope('System', 'webLink')) {
                $links[] = [
                    'url' => ProviderFactory::create(Setting::class)->getSettingByScopeAsString('System', 'webLink', 'https:://www.craigrayner.com'),
                    'text' => ['organisation_website', ['%name%' => ProviderFactory::create(Setting::class)->getSettingByScopeAsString('System', 'organisationNameShort', 'Kookaburra')]],
                    'translation_domain' => 'messages',
                    'target' => '_blank',
                    'class' => 'link-white',
                ];

            }
        } else {
            $name = $this->getSession()->get('preferredName').' '.$this->getSession()->get('surname');
            if ($this->getSession()->has('gibbonRoleIDCurrentCategory')) {
                if ($this->getSession()->get('gibbonRoleIDCurrentCategory') === 'Student') {
                    $highestAction = SecurityHelper::getHighestGroupedAction('/modules/Students/student_view_details.php');
                    if ($highestAction == 'View Student Profile_brief') {
                        $name = [
                            'class' => 'link-white',
                            'text' => $name,
                            'translation_domain' => false,
                            'url' => $this->getSession()->get('absoluteURL').'/?q=/modules/Students/student_view_details.php&gibbonPersonID='.$this->getSession()->get('gibbonPersonID'),
                        ];
                    }
                }
            }
            if (is_string($name)){
                $name = [
                    'text' => $name,
                    'url' => '',
                    'translation_domain' => false,
                ];
            }
            $links[] = $name;

            $links[] = [
                'class' => 'link-white',
                'text' => TranslationsHelper::translate('Logout', [], 'messages'),
                'url' => UrlGeneratorHelper::getUrl('logout'),
                'translation_domain' => 'messages',
            ];
            $links[] = [
                'class' => 'link-white',
                'text' => TranslationsHelper::translate('Preferences', [], 'messages'),
                'url' => UrlGeneratorHelper::getUrl('user_admin__preferences'),
                'translation_domain' => 'messages',
            ];
            if ($this->getSession()->get('emailLink', '') !== '') {
                $links[] = [
                    'class' => 'link-white',
                    'text' => TranslationsHelper::translate('Email'),
                    'url' => $this->getSession()->get('emailLink'),
                    'target' => '_blank',
                    'wrapper' => ['type' => 'span', 'class' => 'hidden sm:inline'],
                ];
            }
            if ($this->getSession()->get('webLink', '') !== '') {
                $links[] = [
                    'url' => $this->getSession()->get('webLink', ''),
                    'text' => ['organisation_website', ['%name%' => $this->getSession()->get('organisationNameShort')]],
                    'translation_domain' => 'messages',
                    'target' => '_blank',
                    'class' => 'link-white',
                    'wrapper' => ['type' => 'span', 'class' => 'hidden sm:inline'],
                ];
            }
            if ($this->getSession()->get('website', '') !== '') {
                $links[] = [
                    'url' => $this->getSession()->get('website', ''),
                    'text' => 'My Website',
                    'translation_domain' => 'messages',
                    'target' => '_blank',
                    'class' => 'link-white',
                    'wrapper' => ['type' => 'span', 'class' => 'hidden sm:inline'],
                ];
            }

            if ($languageLink)
                $links[] = $languageLink;

            //Check for house logo (needed to get bubble, below, in right spot)
            if ($this->getSession()->has('gibbonHouseIDLogo') && $this->getSession()->has('gibbonHouseIDName')) {
                if ($this->getSession()->get('gibbonHouseIDLogo', '') !== '') {
                    $this->houseLogo = [
                        'class' => 'ml-1 w-10 h-10 sm:w-12 sm:h-12 lg:w-16 lg:h-16',
                        'title' => $this->getSession()->get('gibbonHouseIDName'),
                        'style' => 'vertical-align: -75%;',
                        'src' => '/'.$this->getSession()->get('gibbonHouseIDLogo'),
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
        return $this->content;
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
}