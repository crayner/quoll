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
 * Date: 19/07/2020
 * Time: 16:09
 */
namespace App\Modules\People\Form;

use App\Modules\Staff\Entity\Staff;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class SchoolStaffType
 * @package App\Modules\People\Form
 * @author Craig Rayner <craig@craigrayner.com>
 */
class SchoolStaffType extends SchoolCommonType
{
    /**
     * configureOptions
     * @param OptionsResolver $resolver
     * 19/07/2020 10:49
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'translation_domain' => 'People',
                'data_class' => Staff::class
            ]
        );
        $resolver->setRequired(
            [
                'remove_personal_background',
            ]
        );
    }
}
