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
     * @param ContainerManager $manager
     * @param CareGiver $careGiver
     * @return JsonResponse
     * 20/07/2020 11:27
     * @Route("/care/giver/{careGiver}/edit/",name="care_giver_edit")
     * @IsGranted("ROLE_ROUTE")
     */
    public function editCareGiver(ContainerManager $manager, CareGiver $careGiver)
    {
        if ($this->getRequest()->getContentType() === 'json') {

            $form = $this->createCareGiverForm($careGiver);

            return $this->saveCareGiverContent($form, $manager, $careGiver);
        } else {
            $form = $this->createCareGiverForm($careGiver);
            $data = ErrorMessageHelper::getInvalidInputsMessage([], true);
            $manager->singlePanel($form->createView());
            $data['form'] = $manager->getFormFromContainer();
            return new JsonResponse($data);
        }
    }

    /**
     * createCareGiverForm
     * @param CareGiver $careGiver
     * @return FormInterface
     * 20/07/2020 11:29
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
     * saveContent
     * @param FormInterface $form
     * @param ContainerManager $manager
     * @param CareGiver $careGiver
     * @return JsonResponse
     * 20/07/2020 11:31
     */
    private function saveCareGiverContent(FormInterface $form, ContainerManager $manager, CareGiver $careGiver)
    {
        $content = json_decode($this->getRequest()->getContent(), true);

        $form->submit($content);
        $data = [];
        if ($form->isValid()) {
            $data = ProviderFactory::create(CareGiver::class)->persistFlush($careGiver, $data);
//            $data = ProviderFactory::create(Person::class)->persistFlush($careGiver->getPerson(), $data);
            if ($data['status'] !== 'success') {
                $data = ErrorMessageHelper::getDatabaseErrorMessage($data, true);
                $manager->singlePanel($form->createView());
                $data['form'] = $manager->getFormFromContainer();
                return new JsonResponse($data);
            } else {
                $form = $this->createCareGiverForm($careGiver);
            }
            $manager->singlePanel($form->createView());
            $data['form'] = $manager->getFormFromContainer();
        } else {
            $data = ErrorMessageHelper::getInvalidInputsMessage($data, true);
            $manager->singlePanel($form->createView());
            $data['form'] = $manager->getFormFromContainer();
        }
        return new JsonResponse($data);
    }

    /**
     * addToCareGiver
     * @param Person $person
     * @Route("/care/giver/{person}/add/",name="care_giver_add")
     * @IsGranted("ROLE_ROUTE")
     * @return JsonResponse
     * 21/07/2020 10:47
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
            $data = ProviderFactory::create(Person::class)->persistFlush($person, []);
            if ($data['status'] === 'success') {
                $data['status'] = 'redirect';
                $data['redirect'] = $this->generateUrl('person_edit', ['person' => $person->getId(), 'tabName' => 'Care Giver']);
            }
        } else {
            $data = [];
            $data['status'] = 'redirect';
            $data['redirect'] = $this->generateUrl('person_edit', ['person' => $person->getId(), 'tabName' => 'Care Giver']);
            $this->addFlash('warning', ErrorMessageHelper::onlyNothingToDoMessage());
        }
        return new JsonResponse($data);
    }
}
