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
use App\Modules\People\Entity\Address;
use App\Modules\People\Entity\Contact;
use App\Modules\People\Entity\Person;
use App\Modules\People\Entity\Phone;
use App\Modules\People\Form\ContactType;
use App\Provider\ProviderFactory;
use App\Util\ErrorMessageHelper;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\MakerBundle\Validator;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\ValidatorBuilder;

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
     * @param Contact $contact
     * @return JsonResponse
     * 20/07/2020 11:27
     * @Route("/contact/{contact}/edit/",name="contact_edit")
     * @IsGranted("ROLE_ROUTE")
     */
    public function editContact(ContainerManager $manager, Contact $contact)
    {
        $contact->getPerson();
        if ($this->getRequest()->getContentType() === 'json') {

            $this->getDoctrine()->getManager()->refresh($contact);

            $form = $this->createContactForm($contact);

            return $this->saveContactContent($form, $manager, $contact);
        } else {
            $form = $this->createContactForm($contact);
            $data = ErrorMessageHelper::getInvalidInputsMessage([], true);
            $manager->singlePanel($form->createView());
            $data['form'] = $manager->getFormFromContainer();
            return new JsonResponse($data);
        }
    }

    /**
     * createContactForm
     * @param Contact $contact
     * @return FormInterface
     * 23/07/2020 11:35
     */
    private function createContactForm(Contact $contact): FormInterface
    {
        return $this->createForm(ContactType::class, $contact,
            [
                'action' => $this->generateUrl('contact_edit', ['contact' => $contact->getId()]),
            ]
        );
    }

    /**
     * saveContactContent
     * @param FormInterface $form
     * @param ContainerManager $manager
     * @param Contact $contact
     * @return JsonResponse
     * 23/07/2020 10:59
     */
    private function saveContactContent(FormInterface $form, ContainerManager $manager, Contact $contact)
    {
        $content = json_decode($this->getRequest()->getContent(), true);

        foreach($content as $name=>$value) {

            $method = 'set' . ucfirst($name);
            if ($name === 'physicalAddress') {
                $contact->setPhysicalAddress(ProviderFactory::getRepository(Address::class)->find($value));
                continue;
            }
            if ($name === 'postalAddress') {
                $contact->setPostalAddress(ProviderFactory::getRepository(Address::class)->find($value));
                continue;
            }
            if ($name === 'personalPhone') {
                $contact->setPersonalPhone(ProviderFactory::getRepository(Phone::class)->find($value));
                continue;
            }
            if ($name === 'person') {
                $contact->setPerson(ProviderFactory::getRepository(Person::class)->find($value));
                continue;
            }
            if (method_exists($contact, $method)) {
                if ($value === '') $value = null;
                $contact->$method($value ?? null);
            }
        }

        $validator = Validation::createValidator();
        $errorList = $validator->validate($contact);

        $data = [];
        if ($errorList->count() === 0) {
            $data = ProviderFactory::create(Contact::class)->persistFlush($contact, $data);
            if ($data['status'] !== 'success') {
                $data = ErrorMessageHelper::getDatabaseErrorMessage($data, true);
                $manager->singlePanel($form->createView());
                $data['form'] = $manager->getFormFromContainer();
                return new JsonResponse($data);
            } else {
                $form = $this->createContactForm($contact);
            }
            $manager->singlePanel($form->createView());
            $data['form'] = $manager->getFormFromContainer();
        } else {
            $data = ErrorMessageHelper::getInvalidInputsMessage($data, true);
            $manager->singlePanel($form->createView());
            $data['form'] = $manager->getFormFromContainer();
        }
        return new JsonResponse(ErrorMessageHelper::uniqueErrors($data));
    }
}
