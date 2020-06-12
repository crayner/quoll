<?php
/**
 * Created by PhpStorm.
 *
 * kookaburra
 * (c) 2020 Craig Rayner <craig@craigrayner.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * User: craig
 * Date: 15/01/2020
 * Time: 15:05
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
 * Class ResourceSettingsType
 * @package App\Modules\School\Form
 * @author Craig Rayner <craig@craigrayner.com>
 */
class ResourceSettingsType extends AbstractType
{
    /**
     * buildForm
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('resourceHeader', HeaderType::class,
                [
                    'label' => 'Resource Settings',
                    'panel' => 'Category',
                ]
            )
            ->add('settings', SettingsType::class,
                [
                    'settings' => [
                        [
                            'scope' => 'Resources',
                            'name' => 'categories',
                            'entry_type' => SimpleArrayType::class,
                        ],
                    ],
                    'panel' => 'Category',
                ]
            )
            ->add('submit', SubmitType::class,
                [
                    'label' => 'Submit',
                    'panel' => 'Category',
                    'translation_domain' => 'messages',
                ]
            )
            ->add('purpouseHeader', HeaderType::class,
                [
                    'label' => 'Purpose Settings',
                    'panel' => 'Purpose',
                ]
            )
            ->add('purposeSettings', SettingsType::class,
                [
                    'settings' => [
                        [
                            'scope' => 'Resources',
                            'name' => 'purposesGeneral',
                            'entry_type' => SimpleArrayType::class,
                        ],
                        [
                            'scope' => 'Resources',
                            'name' => 'purposesRestricted',
                            'entry_type' => SimpleArrayType::class,
                        ],
                    ],
                    'panel' => 'Purpose',
                ]
            )
            ->add('submit1', SubmitType::class,
                [
                    'label' => 'Submit',
                    'panel' => 'Purpose',
                    'translation_domain' => 'messages',
                ]
            )
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
                'data_class' => null,
                'translation_domain' => 'School',
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
