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
 * Date: 7/09/2019
 * Time: 14:35
 */

namespace App\Modules\System\Manager;

use App\Manager\ParameterFileManager;
use App\Modules\System\Entity\Setting;
use App\Modules\System\Provider\SettingProvider;
use App\Provider\ProviderFactory;
use App\Util\ErrorMessageHelper;
use App\Util\TranslationHelper;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Yaml\Yaml;
use Symfony\Contracts\Translation\TranslatorInterface;

class GoogleSettingManager
{
    /**
     * @var SettingProvider
     */
    private $provider;

    /**
     * GoogleSettingManager constructor.
     */
    public function __construct()
    {
        $this->provider = ProviderFactory::create(Setting::class);
    }

    /**
     * handleGoogleSecretsFile
     * @param FormInterface $form
     * @param Request $request
     * @param TranslatorInterface $translator
     * @return array
     */
    public function handleGoogleSecretsFile(FormInterface $form, Request $request)
    {
        $fileName = realpath($form->get('clientSecretFile')->getData()) ?: realpath(ParameterFileManager::getProjectDir() . '/public' .$form->get('clientSecretFile')->getData()) ?: '';
        if (is_file($fileName)) {
            $content = json_decode($request->getContent(), true);

            $file = new File($fileName, true);
            try {
                $secret = json_decode(file_get_contents($file->getRealPath()), true);
            } catch (\Exception $e) {
                return ['class' => 'error', 'message' => ErrorMessageHelper::onlyFileTransferMessage(true)];
            }
            unlink($file->getRealPath());
            $fileSystem = new Filesystem();
            $fileSystem->remove(__DIR__ . '/../../../../var/cache');
            if($content['googleOAuth'] !== 'Y') {
                return $this->turnGoogleIntegrationOff();
            }

            $config = ParameterFileManager::readParameterFile();

            $config['parameters']['google_oauth'] = $content['googleOAuth'] === 'Y';
            $config['parameters']['google_api_key'] = $content['developerKey'];
            $config['parameters']['google_client_id'] = $secret['web']['client_id'];
            $config['parameters']['google_client_secret'] = $secret['web']['client_secret'];

            ParameterFileManager::writeParameterFile($config);

            return ['class' => 'info', 'message' => TranslationHelper::translate('Your requested included a valid Google Secret File.  The information was successfully stored.', [], 'System')];
        } else {
            $content = json_decode($request->getContent(), true);
            $fileSystem = new Filesystem();
            $fileSystem->remove(__DIR__ . '/../../../../var/cache');
            if($content['googleOAuth'] !== 'Y') {
                return $this->turnGoogleIntegrationOff();
            }

            $config = ParameterFileManager::readParameterFile();

            $config['parameters']['google_oauth'] = $content['googleOAuth'] === 'Y';
            $config['parameters']['google_api_key'] = $content['developerKey'];

            ParameterFileManager::writeParameterFile($config);

            return ['class' => 'info', 'message' => TranslationHelper::translate('Your requested did not included a valid Google Secret File. All other Google changes where saved.', [], 'System')];
        }
    }

    private function turnGoogleIntegrationOff()
    {
        $config = ParameterFileManager::readParameterFile();
        $config['parameters']['google_oauth'] = false;
        $config['parameters']['google_api_key'] = null;
        $config['parameters']['google_client_id'] = null;
        $config['parameters']['google_client_secret'] = null;

        ParameterFileManager::writeParameterFile($config);
        return ['class' => 'info', 'message' => TranslationHelper::translate('Google integration has been turned off.', [], 'System')];
    }
}