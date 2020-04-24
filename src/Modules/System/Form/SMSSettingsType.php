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
 * Date: 7/09/2019
 * Time: 11:57
 */

namespace App\Modules\System\Form;

use App\Form\Type\HeaderType;
use App\Form\Type\ParagraphType;
use App\Form\Type\ReactFormType;
use App\Modules\System\Entity\Setting;
use App\Provider\ProviderFactory;
use App\Util\ReactFormHelper;
use App\Util\TranslationHelper;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class SMSSettingsType
 * @package App\Modules\System\Form
 */
class SMSSettingsType extends AbstractType
{
    /**
     * buildForm
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('smsHeader', HeaderType::class,
                [
                    'label' => 'SMS Settings',
                    'help' => 'Kookaburra can use a number of different gateways to send out SMS messages. These are paid services, not affiliated with Kookaburra, and you must create your own account with them before being able to send out SMSs using the Messenger module.',
                ]
            )
            ->add('smsSettings', SettingsType::class,
                [
                    'settings' => [
                        [
                            'scope' => 'Messenger',
                            'name' => 'smsGateway',
                            'entry_type' => ChoiceType::class,
                            'entry_options' => [
                                'choices' => [
                                    'OneWaySMS' => 'OneWaySMS',
                                    'Twilio' => 'Twilio',
                                    'Nexmo' => 'Nexmo',
                                    'Clockwork' => 'Clockwork',
                                    'TextLocal' => 'TextLocal',
                                    'Mail to SMS' => 'Mail to SMS'
                                ],
                                'choice_translation_domain' => false,
                                'placeholder' => 'No SMS Settings',
                                'visible_by_choice' => true,
                            ],
                        ],
                        [
                            'scope' => 'Messenger',
                            'name' => 'smsSenderID',
                            'entry_type' => TextType::class,
                            'entry_options' => [
                                'attr' => [
                                    'maxLength' => 50,
                                ],
                                'visible_values' => ['OneWaySMS','Twilio','Nexmo','Clockwork','TextLocal','Mail to SMS'],
                            ],
                        ],
                        [
                            'scope' => 'Messenger',
                            'name' => 'smsUsername',
                            'entry_type' => TextType::class,
                            'entry_options' => [
                                'attr' => [
                                    'maxLength' => 50,
                                ],
                                'visible_values' => ['OneWaySMS','Twilio', 'Nexmo', 'Clockwork', 'TextLocal', 'Mail to SMS'],
                                'visible_labels' => [
                                    'OneWaySMS' => [
                                        'label' => TranslationHelper::translate('SMS Username'),
                                        'help' => TranslationHelper::translate('SMS gateway username')
                                    ],
                                    'Twilio' => [
                                        'label' => TranslationHelper::translate('API Key'),
                                        'help' => '',
                                    ],
                                    'Nexmo' => [
                                        'label' => TranslationHelper::translate('API Key'),
                                        'help' => '',
                                    ],
                                    'Clockwork' => [
                                        'label' => TranslationHelper::translate('API Key'),
                                        'help' => '',
                                    ],
                                    'TextLocal' => [
                                        'label' => TranslationHelper::translate('API Key'),
                                        'help' => '',
                                    ],
                                    'Mail to SMS' => [
                                        'label' => TranslationHelper::translate('SMS Domain'),
                                        'help' => '',
                                    ],
                                ],
                            ],
                        ],
                        [
                            'scope' => 'Messenger',
                            'name' => 'smsPassword',
                            'entry_type' => TextType::class,
                            'entry_options' => [
                                'attr' => [
                                    'maxLength' => 50,
                                ],
                                'visible_values' => ['OneWaySMS','Twilio','Nexmo'],
                                'visible_labels' => [
                                    'OneWaySMS' => [
                                        'label' => TranslationHelper::translate('SMS Password'),
                                        'help' => TranslationHelper::translate('SMS gateway password')
                                    ],
                                    'Twilio' => [
                                        'label' => TranslationHelper::translate('API Secret/Auth Token'),
                                        'help' => '',
                                    ],
                                    'Nexmo' => [
                                        'label' => TranslationHelper::translate('API Secret/Auth Token'),
                                        'help' => '',
                                    ],
                                ],
                            ],
                        ],
                        [
                            'scope' => 'Messenger',
                            'name' => 'smsURL',
                            'entry_type' => TextType::class,
                            'entry_options' => [
                                'visible_values' => ['OneWaySMS'],
                            ],
                        ],
                        [
                            'scope' => 'Messenger',
                            'name' => 'smsURLCredit',
                            'entry_type' => TextType::class,
                            'entry_options' => [
                                'visible_values' => ['OneWaySMS'],
                            ],
                        ],
                    ],
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