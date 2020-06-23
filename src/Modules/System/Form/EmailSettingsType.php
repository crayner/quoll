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
 * Date: 7/09/2019
 * Time: 11:57
 */

namespace App\Modules\System\Form;

use App\Form\Transform\NoOnEmptyTransformer;
use App\Form\Type\EnumType;
use App\Form\Type\HeaderType;
use App\Form\Type\ReactFormType;
use App\Modules\System\Entity\Setting;
use App\Modules\System\Manager\MailerSettingsManager;
use App\Provider\ProviderFactory;
use App\Util\TranslationHelper;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
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
            ->add('enableMailerSMTP', EnumType::class,
                [
                    'label' => 'Enable Mailer Settings',
                    'help' => 'Allow the system to send emails.',
                    'choice_list_prefix' => 'mailer.enable',
                    'placeholder' => 'No E-Mail Settings',
                    'required' => false,
                    'visible_by_choice' => true,
                ]
            )
            ->add('mailerSMTPUsername', TextType::class,
                [
                    'visible_values' => ['gmail','smtp'],
                    'visible_parent' => 'email_settings_enableMailerSMTP',
                    'visible_labels' =>
                        [
                            'gmail' =>
                                [
                                    'label' => TranslationHelper::translate('GMail Account'),
                                    'help' => TranslationHelper::translate('Gmail uses the full email address for the account name.'),
                                ],
                            'smtp' =>
                                [
                                    'label' => TranslationHelper::translate('SMTP Username'),
                                    'help' => TranslationHelper::translate('Username to use for SMTP authentication. Leave blank for no authentication.'),
                                ],
                        ],
                    'required' => false,
                ]
            )
            ->add('mailerSMTPPassword', TextType::class,
                [
                    'visible_values' => ['gmail','smtp'],
                    'visible_parent' => 'email_settings_enableMailerSMTP',
                    'visible_labels' =>
                        [
                            'gmail' =>
                                [
                                    'label' => TranslationHelper::translate('GMail Password'),
                                    'help' => 'You may need to create an application password on your GMail account. See <a href="https://support.google.com/accounts/answer/185833?hl=en" target="_blank">https://support.google.com/accounts/answer/185833?hl=en</a> for details.',
                                ],
                            'smtp' =>
                                [
                                    'label' => TranslationHelper::translate('SMTP Password'),
                                    'help' => TranslationHelper::translate('Password to use for SMTP authentication. Leave blank for no authentication.'),
                                ],
                        ],
                ]
            )
            ->add('mailerSMTPHost', TextType::class,
                [
                    'visible_values' => ['smtp'],
                    'visible_parent' => 'email_settings_enableMailerSMTP',
                    'label' => 'SMTP Host',
                    'help' => 'The hostname of the email server.',
                ]
            )
            ->add('mailerSMTPPort', TextType::class,
                [
                    'visible_values' => ['smtp'],
                    'visible_parent' => 'email_settings_enableMailerSMTP',
                    'label' => 'SMTP Port',
                    'help' => 'Set the SMTP port number - likely to be 25, 465 or 587.',
                ]
            )
            ->add('mailerSMTPSecure', EnumType::class,
                [
                    'visible_values' => ['smtp'],
                    'visible_parent' => 'email_settings_enableMailerSMTP',
                    'label' => 'SMTP Encryption',
                    'help' => 'Automatically sets the encryption based on the port, otherwise select one manually.',
                    'choice_list_prefix' => 'mailer.secure',
                ]
            )
            ->add('submit', SubmitType::class)
        ;

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
                'data_class' => MailerSettingsManager::class,
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