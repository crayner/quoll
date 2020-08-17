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
 * Date: 14/01/2020
 * Time: 17:09
 */
namespace App\Modules\MarkBook\Form;

use App\Form\Type\HeaderType;
use App\Form\Type\ParagraphType;
use App\Form\Type\ReactFormType;
use App\Form\Type\SimpleArrayType;
use App\Form\Type\ToggleType;
use App\Modules\System\Manager\SettingFactory;
use App\Modules\System\Form\SettingsType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class MarkbookSettingType
 * @package App\Modules\MarkBook\Form
 * @author Craig Rayner <craig@craigrayner.com>
 */
class MarkBookWarningsSettingType extends AbstractType
{
    /**
     * buildForm
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('warningsHeader', HeaderType::class,
                [
                    'label' => 'Warnings',
                    'panel' => 'Warnings',
                ]
            )
            ->add('warningsSettings', SettingsType::class,
                [
                    'settings' => [
                        [
                            'scope' => 'Mark Book',
                            'name' => 'showStudentAttainmentWarning',
                            'entry_type' => ToggleType::class,
                        ],
                        [
                            'scope' => 'Mark Book',
                            'name' => 'showStudentEffortWarning',
                            'entry_type' => ToggleType::class,
                        ],
                        [
                            'scope' => 'Mark Book',
                            'name' => 'showParentAttainmentWarning',
                            'entry_type' => ToggleType::class,
                        ],
                        [
                            'scope' => 'Mark Book',
                            'name' => 'showParentEffortWarning',
                            'entry_type' => ToggleType::class,
                        ],
                        [
                            'scope' => 'Mark Book',
                            'name' => 'personalisedWarnings',
                            'entry_type' => ToggleType::class,
                        ],
                    ],
                    'panel' => 'Warnings',
                ]
            )
            ->add('submit3', SubmitType::class,
                [
                    'label' => 'Submit',
                    'panel' => 'Warnings',
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
                'translation_domain' => 'MarkBook',
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