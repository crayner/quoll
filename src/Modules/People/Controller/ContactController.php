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
 * Time: 10:11
 */
namespace App\Modules\People\Controller;

use App\Container\ContainerManager;
use App\Modules\People\Entity\Contact;
use App\Modules\People\Entity\Person;
use App\Modules\People\Form\ContactType;
use App\Provider\ProviderFactory;
use App\Util\ErrorMessageHelper;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class ContactController
 * @package App\Modules\People\Controller
 * @author Craig Rayner <craig@craigrayner.com>
 */
class ContactController extends PeopleController
{
    /**
     * editContact
     * @param ContainerManager $manager
     * @param Person $person
     * @return JsonResponse
     * 20/07/2020 11:27
     * @Route("/contact/{person}/edit/",name="contact_edit")
     * @IsGranted("ROLE_ROUTE")
     */
    public function editContact(ContainerManager $manager, Person $person)
    {
        if ($this->getRequest()->getContentType() === 'json') {

            $contact = $person->getContact() ?: new Contact($person);

            $form = $this->createContactForm($person);

            return $this->saveContent($form, $manager, $contact);
        } else {
            $form = $this->createContactForm($person);
            $data = ErrorMessageHelper::getInvalidInputsMessage([], true);
            $manager->singlePanel($form->createView());
            $data['form'] = $manager->getFormFromContainer();
            return new JsonResponse($data);
        }
    }

    /**
     * createContactForm
     * @param Person $person
     * @return FormInterface
     * 20/07/2020 11:29
     */
    private function createContactForm(Person $person): FormInterface
    {
        return $this->createForm(ContactType::class, $person->getContact(),
            [
                'action' => $this->generateUrl('contact_edit', ['person' => $person->getId()]),
            ]
        );
    }

    /**
     * saveContent
     * @param FormInterface $form
     * @param ContainerManager $manager
     * @param Contact $contact
     * @return JsonResponse
     * 20/07/2020 11:31
     */
    private function saveContent(FormInterface $form, ContainerManager $manager, Contact $contact)
    {
        $content = json_decode($this->getRequest()->getContent(), true);

        $form->submit($content);
        $data = [];
        if ($form->isValid()) {
            $data = ProviderFactory::create(Contact::class)->persistFlush($contact, $data, false);
            $data = ProviderFactory::create(Person::class)->persistFlush($contact->getPerson(), $data);
            if ($data['status'] !== 'success') {
                $data = ErrorMessageHelper::getDatabaseErrorMessage($data, true);
                $manager->singlePanel($form->createView());
                $data['form'] = $manager->getFormFromContainer();
                return new JsonResponse($data);
            } else {
                $form = $this->createContactForm($contact->getPerson());
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
}