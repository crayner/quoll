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
 * Date: 25/04/2020
 * Time: 09:01
 */

namespace App\Listeners;

use App\Util\ErrorMessageHelper;
use PhpParser\Node\Stmt\Else_;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Class ParamConverterListener
 * @package App\Listeners
 */
class ParamConverterListener implements EventSubscriberInterface
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * ParamConverterListener constructor.
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * getSubscribedEvents
     * @return array|\array[][]
     */
    public static function getSubscribedEvents()
    {
        // return the subscribed events, their methods and priorities
        return [
            KernelEvents::EXCEPTION => [
                ['processException', 0],
            ],
        ];
    }

    /**
     * processException
     * @param ExceptionEvent $event
     * @return void
     */
    public function processException(ExceptionEvent $event)
    {
        if (!$event->getThrowable() instanceof NotFoundHttpException)
            return;

        $exception = $event->getThrowable();

        if (strpos($exception->getMessage(),'@ParamConverter') === false)
            return;

        $response = new RedirectResponse('/');

        if ($event->getRequest()->server->get('APP_ENV') === 'dev')
            $event->getRequest()->getSession()->getFlashbag()->add('error', $exception->getMessage());
        else
            $event->getRequest()->getSession()->getFlashbag()->add('error', ErrorMessageHelper::onlyInvalidInputsMessage());

        $this->logger->error($exception->getMessage());

        $event->setResponse($response);
    }
}