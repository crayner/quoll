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
 * Date: 8/09/2019
 * Time: 16:29
 */

namespace App\Modules\System\Manager;

use App\Manager\ParameterFileManager;
use App\Modules\Security\Manager\SecurityUser;
use App\Modules\Security\Util\SecurityHelper;
use App\Modules\System\Manager\SettingFactory;
use App\Provider\ProviderFactory;
use App\Util\ParameterBagHelper;
use App\Util\TranslationHelper;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mailer\Transport\Dsn;
use Symfony\Component\Mime\Address;

/**
 * Class MailerSettingsManager
 * @package App\Modules\System\Manager
 */
class MailerSettingsManager
{
    /**
     * @var string|null
     */
    private $enableMailerSMTP;

    /**
     * @var string[]
     */
    private static $enableMailerSMTPList = [
        'gmail',
        'smtp',
    ];

    /**
     * @var string|null
     */
    private $mailerSMTPUsername;

    /**
     * @var string|null
     */
    private $mailerSMTPPassword;

    /**
     * @var string|null
     */
    private $mailerSMTPHost;

    /**
     * @var string|null
     */
    private $mailerSMTPPort;

    /**
     * @var string|null
     */
    private $mailerSMTPSecure;

    /**
     * @var string[]
     */
    private static $mailerSMTPSecureList = [
        'auto',
        'tls',
        'ssl',
        'none',
    ];

    /**
     * handleMailerDsn
     * @param FormInterface $form
     * @param Request $request
     * @param SecurityUser $user
     */
    public function handleMailerDsn(FormInterface $form, Request $request, SecurityUser $user)
    {
        $content = json_decode($request->getContent(), true);
        $form->submit($content);

        if ($form->isValid()) {
            $result = null;
            $config = ParameterFileManager::readParameterFile();
            switch ($content['enableMailerSMTP']) {
                case 'gmail':
                    $result = 'gmail://' . $content['mailerSMTPUsername'] . ':' . $content['mailerSMTPPassword'] . '@default';
                    break;
                case 'No':
                    break;
                default:
                    $result = 'smtp://' . $content['mailerSMTPUsername'] . ':' . $content['mailerSMTPPassword'] . '@' . $content['mailerSMTPHost'] . ':' . $content['mailerSMTPPort'] . '?encryption=' . $content['mailerSMTPSecure'];
            }

            $config['parameters']['mailer_dsn'] = $result;

            ParameterFileManager::writeParameterFile($config);
        }
    }

    /**
     * @return string|null
     */
    public function getEnableMailerSMTP(): ?string
    {
        return $this->enableMailerSMTP;
    }

    /**
     * EnableMailerSMTP.
     *
     * @param string|null $enableMailerSMTP
     * @return MailerSettingsManager
     */
    public function setEnableMailerSMTP(?string $enableMailerSMTP): MailerSettingsManager
    {
        $this->enableMailerSMTP = $enableMailerSMTP;
        return $this;
    }

    /**
     * @return string[]
     */
    public static function getEnableMailerSMTPList(): array
    {
        return self::$enableMailerSMTPList;
    }

    /**
     * @return string|null
     */
    public function getMailerSMTPUsername(): ?string
    {
        return $this->mailerSMTPUsername;
    }

    /**
     * MailerSMTPUsername.
     *
     * @param string|null $mailerSMTPUsername
     * @return MailerSettingsManager
     */
    public function setMailerSMTPUsername(?string $mailerSMTPUsername): MailerSettingsManager
    {
        $this->mailerSMTPUsername = $mailerSMTPUsername;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getMailerSMTPPassword(): ?string
    {
        return $this->mailerSMTPPassword;
    }

    /**
     * MailerSMTPPassword.
     *
     * @param string|null $mailerSMTPPassword
     * @return MailerSettingsManager
     */
    public function setMailerSMTPPassword(?string $mailerSMTPPassword): MailerSettingsManager
    {
        $this->mailerSMTPPassword = $mailerSMTPPassword;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getMailerSMTPHost(): ?string
    {
        return $this->mailerSMTPHost;
    }

    /**
     * MailerSMTPHost.
     *
     * @param string|null $mailerSMTPHost
     * @return MailerSettingsManager
     */
    public function setMailerSMTPHost(?string $mailerSMTPHost): MailerSettingsManager
    {
        $this->mailerSMTPHost = $mailerSMTPHost;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getMailerSMTPPort(): ?string
    {
        return $this->mailerSMTPPort;
    }

    /**
     * MailerSMTPPort.
     *
     * @param string|null $mailerSMTPPort
     * @return MailerSettingsManager
     */
    public function setMailerSMTPPort(?string $mailerSMTPPort): MailerSettingsManager
    {
        $this->mailerSMTPPort = $mailerSMTPPort;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getMailerSMTPSecure(): ?string
    {
        return $this->mailerSMTPSecure;
    }

    /**
     * MailerSMTPSecure.
     *
     * @param string|null $mailerSMTPSecure
     * @return MailerSettingsManager
     */
    public function setMailerSMTPSecure(?string $mailerSMTPSecure): MailerSettingsManager
    {
        $this->mailerSMTPSecure = $mailerSMTPSecure;
        return $this;
    }

    /**
     * @return string[]
     */
    public static function getMailerSMTPSecureList(): array
    {
        return self::$mailerSMTPSecureList;
    }

    /**
     * parseFromDsn
     * @param string|null $dsn
     * @return $this
     */
    public function parseFromDsn(?string $dsn): MailerSettingsManager
    {
        if ($dsn === null || $dsn === '') {
            return $this;
        }

        $dsn = Dsn::fromString($dsn);

        switch ($dsn->getScheme()) {
            case 'gmail':
                $this->setMailerSMTPUsername($dsn->getUser());
                $this->setMailerSMTPPassword($dsn->getPassword());
                $this->setEnableMailerSMTP('gmail');
                break;
            default:
                $this->setMailerSMTPUsername($dsn->getUser());
                $this->setMailerSMTPPassword($dsn->getPassword());
                $this->setEnableMailerSMTP('smtp');
                $this->setMailerSMTPHost($dsn->getHost());
                $this->setMailerSMTPPort($dsn->getPort());
                $this->setMailerSMTPSecure($dsn->getOption('encryption', 'auto'));
        }

        return $this;
    }
}