<?php
/**
 * Created by PhpStorm.
 *
 * Quoll
 * (c) 2019 Craig Rayner <craig@craigrayner.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * User: craig
 * Date: 3/09/2019
 * Time: 14:33
 */

namespace App\Modules\System\Form;

use App\Form\Type\HeaderType;
use App\Form\Type\ReactFormType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CountryType;
use Symfony\Component\Form\Extension\Core\Type\CurrencyType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TimezoneType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class LocalisationSettingsType
 * @package App\Modules\System\Form
 */
class LocalisationSettingsType extends AbstractType
{
    /**
     * buildForm
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('localisationSettingsHeader', HeaderType::class,
                [
                    'label' => 'Localisation'
                ]
            )
            ->add('localisationSettings', SettingsType::class,
                [
                    'settings' => [
                        [
                            'scope' => 'System',
                            'name' => 'firstDayOfTheWeek',
                            'entry_type' => ChoiceType::class,
                            'entry_options' => [
                                'choices' => [
                                    'Monday' => "Monday",
                                    'Sunday' => "Sunday",

                                ],
                            ],
                        ],
                        [
                            'scope' => 'System',
                            'name' => 'currency',
                            'entry_type' => CurrencyType::class,
                            'entry_options' => [
                                'placeholder' => ' ',
                            ],
                        ],
                    ],
                ]
            )
            ->add('country', CountryType::class,
                [
                    'label' => 'Country of Location',
                    'placeholder' => ' ',
                    'alpha3' => true,
                    'data' => $options['country'],
                ]
            )
            ->add('timezone', TimezoneType::class,
                [
                    'label' => 'Time Zone of the School',
                    'placeholder' => ' ',
                    'data' => $options['timezone'],
                ]
            )
            ->add('submit', SubmitType::class);
    }

    /**
     * configureOptions
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'translation_domain' => 'System',
                'data_class' => null,
            ]
        );
        $resolver->setRequired(
            [
                'country',
                'timezone',
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