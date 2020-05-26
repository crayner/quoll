<?php
/**
 * Created by PhpStorm.
 *
 * quoll
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
use App\Modules\People\Entity\Person;
use App\Modules\People\Manager\PhotoImporter;
use App\Modules\Security\Manager\SecurityUser;
use App\Modules\Security\Util\SecurityHelper;
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
        return $this->getPageManager()->createBreadcrumbs('Import People Photos')
            ->render(['special' => $importer->toArray()]);
    }

    /**
     * uploadPhoto
     * @Route("/personal/photo/{person}/upload/",name="personal_photo_upload_api")
     * @IsGranted("ROLE_ROUTE")
     * @param Person $person
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
            if (!is_dir($path))
                $fs->mkdir($path, 0755);

            $name = uniqid(StringHelper::toSnakeCase($person->formatName(['title' => false, 'preferred' => false, 'reverse' => true])). '_') . '.' . $file->guessExtension();

            $file->move($path, $name);

            $fs->remove($file->getRealpath());

            $file = new File($path . DIRECTORY_SEPARATOR . $name);

            $person->setImage240($file->getRealpath());

            try {
                $em = $this->getDoctrine()->getManager();
                $em->persist($person);
                $em->flush();
            } catch (IOException $e) {
                $fs->remove($file->getRealpath());
                return new JsonResponse(['status' => 'error', 'message' => $e->getMessage()]);
            }

            $photo = [];
            $photo['value'] = $person->getId();
            $photo['photo'] = ImageHelper::getAbsoluteImageURL('file', $person->getImage240());
            return new JsonResponse(['status' => 'success', 'message' => ErrorMessageHelper::onlySuccessMessage(true), 'person' => $photo], 200);
        }
        return new JsonResponse(['status' => 'error', 'message' => $violations[0]->getMessage()], 200);
    }

    /**
     * removePhoto
     * @Route("/personal/photo/{person}/remove/",name="personal_photo_remove_api")
     * @IsGranted("ROLE_ROUTE")
     * @param Person $person
     * @return JsonResponse
     */
    public function removePhoto(Person $person)
    {
        try {
            $person->setImage240(null);
            $em = $this->getDoctrine()->getManager();
            $em->persist($person);
            $em->flush();
            $photo = [];
            $photo['value'] = $person->getId();
            $photo['photo'] = 'build/static/DefaultPerson.png';
            return new JsonResponse(['status' => 'success', 'message' => TranslationHelper::translate('The photo was removed.',[],'People'), 'person' => $photo], 200);
        } catch ( \Exception $e) {
            return new JsonResponse(['status' => 'error', 'message' => $e->getMessage()], 200);
        }
    }
}