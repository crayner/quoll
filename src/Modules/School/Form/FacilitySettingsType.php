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
 * Date: 4/01/2020
 * Time: 14:20
 */

namespace App\Modules\School\Form;

use App\Form\Type\HeaderType;
use App\Form\Type\ReactFormType;
use App\Form\Type\SimpleArrayType;
use App\Modules\System\Form\SettingsType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class FacilitySettingsType
 * @package App\Modules\School\Form
 * @author Craig Rayner <craig@craigrayner.com>
 */
class FacilitySettingsType extends AbstractType
{
    /**
     * buildForm
     * @param FormBuilderInterface $builder
     * @param array $options
     * 3/06/2020 16:10
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('facilitySettings', SettingsType::class,
                [
                    'settings' => [
                        [
                            'scope' => 'School Admin',
                            'name' => 'facilityTypes',
                            'entry_type' => SimpleArrayType::class,
                        ],
                    ],
                ]
            )
            ->add('submit', SubmitType::class)
        ;
    }

    /**
     * configureOptions
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'translation_domain' => 'School',
                'data_class' => null,
            ]
        );
    }

    /**
     * getParent
     * @return string|null
     */
    public function getParent()
    {
        return ReactFormType::class;
    }
}
