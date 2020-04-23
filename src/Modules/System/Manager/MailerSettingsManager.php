<?php
/**
 * Created by PhpStorm.
 *
 * kookaburra
 * (c) 2019 Craig Rayner <craig@craigrayner.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * User: craig
 * Date: 8/09/2019
 * Time: 16:29
 */

namespace App\Modules\System\Manager;

use App\Manager\ParameterFileManager;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class MailerSettingsManager
 * @package App\Modules\System\Manager
 */
class MailerSettingsManager
{
    /**
     * handleMailerDsn
     * @param Request $request
     */
    public function handleMailerDsn(Request $request)
    {
        $content = json_decode($request->getContent(), true);
        $config = ParameterFileManager::readParameterFile();

        $result = null;
        $setting = $content['emailSettings'];
        switch ($setting['System__enableMailerSMTP']) {
            case 'GMail':
                $result = 'smtp://'.$setting['System__mailerSMTPUsername'].':'.$setting['System__mailerSMTPPassword'].'@gmail';
                break;
            case 'No':
                break;
            default:
                $result = 'smtp://'.$setting['System__mailerSMTPUsername'].':'.$setting['System__mailerSMTPPassword'].'@'.$setting['System__mailerSMTPHost'].':'.$setting['System__mailerSMTPPort'].'?encryption='.$setting['System__mailerSMTPSecure'];
        }

        $config['parameters']['mailer_dns'] = $result;

        ParameterFileManager::writeParameterFile($config);
    }
}