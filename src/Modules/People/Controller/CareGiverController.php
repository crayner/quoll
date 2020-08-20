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
 * Date: 21/07/2020
 * Time: 10:39
 */
namespace App\Modules\People\Controller;

use App\Container\ContainerManager;
use App\Manager\StatusManager;
use App\Modules\People\Entity\Contact;
use App\Modules\People\Entity\CareGiver;
use App\Modules\People\Entity\Person;
use App\Modules\People\Entity\PersonalDocumentation;
use App\Modules\People\Form\CareGiverType;
use App\Provider\ProviderFactory;
use App\Util\ErrorMessageHelper;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class CareGiverController
 * @package App\Modules\People\Controller
 * @author Craig Rayner <craig@craigrayner.com>
 */
class CareGiverController extends PeopleController
{
    /**
     * editCareGiver
     *
     * 20/08/2020 09:06
     * @param CareGiver $careGiver
     * @Route("/care/giver/{careGiver}/edit/",name="care_giver_edit")
     * @IsGranted("ROLE_ROUTE")
     * @return JsonResponse
     */
    public function editCareGiver(CareGiver $careGiver)
    {
        if ($this->getRequest()->getContentType() === 'json') {

            $form = $this->createCareGiverForm($careGiver);

            return $this->saveCareGiverContent($form, $careGiver);
        } else {
            $form = $this->createCareGiverForm($careGiver);
            $this->getStatusManager()->error(StatusManager::INVALID_INPUTS);
            return $this->generateJsonResponse(
                [
                    'form' => $this->getContainerManager()
                        ->singlePanel($form->createView())
                        ->getFormFromContainer(),
                ]
            );
        }
    }

    /**
     * createCareGiverForm
     *
     * 20/08/2020 08:46
     * @param CareGiver $careGiver
     * @return FormInterface
     */
    private function createCareGiverForm(CareGiver $careGiver): FormInterface
    {
        return $this->createForm(CareGiverType::class, $careGiver,
            [
                'action' => $this->generateUrl('care_giver_edit', ['careGiver' => $careGiver->getId()]),
            ]
        );
    }

    /**
     * saveCareGiverContent
     *
     * 19/08/2020 12:42
     * @param FormInterface $form
     * @param CareGiver $careGiver
     * @return JsonResponse
     */
    private function saveCareGiverContent(FormInterface $form, CareGiver $careGiver)
    {
        $content = json_decode($this->getRequest()->getContent(), true);

        $form->submit($content);
        if ($form->isValid()) {
            ProviderFactory::create(CareGiver::class)->persistFlush($careGiver);
            if ($this->isStatusSuccess()) {
                $form = $this->createCareGiverForm($careGiver);
            }
        } else {
            $this->getStatusManager()->error(StatusManager::INVALID_INPUTS);
        }
        return $this->singleForm($form);
    }

    /**
     * addToCareGiver
     *
     * 19/08/2020 12:44
     * @param Person $person
     * @Route("/care/giver/{person}/add/",name="care_giver_add")
     * @IsGranted("ROLE_ROUTE")
     * @return JsonResponse
     */
    public function addToCareGiver(Person $person)
    {
        if (null === $person->getCareGiver()) {
            new CareGiver($person);
            if ($person->getPersonalDocumentation() === null) {
                new PersonalDocumentation($person);
            }
            if ($person->getContact() === null) {
                new Contact($person);
            }
            $person->getSecurityUser()->addSecurityRole('ROLE_CARE_GIVER');
            ProviderFactory::create(Person::class)->persistFlush($person);
            if ($this->isStatusSuccess()) {
                $this->getStatusManager()
                    ->setReDirect($this->generateUrl('person_edit', ['person' => $person->getId(), 'tabName' => 'Care Giver']))
                    ->convertToFlash();
            }
        } else {
            $this->getStatusManager()->warning(StatusManager::NOTHING_TO_DO);
            $this->getStatusManager()
                ->setReDirect($this->generateUrl('person_edit', ['person' => $person->getId(), 'tabName' => 'Care Giver']))
                ->convertToFlash();
        }

        return $this->generateJsonResponse();
    }
}
