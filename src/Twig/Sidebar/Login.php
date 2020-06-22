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
 * Date: 9/11/2019
 * Time: 07:55
 */
namespace App\Twig\Sidebar;

use App\Modules\School\Entity\AcademicYear;
use App\Modules\School\Util\AcademicYearHelper;
use App\Modules\System\Entity\I18n;
use App\Provider\ProviderFactory;
use App\Twig\SidebarContentInterface;
use App\Twig\SidebarContentTrait;
use App\Util\ImageHelper;
use App\Util\TranslationHelper;
use App\Util\UrlGeneratorHelper;
use Symfony\Component\Security\Csrf\CsrfToken;

/**
 * Class Login
 * @package App\Twig\SidebarContent
 */
class Login implements SidebarContentInterface
{
    use SidebarContentTrait;

    private $name = 'Login';

    private $position = 'top';

    /**
     * @var bool
     */
    private $googleOn = false;

    /**
     * @var CsrfToken
     */
    private $token;

    public function render(array $options): string
    {
        return '';
    }

    /**
     * toArray
     * @return array
     * @throws \Exception
     */
    public function toArray(): array
    {
        $lang = ProviderFactory::getRepository(I18n::class)->findSystemDefaultCode();

        return [
            'googleOAuth' => $this->getGoogleOAuth(),
            'login' => [
                'resetPasswordURL' => UrlGeneratorHelper::getUrl('password_reset'),
                'academicYears' => ProviderFactory::create(AcademicYear::class)->getSelectList(),
                'academicYear' => AcademicYearHelper::getCurrentAcademicYear()->getId(),
                'languages' => ProviderFactory::create(I18n::class)->getSelectedLanguages(),
                'language' => $lang ? ProviderFactory::getRepository(I18n::class)->findOneByCode($lang) : null,
                'token' => $this->getToken()->getValue(),
            ],
            'translations' => $this->getTranslations(),
        ];
    }

    /**
     * getGoogleOAuth
     * @return array
     */
    private function getGoogleOAuth(): array
    {
        return [
            'on' => $this->isGoogleOn(),
            'login_img' => ImageHelper::getAbsoluteImageURL('File','/build/static/google-login.svg'),
            'googleOAuthURL' => UrlGeneratorHelper::getUrl('google_oauth'),
        ];
    }

    /**
     * getTranslations
     * @return array
     */
    private function getTranslations(): array
    {
        return [
            'Login' => TranslationHelper::translate('Login', [], 'Security'),
            'Username or email' => TranslationHelper::translate('Username or email', [], 'Security'),
            'Password' => TranslationHelper::translate('Password', [], 'Security'),
            'Options' => TranslationHelper::translate('Options', [], 'Security'),
            'Forgot Password' => TranslationHelper::translate('Forgot Password', [], 'Security'),
            'Login with Google' => TranslationHelper::translate('Login with Google', [], 'Security'),
            'Language' => TranslationHelper::translate('Language', [], 'Security'),
            'Academic Year' => TranslationHelper::translate('Academic Year', [], 'School'),
        ];
    }

    /**
     * @return CsrfToken
     */
    public function getToken(): CsrfToken
    {
        return $this->token;
    }

    /**
     * Token.
     *
     * @param CsrfToken $token
     * @return Login
     */
    public function setToken(CsrfToken $token): Login
    {
        $this->token = $token;
        return $this;
    }

    /**
     * @return bool
     */
    public function isGoogleOn(): bool
    {
        return $this->googleOn;
    }

    /**
     * GoogleOn.
     *
     * @param bool $googleOn
     * @return Login
     */
    public function setGoogleOn(bool $googleOn): Login
    {
        $this->googleOn = $googleOn;
        return $this;
    }
}
