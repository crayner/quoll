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

use App\Form\Transform\NoOnEmptyTransformer;
use App\Form\Type\HeaderType;
use App\Form\Type\ReactFormType;
use App\Modules\System\Entity\Setting;
use App\Provider\ProviderFactory;
use App\Util\TranslationHelper;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class SMSSettingsType
 * @package App\Modules\System\Form
 */
class EmailSettingsType extends AbstractType
{
    /**
     * buildForm
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        TranslationHelper::setDomain('System');
        $builder
            ->add('emailHeader', HeaderType::class,
                [
                    'label' => 'E-Mail',
                ]
            )
            ->add('emailSettings', SettingsType::class,
                [
                    'settings' => [
                        [
                            'scope' => 'System',
                            'name' => 'enableMailerSMTP',
                            'entry_type' => ChoiceType::class,
                            'entry_options' => [
                                'choices' => [
                                    'GMail' => 'GMail',
                                    'SMTP' => 'SMTP',
                                ],
                                'placeholder' => 'No E-Mail Settings',
                                'required' => false,
                                'visible_by_choice' => true,
                            ],
                        ],
                        [
                            'scope' => 'System',
                            'name' => 'mailerSMTPUsername',
                            'entry_type' => TextType::class,
                            'entry_options' => [
                                'visible_values' => ['GMail','SMTP'],
                                'visible_labels' =>
                                    [
                                        'GMail' =>
                                            [
                                                'label' => TranslationHelper::translate('GMail Account'),
                                                'help' => TranslationHelper::translate('Gmail uses the full email address for the account name.'),
                                            ],
                                        'SMTP' =>
                                            [
                                                'label' => TranslationHelper::translate('SMTP Username'),
                                                'help' => TranslationHelper::translate('Username to use for SMTP authentication. Leave blank for no authentication.'),
                                            ],
                                    ],
                            ],
                        ],
                        [
                            'scope' => 'System',
                            'name' => 'mailerSMTPPassword',
                            'entry_type' => TextType::class,
                            'entry_options' => [
                                'visible_values' => ['GMail','SMTP'],
                                'visible_labels' =>
                                    [
                                        'GMail' =>
                                            [
                                                'label' => TranslationHelper::translate('GMail Password'),
                                                'help' => '',
                                            ],
                                        'SMTP' =>
                                            [
                                                'label' => TranslationHelper::translate('SMTP Password'),
                                                'help' => TranslationHelper::translate('Password to use for SMTP authentication. Leave blank for no authentication.'),
                                            ],
                                    ],
                            ],
                        ],
                        [
                            'scope' => 'System',
                            'name' => 'mailerSMTPHost',
                            'entry_type' => TextType::class,
                            'entry_options' => [
                                'visible_values' => ['SMTP'],
                            ],
                        ],
                        [
                            'scope' => 'System',
                            'name' => 'mailerSMTPPort',
                            'entry_type' => IntegerType::class,
                            'entry_options' => [
                                'visible_values' => ['SMTP'],
                            ],
                        ],
                        [
                            'scope' => 'System',
                            'name' => 'mailerSMTPSecure',
                            'entry_type' => ChoiceType::class,
                            'entry_options' => [
                                'choices' => [
                                    'Automatic' => 'auto',
                                    'tls'  => 'tls',
                                    'ssl'  => 'ssl',
                                    'None' => 'none',
                                ],
                                'visible_values' => ['SMTP'],
                            ],
                        ],
                    ],
                ]
            )
            ->add('submit', SubmitType::class);

        TranslationHelper::setDomain('System');
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