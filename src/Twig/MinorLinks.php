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

use App\Modules\School\Entity\House;
use App\Modules\Security\Manager\SecurityUser;
use App\Modules\Security\Util\SecurityHelper;
use App\Modules\System\Entity\I18n;
use App\Modules\System\Entity\Setting;
use App\Provider\ProviderFactory;
use App\Util\TranslationHelper;
use App\Util\UrlGeneratorHelper;

/**
 * Class MinorLinks
 * @package App\Twig
 */
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
        if (!$this->hasSession())
            return ;

        $links = [];
        $languageLink = false;
        // Add a link to go back to the system/personal default language, if we're not using it
        if ($this->getSession()->has('i18n')) {
            $locale = $this->getSession()->get('i18n');
            if ($locale->getCode() !== $this->getRequest()->getDefaultLocale()) {
                $defaultLocale = ProviderFactory::getRepository(I18n::class)->findOneByCode($this->getRequest()->getDefaultLocale());
                $languageLink =
                    [
                        'url' => [
                            'route' => 'locale_switch',
                            'params' => ['i18n' => $defaultLocale->getCode()],
                        ],
                        'text' => $defaultLocale->getShortName(),
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
                    'url' => ProviderFactory::create(Setting::class)->getSettingByScopeAsString('System', 'webLink'),
                    'text' => ProviderFactory::create(Setting::class)->getSettingByScopeAsString('System', 'organisationAbbreviation', 'Quoll'),
                    'translation_domain' => 'messages',
                    'target' => '_blank',
                    'class' => 'link-white',
                ];
            }
        } else {
            $person = SecurityHelper::getCurrentUser()->getPerson();
            $name = $person->formatName(['preferred' => true, 'reverse' => false]);
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
            $provider = ProviderFactory::create(Setting::class);

            $links[] = [
                'class' => 'link-white',
                'text' => TranslationHelper::translate('Logout', [], 'Security'),
                'url' => UrlGeneratorHelper::getUrl('logout'),
                'translation_domain' => 'Security',
            ];
            $links[] = [
                'class' => 'link-white',
                'text' => TranslationHelper::translate('Preferences', [], 'People'),
                'url' => UrlGeneratorHelper::getUrl('preferences'),
                'translation_domain' => 'People',
            ];
            if ($provider->hasSettingByScope('System','emailLink')) {
                $links[] = [
                    'class' => 'link-white',
                    'text' => TranslationHelper::translate('Email', [], 'People'),
                    'url' => $provider->getSettingByScopeAsString('System','emailLink'),
                    'target' => '_blank',
                    'wrapper' => ['type' => 'span', 'class' => 'hidden sm:inline'],
                ];
            }
            if ($provider->hasSettingByScope('System','webLink')) {
                $links[] = [
                    'url' => $provider->getSettingByScopeAsString('System','webLink'),
                    'text' => $provider->getSettingByScopeAsString('System', 'organisationAbbreviation'),
                    'translation_domain' => 'School',
                    'target' => '_blank',
                    'class' => 'link-white',
                    'wrapper' => ['type' => 'span', 'class' => 'hidden sm:inline'],
                ];
            }

            if ($languageLink)
                $links[] = $languageLink;

            //Check for house logo (needed to get bubble, below, in right spot)
            if ($person->getHouse() instanceof House) {
                $house = $person->getHouse();
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
}