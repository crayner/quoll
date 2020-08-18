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
 * Date: 30/11/2019
 * Time: 15:02
 */
namespace App\Modules\People\Form;

use App\Form\Type\HeaderType;
use App\Form\Type\ReactFormType;
use App\Form\Type\SimpleArrayType;
use App\Modules\System\Form\SettingsType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class PeopleSettingsType
 * @package App\Modules\People\Form
 * @author Craig Rayner <craig@craigrayner.com>
 */
class PeopleFieldValueSettingsType extends AbstractType
{
    /**
     * buildForm
     *
     * 19/08/2020 09:04
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('fieldValueHeader', HeaderType::class,
                [
                    'label' => 'Field Values',
                ]
            )
            ->add('fieldValueSettings', SettingsType::class,
                [
                    'settings' => [
                        [
                            'scope' => 'People',
                            'name' => 'nationality',
                            'entry_type' => SimpleArrayType::class,
                        ],
                        [
                            'scope' => 'People',
                            'name' => 'ethnicity',
                            'entry_type' => SimpleArrayType::class,
                        ],
                        [
                            'scope' => 'People',
                            'name' => 'religions',
                            'entry_type' => SimpleArrayType::class,
                        ],
                        [
                            'scope' => 'People',
                            'name' => 'residencyStatus',
                            'entry_type' => SimpleArrayType::class,
                        ],
                        [
                            'scope' => 'People',
                            'name' => 'departureReasons',
                            'entry_type' => SimpleArrayType::class,
                        ],
                    ],
                ]
            )
            ->add('submit2', SubmitType::class)
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
                'translation_domain' => 'People',
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