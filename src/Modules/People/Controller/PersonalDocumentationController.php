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
 * Date: 20/07/2020
 * Time: 11:24
 */

namespace App\Modules\People\Controller;

use App\Container\ContainerManager;
use App\Modules\People\Entity\Person;
use App\Modules\People\Entity\PersonalDocumentation;
use App\Modules\People\Form\PersonalDocumentationType;
use App\Provider\ProviderFactory;
use App\Util\ErrorMessageHelper;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class PersonalDocumentationController
 * @package App\Modules\People\Controller
 * @author Craig Rayner <craig@craigrayner.com>
 */
class PersonalDocumentationController extends PeopleController
{
    /**
     * editPersonalDocumentation
     * @param ContainerManager $manager
     * @param Person $person
     * @return JsonResponse
     * 20/07/2020 11:27
     * @Route("/personal/documentation/{person}/edit/",name="personal_documentation_edit")
     * @IsGranted("ROLE_ROUTE")
     */
    public function editPersonalDocumentation(ContainerManager $manager, Person $person)
    {
        if ($this->getRequest()->getContentType() === 'json') {

            $documentation = $person->getPersonalDocumentation() ?: new PersonalDocumentation($person);

            $form = $this->createDocumentationForm($person);

            return $this->saveContent($form, $manager, $documentation);
        } else {
            $form = $this->createDocumentationForm($person);
            $data = ErrorMessageHelper::getInvalidInputsMessage([], true);
            $manager->singlePanel($form->createView());
            $data['form'] = $manager->getFormFromContainer();
            return new JsonResponse($data);
        }
    }

    /**
     * createDocumentationForm
     * @param Person $person
     * @return FormInterface
     * 20/07/2020 11:29
     */
    private function createDocumentationForm(Person $person): FormInterface
    {
        return $this->createForm(PersonalDocumentationType::class, $person->getPersonalDocumentation(),
            [
                'action' => $this->generateUrl('personal_documentation_edit', ['person' => $person->getId()]),
                'remove_birth_certificate_scan' => $this->generateUrl('remove_birth_certificate_scan', ['person' => $person->getId()]),
                'remove_passport_scan' => $this->generateUrl('remove_passport_scan', ['person' => $person->getId()]),
                'remove_personal_image' => $this->generateUrl('remove_personal_image', ['person' => $person->getId()]),
            ]
        );
    }

    /**
     * saveContent
     * @param FormInterface $form
     * @param ContainerManager $manager
     * @param PersonalDocumentation $documentation
     * @return JsonResponse
     * 20/07/2020 11:31
     */
    private function saveContent(FormInterface $form, ContainerManager $manager, PersonalDocumentation $documentation)
    {
        $content = json_decode($this->getRequest()->getContent(), true);

        $form->submit($content);
        $data = [];
        if ($form->isValid()) {
            $data = ProviderFactory::create(PersonalDocumentation::class)->persistFlush($documentation, $data, false);
            $data = ProviderFactory::create(PersonalDocumentation::class)->persistFlush($documentation->getPerson(), $data);
            if ($data['status'] !== 'success') {
                $data = ErrorMessageHelper::getDatabaseErrorMessage($data, true);
                $manager->singlePanel($form->createView());
                $data['form'] = $manager->getFormFromContainer();
                return new JsonResponse($data);
            } else {
                    $form = $this->createDocumentationForm($documentation->getPerson());
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
     * removeBirthCertificateScan
     * @param Person $person
     * @Route("/personal/documentation/{person}/birth/certicate/scan/remove/",name="remove_birth_certificate_scan")
     * @IsGranted("ROLE_ROUTE")
     * @return JsonResponse
     * 20/07/2020 13:37
     */
    public function removeBirthCertificateScan(Person $person)
    {
        $documentation = $person->getPersonalDocumentation();

        $documentation->removeBirthCertificateScan();

        $data = ProviderFactory::create(PersonalDocumentation::class)->persistFlush($documentation, []);

        return new JsonResponse($data);
    }

    /**
     * removeBirthCertificateScan
     * @param Person $person
     * @Route("/personal/documentation/{person}/personal/image/remove/",name="remove_personal_image")
     * @IsGranted("ROLE_ROUTE")
     * @return JsonResponse
     * 20/07/2020 13:37
     */
    public function removePersonalImage(Person $person)
    {
        $documentation = $person->getPersonalDocumentation();

        $documentation->removePersonalImage();

        $data = ProviderFactory::create(PersonalDocumentation::class)->persistFlush($documentation, []);

        return new JsonResponse($data);
    }

    /**
     * removePassportScan
     * @param Person $person
     * @Route("/personal/documentation/{person}/passport/scan/remove/",name="remove_passport_scan")
     * @IsGranted("ROLE_ROUTE")
     * @return JsonResponse
     * 20/07/2020 13:37
     */
    public function removePassportScan(Person $person)
    {
        $documentation = $person->getPersonalDocumentation();

        $documentation->removeCitizenship1PassportScan();

        $data = ProviderFactory::create(PersonalDocumentation::class)->persistFlush($documentation, []);

        return new JsonResponse($data);
    }
}
