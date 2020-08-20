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

use App\Manager\StatusManager;
use App\Modules\People\Entity\Person;
use App\Modules\People\Entity\PersonalDocumentation;
use App\Modules\People\Form\PersonalDocumentationType;
use App\Provider\ProviderFactory;
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
     *
     * 19/08/2020 16:19
     * @param PersonalDocumentation $documentation
     * @Route("/personal/documentation/{documentation}/edit/",name="personal_documentation_edit")
     * @IsGranted("ROLE_ROUTE")
     * @return JsonResponse
     */
    public function editPersonalDocumentation(PersonalDocumentation $documentation)
    {
        if ($this->getRequest()->getContentType() === 'json') {

            $form = $this->createDocumentationForm($documentation);

            return $this->saveContent($form, $documentation);
        } else {
            $form = $this->createDocumentationForm($documentation);
            $this->getStatusManager()->error(StatusManager::INVALID_INPUTS);
        }
        return $this->singleForm($form);
    }

    /**
     * createDocumentationForm
     *
     * 20/08/2020 08:45
     * @param PersonalDocumentation $documentation
     * @return FormInterface
     */
    private function createDocumentationForm(PersonalDocumentation $documentation): FormInterface
    {
        return $this->createForm(PersonalDocumentationType::class, $documentation,
            [
                'action' => $this->generateUrl('personal_documentation_edit', ['documentation' => $documentation->getId()]),
                'remove_birth_certificate_scan' => $this->generateUrl('remove_birth_certificate_scan', ['documentation' => $documentation->getId()]),
                'remove_passport_scan' => $this->generateUrl('remove_passport_scan', ['documentation' => $documentation->getId()]),
                'remove_personal_image' => $this->generateUrl('remove_personal_image', ['documentation' => $documentation->getId()]),
                'remove_id_card_scan' => $this->generateUrl('remove_id_card_scan', ['documentation' => $documentation->getId()])
            ]
        );
    }

    /**
     * saveContent
     *
     * 19/08/2020 16:32
     * @param FormInterface $form
     * @param PersonalDocumentation $documentation
     * @return JsonResponse
     */
    private function saveContent(FormInterface $form, PersonalDocumentation $documentation)
    {
        $content = json_decode($this->getRequest()->getContent(), true);

        $form->submit($content);
        if ($form->isValid()) {
            $id = $documentation->getId();
            ProviderFactory::create(PersonalDocumentation::class)->persistFlush($documentation);
            if ($this->isStatusSuccess() && $id !== $documentation->getId()) {
                $form = $this->createDocumentationForm($documentation);
            }
        } else {
            $this->getStatusManager()->error(StatusManager::INVALID_INPUTS);
        }
        return $this->singleForm($form);
    }

    /**
     * removeBirthCertificateScan
     *
     * 19/08/2020 16:28
     * @param PersonalDocumentation $documentation
     * @Route("/personal/documentation/{documentation}/birth/certicate/scan/remove/",name="remove_birth_certificate_scan")
     * @IsGranted("ROLE_ROUTE")
     * @return JsonResponse
     */
    public function removeBirthCertificateScan(PersonalDocumentation $documentation)
    {
        $documentation->removeBirthCertificateScan();

        ProviderFactory::create(PersonalDocumentation::class)->persistFlush($documentation);

        return $this->generateJsonResponse();
    }

    /**
     * removePersonalImage
     *
     * 19/08/2020 16:28
     * @param PersonalDocumentation $documentation
     * @Route("/personal/documentation/{documentation}/personal/image/remove/",name="remove_personal_image")
     * @IsGranted("ROLE_ROUTE")
     * @return JsonResponse
     */
    public function removePersonalImage(PersonalDocumentation $documentation)
    {
        $documentation->removePersonalImage();

        ProviderFactory::create(PersonalDocumentation::class)->persistFlush($documentation);

        return $this->generateJsonResponse();
    }

    /**
     * removePassportScan
     *
     * 19/08/2020 16:29
     * @param PersonalDocumentation $documentation
     * @Route("/personal/documentation/{documentation}/passport/scan/remove/",name="remove_passport_scan")
     * @IsGranted("ROLE_ROUTE")
     * @return JsonResponse
     */
    public function removePassportScan(PersonalDocumentation $documentation)
    {
        $documentation->removeCitizenship1PassportScan();

        ProviderFactory::create(PersonalDocumentation::class)->persistFlush($documentation);

        return $this->generateJsonResponse();
    }

    /**
     * removeIDCardScan
     *
     * 19/08/2020 16:30
     * @param PersonalDocumentation $documentation
     * @Route("/personal/documentation/{documentation}/id/card/scan/remove/",name="remove_id_card_scan")
     * @IsGranted("ROLE_ROUTE")
     * @return JsonResponse
     */
    public function removeIDCardScan(PersonalDocumentation $documentation)
    {
        $documentation->removeNationalIDCardScan();

        ProviderFactory::create(PersonalDocumentation::class)->persistFlush($documentation);

        return $this->generateJsonResponse();
    }
}
