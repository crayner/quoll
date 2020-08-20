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
use App\Manager\StatusManager;
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
     *
     * 20/08/2020 11:22
     * @param Contact $contact
     * @Route("/contact/{contact}/edit/",name="contact_edit")
     * @IsGranted("ROLE_ROUTE")
     * @return JsonResponse
     */
    public function editContact(Contact $contact)
    {
        $contact->getPerson();
        if ($this->getRequest()->getContentType() === 'json') {

            $this->getDoctrine()->getManager()->refresh($contact);

            $form = $this->createContactForm($contact);

            return $this->saveContactContent($form, $contact);
        } else {
            $this->getStatusManager()->error(StatusManager::INVALID_INPUTS);
            $form = $this->createContactForm($contact);
            return $this->singleForm($form);
        }
    }

    /**
     * createContactForm
     *
     * 20/08/2020 11:22
     * @param Contact $contact
     * @return FormInterface
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
     *
     * 20/08/2020 11:24
     * @param FormInterface $form
     * @param Contact $contact
     * @return JsonResponse
     */
    private function saveContactContent(FormInterface $form, Contact $contact)
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

        if ($errorList->count() === 0) {
            ProviderFactory::create(Contact::class)->persistFlush($contact);
            if ($this->isStatusSuccess()) {
                $form = $this->createContactForm($contact);
            }
        } else {
            $this->getStatusManager()->error(StatusManager::INVALID_INPUTS);
        }
        return $this->singleForm($form);
    }
}
