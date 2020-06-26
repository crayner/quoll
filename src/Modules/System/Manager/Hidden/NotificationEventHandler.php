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
 * Date: 29/03/2020
 * Time: 08:37
 */

namespace App\Modules\System\Manager\Hidden;

use App\Modules\Comms\Entity\NotificationEvent;
use App\Modules\Comms\Entity\NotificationListener;
use App\Modules\Comms\Validator\EventListener;
use App\Modules\People\Entity\Person;
use App\Provider\ProviderFactory;
use App\Util\ErrorMessageHelper;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\DBAL\Driver\PDOException;
use Doctrine\DBAL\Exception\NotNullConstraintViolationException;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validation;

/**
 * Class NotificationEventHandler
 * @package App\Modules\System\Manager\Hidden
 */
class NotificationEventHandler
{
    /**
     * handleRequest
     * @param Request $request
     * @param Form $form
     * @param NotificationEvent $event
     * @return array
     */
    public function handleRequest(Request $request, Form $form, NotificationEvent $event): array
    {
        $content = json_decode($request->getContent(), true);
        $em = ProviderFactory::getEntityManager();

        foreach ($content['listeners'] as $q => $w) {
            $content['listeners'][$q]['event'] = $event->getId();
        }

        $validator = Validation::createValidator();
        $violations = new ConstraintViolationList();

        $listeners = new ArrayCollection();
        try {
            foreach ($content['listeners'] as $q => $w) {
                $w['id'] = array_key_exists('id', $w) ? $w['id'] : null;
                $listener = ProviderFactory::getRepository(NotificationListener::class)->find($w['id']) ?: new NotificationListener();
                $person = ProviderFactory::getRepository(Person::class)->find($w['person']);
                if (count($event->getScopes()) === 1) {
                    $w['scopeType'] = $event->getScopes()[0];
                }
                $listener->setPerson($person)
                    ->setScopeType($w['scopeType'])
                    ->setScopeIdentifier($w['scopeType'] === 'All' ? null : ($w['scopeIdentifier'] ?: ''))
                    ->setEvent($event);
                $lv = $validator->validate($listener, [new EventListener()]);
                if ($lv->count() > 0) {
                    $form->get('listeners')->get($q)->get('person')->addError(new FormError($lv->get(0)->getMessage()));
                }
                $violations->addAll($lv);
                $listener = clone $listener;
                $listeners->add($listener);
            }

            if ($violations->count() === 0) {
                $listeners = $event->sortListeners($listeners)->getListeners();
                $all = [];
                foreach($listeners as $listener) {
                    if ($listener->getScopeType() === 'All') {
                        $all[] = $listener->getPerson()->getId();
                    }
                    if ($listener->getScopeType() !== 'All' && in_array($listener->getPerson()->getId(), $all)) {
                        $listeners->removeElement($listener);
                    }
                }

                ProviderFactory::getRepository(NotificationListener::class)->deleteAllForEvent($event);
                foreach($listeners as $listener) {
                    $em->persist($listener);
                }
                $event->setActive($content['active']);
                $em->persist($event);
                $em->flush();

                $data = ErrorMessageHelper::getSuccessMessage([], true);
            } else {
                $data = ErrorMessageHelper::getInvalidInputsMessage([], true);
            }
        } catch (PDOException | \PDOException | NotNullConstraintViolationException $e) {
            $data = ErrorMessageHelper::getDatabaseErrorMessage([], true);
        }

        return $data;
    }
}