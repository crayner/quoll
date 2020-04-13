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
 * Date: 9/11/2019
 * Time: 07:55
 */

namespace App\Twig\Sidebar;

use App\Modules\School\Entity\AcademicYear;
use App\Modules\School\Util\AcademicYearHelper;
use App\Modules\System\Entity\I18n;
use App\Modules\System\Entity\Setting;
use App\Provider\ProviderFactory;
use App\Twig\SidebarContentInterface;
use App\Twig\SidebarContentTrait;
use App\Util\ImageHelper;
use App\Util\TranslationsHelper;
use App\Util\UrlGeneratorHelper;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

/**
 * Class Login
 * @package App\Twig\SidebarContent
 */
class Login implements SidebarContentInterface
{
    use SidebarContentTrait;

    private $name = 'Login';

    private $position = 'top';

    public function render(array $options): string
    {
        try {
            return trim($this->getTwig()->render('default/sidebar/login.html.twig'));
        } catch (LoaderError | RuntimeError | SyntaxError $e) {
            return '';
        }
    }

    /**
     * toArray
     * @return array
     */
    public function toArray(): array
    {
        $lang = ProviderFactory::create(I18n::class)->findOneBy(['systemDefault' => 'Y']);
        return [
            'googleOAuth' => $this->getGoogleOAuth(),
            'login' => [
                'resetPasswordURL' => UrlGeneratorHelper::getUrl('legacy', ['q' => 'passwordReset.php']),
                'academicYears' => ProviderFactory::create(AcademicYear::class)->getSelectList(),
                'academicYear' => AcademicYearHelper::getCurrentAcademicYear()->getId(),
                'languages' => ProviderFactory::create(I18n::class)->getSelectedLanguages(),
                'language' => $lang ? $lang->getId() : 0,
            ],
            'translations' => $this->getTranslations(),
        ];
    }

    private function getGoogleOAuth(): array
    {
        return [
            'on' => ProviderFactory::create(Setting::class)->getSettingByScopeAsBoolean('System', 'googleOAuth'),
            'login_img' => ImageHelper::getAbsoluteImageURL('File','/build/static/google-login.svg'),
            'googleOAuthURL' => UrlGeneratorHelper::getUrl('google_oauth'),
        ];
    }

    private function getTranslations(): array
    {
        return [
            'Login' => TranslationsHelper::translate('Login', [], 'UserAdmin'),
            'Username or email' => TranslationsHelper::translate('Username or email', [], 'UserAdmin'),
            'Password' => TranslationsHelper::translate('Password', [], 'UserAdmin'),
            'Options' => TranslationsHelper::translate('Options', [], 'UserAdmin'),
            'Forgot Password' => TranslationsHelper::translate('Forgot Password', [], 'UserAdmin'),
            'Login with Google' => TranslationsHelper::translate('Login with Google', [], 'UserAdmin'),
            'Language' => TranslationsHelper::translate('Language', [], 'UserAdmin'),
            'Academic Year' => TranslationsHelper::translate('Academic Year', [], 'SchoolAdmin'),
        ];
    }
}
