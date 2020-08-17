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

use App\Manager\StatusManager;
use App\Manager\ParameterFileManager;
use App\Modules\Security\Entity\SecurityUser;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\Transport\Dsn;

/**
 * Class MailerSettingsManager
 * @package App\Modules\System\Manager
 * @author Craig Rayner <craig@craigrayner.com>
 */
class MailerSettingsManager
{
    /**
     * @var string|null
     */
    private ?string $enableMailerSMTP;

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
     * @var StatusManager
     */
    private StatusManager $messages;

    /**
     * handleMailerDsn
     *
     * 16/08/2020 09:43
     * @param FormInterface $form
     * @param Request $request
     * @return bool
     */
    public function handleMailerDsn(FormInterface $form, Request $request): bool
    {
        $content = json_decode($request->getContent(), true);
        $form->submit($content);

        if ($form->isValid()) {
            $result = null;
            $config = ParameterFileManager::readParameterFile();
            switch ($this->getEnableMailerSMTP()) {
                case 'gmail':
                    $result = 'gmail://' . $this->getMailerSMTPUsername() . ':' . $this->getMailerSMTPPassword() . '@default';
                    break;
                case 'No':
                    $result = null;
                    break;
                default:
                    $result = 'smtp://' . $this->getMailerSMTPUsername() . ':' . $this->getMailerSMTPPassword() . '@' . $this->getMailerSMTPHost() . ':' . $this->getMailerSMTPPort() . '?encryption=' . $this->getMailerSMTPSecure();
            }

            $config['parameters']['mailer_dsn'] = $result;

            ParameterFileManager::writeParameterFile($config);
            $this->getMessages()->success();
            return true;
        }
        return false;
    }

    /**
     * getEnableMailerSMTP
     *
     * 16/08/2020 09:52
     * @return string|null
     */
    public function getEnableMailerSMTP(): ?string
    {
        return $this->enableMailerSMTP = $this->enableMailerSMTP = in_array($this->enableMailerSMTP, self::getEnableMailerSMTPList()) ? $this->enableMailerSMTP : 'No';
    }

    /**
     * setEnableMailerSMTP
     *
     * 16/08/2020 09:51
     * @param string|null $enableMailerSMTP
     * @return $this
     */
    public function setEnableMailerSMTP(?string $enableMailerSMTP): MailerSettingsManager
    {
        $this->enableMailerSMTP = in_array($enableMailerSMTP, self::getEnableMailerSMTPList()) ? $enableMailerSMTP : 'No';
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

    /**
     * @return StatusManager
     */
    public function getMessages(): StatusManager
    {
        return $this->messages;
    }

    /**
     * setMessages
     *
     * 16/08/2020 09:58
     * @param StatusManager $messages
     * @return MailerSettingsManager
     */
    public function setMessages(StatusManager $messages): MailerSettingsManager
    {
        $this->messages = $messages;
        return $this;
    }

}
