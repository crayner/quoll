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
 * Date: 25/07/2020
 * Time: 14:55
 */
namespace App\Modules\People\Form;

/**
 * Class FormatNameSettingGeneralType
 * @package App\Modules\People\Form
 * @author Craig Rayner <craig@craigrayner.com>
 */
class FormatNameSettingGeneralType extends FormatNameSettingType
{
    /**
     * getBlockPrefix
     * @return string|null
     * 25/07/2020 14:56
     */
    public function getBlockPrefix()
    {
        return 'format_name_setting_general';
    }
}
