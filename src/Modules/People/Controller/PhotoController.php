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
 * Date: 27/04/2020
 * Time: 10:14
 */
namespace App\Modules\People\Controller;

use App\Controller\AbstractPageController;
use App\Manager\StatusManager;
use App\Modules\People\Entity\Person;
use App\Modules\People\Entity\PersonalDocumentation;
use App\Modules\People\Manager\PhotoImporter;
use App\Provider\ProviderFactory;
use App\Util\ErrorMessageHelper;
use App\Util\ImageHelper;
use App\Util\StringHelper;
use App\Util\TranslationHelper;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\Image;
use Symfony\Component\Validator\Validation;

/**
 * Class PhotoController
 * @package App\Modules\People\Controller
 */
class PhotoController extends AbstractPageController
{
    /**
     * import
     * @param PhotoImporter $importer
     * @return Response|JsonResponse
     * @Route("/personal/photo/import/",name="personal_photo_import")
     * @IsGranted("ROLE_ROUTE")
     */
    public function import(PhotoImporter $importer)
    {
        return $this->getPageManager()
            ->createBreadcrumbs('Import People Photos')
            ->render(['special' => $importer->toArray()]);
    }

    /**
     * uploadPhoto
     *
     * 20/08/2020 15:31
     * @param Person $person
     * @Route("/personal/photo/{person}/upload/",name="personal_photo_upload_api")
     * @IsGranted("ROLE_ROUTE")
     * @return JsonResponse
     */
    public function uploadPhoto(Person $person)
    {
        $request = $this->getRequest();
        $file = $request->files->get('file');

        $validator = Validation::createValidator();
        $constraints = [
            new Image(['maxHeight' => 960, 'maxWidth' => 720, 'mimeTypes' => ['image/jpeg', 'image/png', 'image/gif', 'image/jpeg'], 'mimeTypesMessage' => 'The image is not a JPG/JPEG/GIF/PNG file type.', 'maxRatio' => 0.84, 'minRatio' => 0.7, 'minHeight' => 320, 'minWidth' => 240]),
        ];
        $violations = $validator->validate($file, $constraints);

        if ($violations->count() === 0) {
            $path = $this->getParameter('upload_path');
            $fs = new Filesystem();
            if (!is_dir($path)) {
                $fs->mkdir($path, 0755);
            }

            $path = realpath($path);

            $name = uniqid('personal_'.StringHelper::toSnakeCase($person->formatName('Reversed')). '_') . '.' . $file->guessExtension();

            $file->move($path, $name);

            $fs->remove($file->getRealpath());

            $file = new File($path . DIRECTORY_SEPARATOR . $name);

            $person->getPersonalDocumentation()->setPersonalImage(ImageHelper::getRelativeImageURL($file->getRealpath()));

            ProviderFactory::create(PersonalDocumentation::class)->persistFlush($person->getPersonalDocumentation());

            $photo = [];
            $photo['value'] = $person->getId();
            $photo['photo'] = ImageHelper::getAbsoluteImageURL('file', $person->getPersonalDocumentation()->getPersonalImage());
            $message = $this->getStatusManager()->getMessages()->first()->getTranslatedMessage();
            return $this->getStatusManager()->toJsonResponse(['person' => $photo, 'message' => $message]);
        }
        $this->getStatusManager()->error(StatusManager::INVALID_INPUTS);
        foreach ($violations as $violation) {
            $this->getStatusManager()->error($violation->getMessage());
        }
        return $this->getStatusManager()->toJsonResponse();
    }

    /**
     * removePhoto
     *
     * 20/08/2020 16:30
     * @param Person $person
     * @Route("/personal/photo/{person}/remove/",name="personal_photo_remove_api")
     * @IsGranted("ROLE_ROUTE")
     * @return JsonResponse
     */
    public function removePhoto(Person $person)
    {
        $person->getPersonalDocumentation()->removePersonalImage();
        ProviderFactory::create(PersonalDocumentation::class)->persistFlush($person->getPersonalDocumentation());
        $photo = [];
        $photo['value'] = $person->getId();
        $photo['photo'] = 'build/static/DefaultPerson.png';
        return $this->getStatusManager()->toJsonResponse(['person' => $photo]);
    }
}