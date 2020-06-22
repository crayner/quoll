<?php
/**
 * Created by PhpStorm.
 *
 * Project: Kookaburra
 * Build: Quoll
 * 
 * (c) 2019 Craig Rayner <craig@craigrayner.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * User: craig
 * Date: 5/09/2019
 * Time: 15:55
 */

namespace App\Util;

use App\Modules\People\Util\UserHelper;
use Symfony\Component\HttpFoundation\File\File;

/**
 * Class JsonFileUploadHelper
 * @package App\Util
 */
class JsonFileUploadHelper
{
    /**
     * saveFile
     * @param string $value
     * @return File|null
     * @throws \Exception
     */
    public static function saveFile(string $value, string $filePrefix): ?File
    {
        if (preg_match('#^data:[^;]*;base64,#', $value) === 1) {
            $targetPath = realpath(__DIR__ . '/../../public/uploads') . DIRECTORY_SEPARATOR . date('Y') . DIRECTORY_SEPARATOR . date('m');
            $target = DIRECTORY_SEPARATOR . 'temp' . uniqid() . '.txt';
            $value = explode(',', $value);
            if (!is_dir($targetPath))
                mkdir($targetPath, '0755', true);
            file_put_contents($targetPath . $target, base64_decode($value[1]));

            $file = new File($targetPath . $target, true);
            $user = UserHelper::getCurrentUser() ?  '_' . UserHelper::getCurrentUser()->getId() : '';
            $fileName = substr(trim($filePrefix, '_') . $user . '_' . uniqid(), 0, 32) . '.' . $file->guessExtension();
            $file->move($targetPath, $fileName);
            $file = new File($targetPath . DIRECTORY_SEPARATOR . $fileName, true);
            return $file;
        }

        $publicDir = realpath(__DIR__ . '/../../public');
        $value = realpath($value) ?: realpath($publicDir.$value) ?: null;
        if (null !== $value) {
            $file = new File($value, true);
            return $file;
        }

        return null;
    }
}