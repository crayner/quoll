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
 * Date: 18/01/2020
 * Time: 08:33
 */

namespace App\Modules\IndividualNeed\Form;

use App\Form\Type\EnumType;
use App\Form\Type\HeaderType;
use App\Form\Type\ReactFormType;
use App\Modules\System\Form\SettingsType;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class INTemplatesType
 * @package App\Modules\IndividualNeed\Form
 * @author Craig Rayner <craig@craigrayner.com>
 */
class INTemplatesType extends AbstractType
{
    /**
     * buildForm
     * @param FormBuilderInterface $builder
     * @param array $options
     * 9/06/2020 11:33
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('settings', SettingsType::class,
                [
                    'settings' => [
                        [
                            'scope' => 'Individual Needs',
                            'name' => 'targetsTemplate',
                            'entry_type' => CKEditorType::class,
                        ],
                        [
                            'scope' => 'Individual Needs',
                            'name' => 'teachingStrategiesTemplate',
                            'entry_type' => CKEditorType::class,
                        ],
                        [
                            'scope' => 'Individual Needs',
                            'name' => 'notesReviewTemplate',
                            'entry_type' => CKEditorType::class,
                        ],
                        [
                            'scope' => 'Individual Needs',
                            'name' => 'investigationNotificationRole',
                            'entry_type' => EnumType::class,
                            'entry_options' => [
                                'choice_list_prefix' => 'securityRole.category',
                                'choice_translation_domain' => 'Security',
                                'placeholder' => ' ',
                            ]
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
     * 9/06/2020 11:33
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'translation_domain' => 'IndividualNeed',
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