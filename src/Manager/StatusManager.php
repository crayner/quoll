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
 * Date: 15/08/2020
 * Time: 14:05
 */
namespace App\Manager;

use App\Manager\Hidden\Message;
use Doctrine\Common\Collections\ArrayCollection;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class StatusManager
{
    const INVALID_INPUTS = 'return.error.1';

    const NO_ACCESS = 'return.error.0';

    const DATABASE_ERROR = 'return.error.2';

    const SUCCESS = 'return.success.0';

    const INVALID_TOKEN = 'return.error.csrf';

    const FILE_TRANSFER = 'return.error.file_transfer';

    const LOCKED_RECORD = 'return.warning.3';

    const NOTHING_TO_DO = 'return.warning.4';

    /**
     *
    $returns['success0'] = __('Your request was completed successfully.'); return.success.0
    $returns['error0'] = __('Your request failed because you do not have access to this action.'); return.error.0
    $returns['error1'] = __('Your request failed because your inputs were invalid.'); return.error.1
    $returns['error2'] = __('Your request failed due to a database error.'); return.error.2
    $returns['error3'] = __('Your request failed because your inputs were invalid.'); return.error.3
    $returns['error4'] = __('Your request failed because your passwords did not match.'); return.error.4
    $returns['error5'] = __('Your request failed because there are no records to show.'); return.error.5
    $returns['error6'] = __('Your request was completed successfully, but there was a problem saving some uploaded files.'); return.error.6
    $returns['error7'] = __('Your request failed because some required values were not unique.'); return.error.7
    $returns['error8'] = __('Your request failed because some values are still in use within the data.'); return.error.8
    $returns['warning0'] = __('Your optional extra data failed to save.'); return.warning.0
    $returns['warning1'] = __('Your request was successful, but some data was not properly saved.'); return.warning.1
    $returns['warning2'] = __('Your request was successful, but some data was not properly deleted.'); return.warning.2
    $returns['warning3'] = __('The record "{id}" is locked and will not be deleted from class "{class}".'); return.warning.3
    $returns['warning4'] = __('Your request did not require any action.'); return.warning.4

     */

    /**
     * @var string
     */
    private string $status = 'default';

    /**
     * @var ArrayCollection
     */
    private ArrayCollection $messages;

    /**
     * @var string
     */
    private string $domain = 'messages';

    /**
     * @var FlashBagInterface
     */
    private FlashBagInterface $flashBag;

    /**
     * @var LoggerInterface|null
     */
    private ?LoggerInterface $logger = null;

    /**
     * @var string
     */
    private string $reDirect = '';

    /**
     * MessageStatusManager constructor.
     * @param SessionInterface $session
     */
    public function __construct(SessionInterface $session)
    {
        $this->flashBag = $session->getFlashBag();
        $this->resetStatus();

    }

    /**
     * @return string
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * @param string $status
     * @return StatusManager
     */
    public function setStatus(string $status): StatusManager
    {
        if ((in_array($status, self::getStatusList()) && array_search($status, self::getStatusList()) > array_search($this->status, self::getStatusList())) || $status === 'redirect') {
            $this->status = $status;
        }
        return $this;
    }

    /**
     * count
     * @return mixed
     * 15/08/2020 14:13
     */
    public function count()
    {
        return $this->getMessages()->count();
    }

    /**
     * @return ArrayCollection
     */
    public function getMessages(): ArrayCollection
    {
        if (null === $this->messages) $this->messages = new ArrayCollection();

        return $this->messages;
    }

    /**
     * @param ArrayCollection $messages
     * @return StatusManager
     */
    public function setMessages(ArrayCollection $messages): StatusManager
    {
        $this->messages = $messages;
        return $this;
    }

    /**
     * addMessage
     * @param string $status
     * @param string $id
     * @param array $parameters
     * @param string|null $domain
     * @return StatusManager
     * 15/08/2020 14:33
     */
    public function addMessage(string $status, string $id, array $parameters = [], ?string $domain = null)
    {
        $this->setStatus($status);
        $message = new Message($status, $id, $parameters,$domain ?: $this->getDomain());

        $this->getMessages()->set($id, $message);

        return $this->logMessage($message);
    }

    /**
     * critical
     * @param string $id
     * @param array $parameters
     * @param string|null $domain
     * @return $this
     * 15/08/2020 16:18
     */
    public function critical(string $id, array $parameters = [], ?string $domain = null)
    {
        return $this->addMessage('critical', $id, $parameters, $domain);
    }

    /**
     * danger
     * @param string $id
     * @param array $parameters
     * @param string|null $domain
     * 15/08/2020 14:41
     */
    public function danger(string $id, array $parameters = [], ?string $domain = null)
    {
        return $this->addMessage('danger', $id, $parameters, $domain);
    }

    /**
     * warning
     *
     * 16/08/2020 12:55
     * @param string $id
     * @param array $parameters
     * @param string|null $domain
     * @return $this
     */
    public function warning(string $id, array $parameters = [], ?string $domain = null)
    {
        if (strpos($id, 'return.warning') === 0) {
            $domain = 'messages';
        }
        return $this->addMessage('warning', $id, $parameters, $domain);
    }

    /**
     * info
     *
     * 16/08/2020 12:55
     * @param string $id
     * @param array $parameters
     * @param string|null $domain
     * @return $this
     */
    public function info(string $id, array $parameters = [], ?string $domain = null)
    {
        return $this->addMessage('info', $id, $parameters, $domain);
    }

    /**
     * primary
     * @param string $id
     * @param array $parameters
     * @param string|null $domain
     * 15/08/2020 14:41
     */
    public function primary(string $id, array $parameters = [], ?string $domain = null)
    {
        return $this->addMessage('primary', $id, $parameters, $domain);
    }

    /**
     * secondary
     * @param string $id
     * @param array $parameters
     * @param string|null $domain
     * 15/08/2020 14:41
     */
    public function secondary(string $id, array $parameters = [], ?string $domain = null)
    {
        return $this->addMessage('secondary', $id, $parameters, $domain);
    }

    /**
     * dark
     * @param string $id
     * @param array $parameters
     * @param string|null $domain
     * 15/08/2020 14:42
     */
    public function dark(string $id, array $parameters = [], ?string $domain = null)
    {
        return $this->addMessage('dark', $id, $parameters, $domain);
    }

    /**
     * light
     * @param string $id
     * @param array $parameters
     * @param string|null $domain
     * 15/08/2020 14:42
     */
    public function light(string $id, array $parameters = [], ?string $domain = null)
    {
        return $this->addMessage('light', $id, $parameters, $domain);
    }

    /**
     * success
     *
     * 16/08/2020 12:55
     * @param string|null $id
     * @param array $parameters
     * @param string|null $domain
     * @return StatusManager
     */
    public function success(?string $id = null, array $parameters = [], ?string $domain = null): StatusManager
    {
        if (null === $id) {
            $id = self::SUCCESS;
            $domain = 'messages';
        }
        return $this->addMessage('success', $id, $parameters, $domain);
    }

    /**
     * error
     *
     * 16/08/2020 12:55
     * @param string $id
     * @param array $parameters
     * @param string|null $domain
     * @return $this
     */
    public function error(string $id, array $parameters = [], ?string $domain = null)
    {
        if (strpos($id, 'return.error') === 0) {
            $domain = 'messages';
        }
        return $this->addMessage('error', $id, $parameters, $domain);
    }

    /**
     * @return string
     */
    public function getDomain(): string
    {
        return $this->domain;
    }

    /**
     * @param string $domain
     * @return StatusManager
     */
    public function setDomain(string $domain): StatusManager
    {
        $this->domain = $domain;
        return $this;
    }

    /**
     * getStatusList
     * @return array
     * 15/08/2020 14:37
     */
    public static function getStatusList(): array
    {
        return array_flip(MessageManager::getStatusLevel());
    }

    /**
     * toArray
     *
     * 17/08/2020 11:34
     * @param array|null $formView
     * @param array $data
     * @return array
     */
    public function toArray(?array $formView = null, array $data = []): array
    {
        if (null !== $formView) {
            $data['form'] = $formView;
        }
        $data['status'] = $this->getStatus();
        $data['redirect'] = $this->getReDirect();
        $data['errors'] = $this->getMessageArray();
        return $data;
    }

    /**
     * getMessageArray
     *
     * 17/08/2020 11:33
     * @return array
     */
    public function getMessageArray(): array
    {
        $data = [];
        foreach ($this->getMessages() as $message) {
            $data[] = ['class' => $message->getLevel(), 'message' => $message->getTranslatedMessage()];
        }
        return $data;
    }

    /**
     * @return FlashBagInterface
     */
    public function getFlashBag(): FlashBagInterface
    {
        return $this->flashBag;
    }

    /**
     * @return LoggerInterface|null
     */
    public function getLogger(): ?LoggerInterface
    {
        return $this->logger;
    }

    /**
     * @param LoggerInterface|null $logger
     * @return StatusManager
     */
    public function setLogger(?LoggerInterface $logger): StatusManager
    {
        $this->logger = $logger;
        return $this;
    }

    /**
     * convertToFlash
     *
     * 16/08/2020 12:57
     */
    public function convertToFlash()
    {
        foreach ($this->getMessages() as $message) {
            $this->getFlashBag()->add($message->getLevel(), [$message->getMessage(), $message->getOptions(), $message->getDomain()]);
        }
    }

    /**
     * logMessage
     *
     * 16/08/2020 12:57
     * @param Message $message
     * @return StatusManager
     */
    public function logMessage(Message $message): StatusManager
    {
        if (null !== $this->getLogger()) {
            $mapping = [
                'default'   => 'debug',
                'light'     => 'debug',
                'dark'      => 'debug',
                'secondary' => 'notice',
                'primary'   => 'notice',
                'info'      => 'info',
                'success'   => 'debug',
                'warning'   => 'warning',
                'danger'    => 'emergency',
                'error'     => 'error',
                'critical'  => 'critical',
            ];
            $map = $mapping[$message->getLevel()];
            $this->getLogger()->$map($message->getTranslatedMessage());
        }

        return $this->setLogger(null);
    }

    /**
     * toJsonResponse
     *
     * 16/08/2020 15:11
     * @param array $data
     * @return JsonResponse
     */
    public function toJsonResponse(array $data = []): JsonResponse
    {
        return new JsonResponse(array_merge($data, $this->toArray()));
    }

    /**
     * isStatusSuccess
     *
     * 17/08/2020 10:45
     * @return bool
     */
    public function isStatusSuccess(): bool
    {
        return $this->getStatus() === 'success' || $this->getStatus() === 'redirect' || $this->getMessages()->count() === 0;
    }

    /**
     * getReDirect
     *
     * 17/08/2020 11:24
     * @return string
     */
    public function getReDirect(): string
    {
        return $this->reDirect;
    }

    /**
     * setReDirect
     *
     * 24/08/2020 14:03
     * @param string $reDirect
     * @param bool $convertToFlash
     * @return StatusManager
     */
    public function setReDirect(string $reDirect, bool $convertToFlash = false): StatusManager
    {
        $this->reDirect = $reDirect;

        if ($convertToFlash) $this->convertToFlash();
        return $this->setStatus('redirect');
    }

    /**
     * invalidInputs
     *
     * 28/10/2020 16:56
     * @return $this
     */
    public function invalidInputs(): StatusManager
    {
        $this->error(static::INVALID_INPUTS);
        return $this;
    }

    /**
     * databaseError
     *
     * 21/08/2020 07:59
     */
    public function databaseError()
    {
        $this->error(static::DATABASE_ERROR);
    }

    /**
     * getLastMessage
     *
     * 26/08/2020 13:39
     * @return Message|bool
     */
    public function getLastMessage()
    {
        return $this->getMessages()->last();
    }

    /**
     * getLastMessageTranslated
     *
     * 26/08/2020 13:40
     * @return string
     */
    public function getLastMessageTranslated(): string
    {
        return $this->getLastMessage() ? $this->getLastMessage()->getTranslatedMessage(): '';
    }

    /**
     * getFirstMessage
     *
     * 26/08/2020 15:51
     * @return Message|bool
     */
    public function getFirstMessage()
    {
        return $this->getMessages()->first();
    }

    /**
     * getFirstMessageTranslated
     *
     * 26/08/2020 15:51
     * @return string
     */
    public function getFirstMessageTranslated(): string
    {
        return $this->getFirstMessage() ? $this->getFirstMessage()->getTranslatedMessage(): '';
    }

    /**
     * resetStatus
     *
     * 3/09/2020 14:50
     * @return $this
     */
    public function resetStatus(): StatusManager
    {
        $this->setStatus('default')
            ->setMessages(new ArrayCollection());
        return $this;
    }
}
