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
 * Date: 25/10/2019
 * Time: 16:01
 */

namespace App\Modules\System\Manager;

use App\Modules\Comms\Entity\Notification;
use App\Modules\People\Entity\Person;
use App\Modules\System\Entity\Module;
use App\Provider\ProviderFactory;
use Doctrine\Common\Collections\ArrayCollection;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

/**
 * Class NotificationSender
 * @package App\Modules\System\Manager
 */
class NotificationSender
{
    /**
     * @var Environment
     */
    private $twig;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var ArrayCollection
     */
    private $events;

    /**
     * @var SessionInterface
     */
    private $session;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var MailerInterface
     */
    private $mailer;

    /**
     * NotificationSender constructor.
     * @param SessionInterface $session
     * @param Environment $twig
     * @param TranslatorInterface $translator
     * @param LoggerInterface $logger
     * @param MailerInterface $mailer
     */
    public function __construct(SessionInterface $session, Environment $twig, TranslatorInterface $translator, LoggerInterface $logger, MailerInterface $mailer)
    {
        $this->session = $session;
        $this->twig = $twig;
        $this->translator = $translator;
        $this->logger = $logger;


        $this->mailer = $mailer;
    }

    /**
     * @return ArrayCollection
     */
    public function getEvents(): ArrayCollection
    {
        if ($this->events === null && $this->getSession()->has('Notification Events')) {
            $this->events = $this->getSession()->remove('Notification Events');
        }
        return $this->events = $this->events ?: new ArrayCollection();
    }

    /**
     * addEvent
     * @param EventBuilder $event
     * @return NotificationSender
     */
    public function addEvent(EventBuilder $event): NotificationSender
    {
        if ($this->getEvents()->contains($event))
            return $this;

        $this->events->add($event);
        $this->getSession()->set('Notification Events', $this->events);
        return $this;
    }

    /**
     * Events.
     *
     * @param ArrayCollection $events
     * @return NotificationSender
     */
    public function setEvents(ArrayCollection $events): NotificationSender
    {
        $this->events = $events;
        return $this;
    }

    /**
     * hasEvents
     * @return bool
     */
    public function hasEvents(): bool
    {
        return $this->getEvents()->count() > 0;
    }

    /**
     * @return SessionInterface
     */
    public function getSession(): SessionInterface
    {
        return $this->session;
    }

    /**
     * @return Environment
     */
    public function getTwig(): Environment
    {
        return $this->twig;
    }

    /**
     * @return TranslatorInterface
     */
    public function getTranslator(): TranslatorInterface
    {
        return $this->translator;
    }

    /**
     * renderEvents
     * @throws \Symfony\Component\Mailer\Exception\TransportExceptionInterface
     */
    public function renderEvents()
    {
        foreach($this->getEvents() as $event) {

            $this->manageEventListeners($event);

            foreach($event->getRecipients() as $recipient) {
                $notif = new Notification();
                $text = $this->getText($event);
                $notif->setPerson(ProviderFactory::getRepository(Person::class)->find($recipient->getId()))
                    ->setModule(ProviderFactory::getRepository(Module::class)->find($event->getModule()->getId()))
                    ->setStatus('New')
                    ->setActionLink($event->getActionLink())
                    ->setText($text)
                    ->setTextOptions(array_merge($event->getTextParams(), ['translation_domain' => $event->getTranslationDomain()]))
                ;
                $em = ProviderFactory::getEntityManager();
                $em->persist($notif);
                $em->flush();

                if ($recipient->isReceiveNotificationEmails()) {
                    $title = $this->translate('Notification') . ' - ' . $this->translate($notif->getModule()->getName()) . ': '
                        . $this->translate($event->getEvent()->getEvent(), [], $event->getTranslationDomain());
                    $email = (new TemplatedEmail())
                        ->from(new NamedAddress($event->getOption('fromAddress'), $event->getOption('fromName')))
                        ->to(new NamedAddress($recipient->getEmail(), $recipient->formatName()))
                        //->cc('cc@example.com')
                        //->bcc('bcc@example.com')
                        //->replyTo('fabien@example.com')
                        //->priority(Email::PRIORITY_HIGH)
                        ->subject($title)
                        // path of the Twig template to render
                        ->htmlTemplate('@KookaburraSystemAdmin/email/notification.html.twig')

                        // pass variables (name => value) to the template
                        ->context([
                            'title'  => $title,
                            'body'   => $notif->getText(),
                            'button' => [
                                'route'  => 'notification_action',
                                'routeOptions' => ['notification' => $notif->getId() ?: 0],
                                'text' => 'View Details',
                            ],
                        ]);

                    $this->getMailer()->send($email);

                }
            }
        }
        $this->getSession()->remove('Notification Events');
    }

    /**
     * manageEventListeners
     * @param EventBuilder $event
     */
    private function manageEventListeners(EventBuilder $eventBuilder)
    {
        foreach($eventBuilder->getEvent()->getListeners() as $listener) {
            if (in_array($listener->getScopeType(), $eventBuilder->getEvent()->getScopes()) || null === $listener->getScopeType())
            {
                $eventBuilder->addRecipient($listener->getPerson());
            }
        }

    }

    /**
     * @return LoggerInterface
     */
    private function getLogger(): LoggerInterface
    {
        return $this->logger;
    }

    /**
     * getText
     * @param EventBuilder $eventBuilder
     * @return string
     */
    private function getText(EventBuilder $eventBuilder)
    {
        if ($this->getTwig()->getLoader()->exists($eventBuilder->getText()))
        {
            return $this->getTwig()->render($eventBuilder->getText(), array_merge($eventBuilder->getTextParams(), ['translation_domain' => $eventBuilder->getTranslationDomain()]));
        }

        return str_replace(array_keys($eventBuilder->getTextParams()), array_values($eventBuilder->getTextParams()), $eventBuilder->getText());
    }

    /**
     * @return MailerInterface
     */
    private function getMailer(): MailerInterface
    {
        return $this->mailer;
    }

    /**
     * translate
     * @param string $id
     * @param array $options
     * @param string $domain
     */
    private function translate(string $id, array $options = [], string $domain = 'messages')
    {
        return $this->getTranslator()->trans($id,$options,$domain);
    }
}