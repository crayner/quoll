<?php
/**
 * Created by PhpStorm.
 *
* Quoll
 * (c) 2019 Craig Rayner <craig@craigrayner.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * User: craig
 * Date: 8/08/2019
 * Time: 12:56
 */

namespace App\Modules\Comms\Provider;

use App\Modules\People\Entity\Person;
use App\Mailer\NotificationMailer;
use App\Provider\AbstractProvider;
use App\Provider\ProviderFactory;
use App\Modules\Comms\Entity\Module;
use App\Modules\Comms\Entity\Notification;
use App\Modules\Comms\Entity\NotificationEvent;
use App\Modules\Comms\Entity\NotificationListener;
use Symfony\Component\Mailer\MailerInterface;

/**
 * Class NotificationEventProvider
 * @package App\Modules\Comms\Provider
 */
class NotificationEventProvider extends AbstractProvider
{

    /**
     * @var null|MailerInterface
     */
    private $sender;

    /**
     * @var null|Module
     */
    private $module;

    /**
     * @var string
     */
    protected $entityName = NotificationEvent::class;

    /**
     * createEvent
     * @param string $moduleName
     * @param string $event
     * @return NotificationEventProvider
     */
    public function createEvent(string $moduleName, string $event): NotificationEventProvider
    {
        $notificationEvent = $this->findOneBy(['moduleName' => $moduleName, 'event' => $event]);
        $this->module = ProviderFactory::getRepository(Module::class)->findOneByName($moduleName);
        $this->setEntity($notificationEvent);
        return $this;
    }

    /**
     * Collects and sends all notifications for this event, returning a send report array.
     *
     * @param   bool        $bccMode
     * @return  array Send report with success/fail counts.
     */
    public function sendNotifications($bccMode = false)
    {
        $provider = ProviderFactory::create(Notification::class);

        $this->pushNotifications($provider);
    }

    /**
     * @return array
     */
    public function getNotificationTextOptions(): array
    {
        return $this->notificationTextOptions = $this->notificationTextOptions ?: [];
    }

    /**
     * NotificationTextOptions.
     *
     * @param array $notificationTextOptions
     * @return NotificationEventProvider
     */
    public function setNotificationTextOptions(array $notificationTextOptions): NotificationEventProvider
    {
        $this->notificationTextOptions = $notificationTextOptions;
        return $this;
    }

    /**
     * @var array
     */
    private $recipients = [];

    /**
     * @return array|Person[]
     */
    public function getRecipients(): array
    {
        return $this->recipients = $this->recipients ?: [];
    }

    /**
     * Recipients.
     *
     * @param array|Person[] $recipients
     * @return NotificationEvent
     */
    public function setRecipients(array $recipients): NotificationEvent
    {
        $this->recipients = $recipients;
        return $this;
    }


    /**
     * addRecipient
     * Adds a recipient to the list. Avoids duplicates by checking presence in the the array.
     *
     * @param Person|int $person
     * @return NotificationEventProvider
     */
    public function addRecipient($person): NotificationEventProvider
    {
        if (is_int($person))
            $person = ProviderFactory::getRepository(Person::class)->find($person);

        if (!$person instanceof Person)
            return $this;

        if (!in_array($person->getId(), $this->getRecipients()))
            $this->recipients[$person->getId()] = $person;

        return $this;
    }

    /**
     * @var string|null
     */
    private $notificationText;

    /**
     * @var array
     */
    private $notificationTextOptions;

    /**
     * @return string|null
     */
    public function getNotificationText(): ?string
    {
        return $this->notificationText;
    }

    /**
     * NotificationText.
     *
     * @param string|null $notificationText
     * @return NotificationEventProvider
     */
    public function setNotificationText(?string $notificationText): NotificationEventProvider
    {
        $this->notificationText = $notificationText;
        return $this;
    }

    /**
     * @var string|null
     */
    private $actionLink;

    /**
     * @return string|null
     */
    private function getActionLink(): ?string
    {
        return $this->actionLink;
    }

    /**
     * setActionLink
     * @param string $actionLink
     * @return NotificationEventProvider
     */
    public function setActionLink(string $actionLink): NotificationEventProvider
    {
        $this->actionLink = $actionLink;
        return $this;
    }

    /**
     * @return MailerInterface
     */
    public function getSender(): MailerInterface
    {
        return $this->sender;
    }

    /**
     * Sender.
     *
     * @param NotificationMailer $sender
     * @return NotificationEventProvider
     */
    public function setSender(NotificationMailer $sender): NotificationEventProvider
    {
        $this->sender = $sender;
        return $this;
    }

    /**
     * Adds event listeners to the recipients list, then pushes a notification for each recipient to the notification sender.
     * Does not perform the sending of notifications (can be used for bulk processing).
     *
     * @param   NotificationProvider  $provider
     * @param   NotificationMailer   $sender
     * @return  int|bool Final recipient count, false on failure
     */
    public function pushNotifications(NotificationProvider $provider)
    {
        $eventDetails = $this->getEntity();

        if (null === $this->getEntity() || $this->getEntity()->getActive() === 'N') {
            return false;
        }

        $this->addEventListeners();

        if ($this->getRecipientCount() == 0) {
            return false;
        }

        foreach ($this->recipients as $person) {
            $this->writeNotification($person);
        }

        return $this->getRecipientCount();
    }

    /**
     * Finds all listeners in the database for this event and adds them as recipients. The returned set
     * of listeners are filtered by the event scopes.
     *
     * @return int
     */
    protected function addEventListeners()
    {
        $result = ProviderFactory::getRepository(NotificationListener::class)->selectNotificationListenersByScope($this->getEntity(), []);

        if (count($result) > 0) {
            foreach($result as $person) {
                $this->addRecipient($person);
            }
        }

        return count($result);
    }

    /**
     * Gets the current recipient count for this event. If called after pushNotifications() it will all include listener count.
     *
     * @return  int
     */
    public function getRecipientCount()
    {
        return count($this->getRecipients());
    }

    /**
     * getModuleName
     * @return string
     */
    private function getModuleName(): string
    {
        return $this->getEntity()->getName();
    }

    /**
     * writeNotification
     * @param Person $person
     */
    public function writeNotification(Person $person)
    {
        $notification = new Notification();
        $provider = ProviderFactory::create(Notification::class);
        $provider->setEntity($notification);
        $notification->setText($this->sender->translate($this->getNotificationText(), $this->getNotificationTextOptions()))
            ->setActionLink($this->getActionLink())
            ->setPerson($person)
            ->setModule($this->module)
        ;
        $provider->saveEntity();
        $this->getSender()->newRegistration($person, $this->getNotificationText(), $this->getNotificationTextOptions(), $this->getModuleName(), $this->getActionLink(), $notification);
    }

    /**
     * selectAllNotificationEvents
     * @return array
     * @throws \Exception
     */
    public function selectAllNotificationEvents(): array
    {
        return $this->getRepository()->findAllNotificationEvents();
    }
}