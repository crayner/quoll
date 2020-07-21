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
use App\Modules\People\Entity\ParentContact;
use App\Modules\People\Entity\Person;
use App\Modules\People\Entity\PersonalDocumentation;
use App\Modules\People\Form\ParentType;
use App\Provider\ProviderFactory;
use App\Util\ErrorMessageHelper;
use Doctrine\DBAL\Driver\PDOException;
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
     * editParent
     * @param ContainerManager $manager
     * @param Person $person
     * @return JsonResponse
     * 20/07/2020 11:27
     * @Route("/parent/{person}/edit/",name="parent_edit")
     * @IsGranted("ROLE_ROUTE")
     */
    public function editParent(ContainerManager $manager, Person $person)
    {
        if ($this->getRequest()->getContentType() === 'json') {

            $parent = $person->getParent() ?: new ParentContact($person);

            $form = $this->createParentForm($person);

            return $this->saveContent($form, $manager, $parent);
        } else {
            $form = $this->createParentForm($person);
            $data = ErrorMessageHelper::getInvalidInputsMessage([], true);
            $manager->singlePanel($form->createView());
            $data['form'] = $manager->getFormFromContainer();
            return new JsonResponse($data);
        }
    }

    /**
     * createParentForm
     * @param Person $person
     * @return FormInterface
     * 20/07/2020 11:29
     */
    private function createParentForm(Person $person): FormInterface
    {
        return $this->createForm(ParentType::class, $person->getParent(),
            [
                'action' => $this->generateUrl('parent_edit', ['person' => $person->getId()]),
            ]
        );
    }

    /**
     * saveContent
     * @param FormInterface $form
     * @param ContainerManager $manager
     * @param ParentContact $parent
     * @return JsonResponse
     * 20/07/2020 11:31
     */
    private function saveContent(FormInterface $form, ContainerManager $manager, ParentContact $parent)
    {
        $content = json_decode($this->getRequest()->getContent(), true);

        $form->submit($content);
        $data = [];
        if ($form->isValid()) {
            $data = ProviderFactory::create(ParentContact::class)->persistFlush($parent, $data, false);
            $data = ProviderFactory::create(Person::class)->persistFlush($parent->getPerson(), $data);
            if ($data['status'] !== 'success') {
                $data = ErrorMessageHelper::getDatabaseErrorMessage($data, true);
                $manager->singlePanel($form->createView());
                $data['form'] = $manager->getFormFromContainer();
                return new JsonResponse($data);
            } else {
                $form = $this->createParentForm($parent->getPerson());
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
     * addToParent
     * @param Person $person
     * @Route("/parent/{person}/add/",name="parent_add")
     * @IsGranted("ROLE_ROUTE")
     * @return JsonResponse
     * 21/07/2020 10:47
     */
    public function addToParent(Person $person)
    {
        if (null === $person->getParent()) {
            new ParentContact($person);
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
