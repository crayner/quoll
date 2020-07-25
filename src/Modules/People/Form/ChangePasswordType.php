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
 * Date: 27/11/2019
 * Time: 16:39
 */

namespace App\Modules\People\Form;

use App\Form\Type\DisplayType;
use App\Form\Type\HeaderType;
use App\Form\Type\ParagraphType;
use App\Form\Type\ReactFormType;
use App\Form\Type\ToggleType;
use App\Modules\People\Entity\Person;
use App\Modules\Security\Form\PasswordGeneratorType;
use App\Modules\Security\Validator\Password;
use App\Modules\System\Manager\SettingFactory;
use App\Provider\ProviderFactory;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class ChangePasswordType
 * @package App\Modules\People\Form
 */
class ChangePasswordType extends AbstractType
{
    /**
     * buildForm
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $provider = SettingFactory::getSettingManager();
        $builder
            ->add('resetPassword', HeaderType::class,
                [
                    'label' => 'Reset Password',
                ]
            )
            ->add('policy', ParagraphType::class,
                [
                    'help' => $options['policy'],
                    'wrapper_class' => 'warning',
                    'translation_domain' => false,
                ]
            )
            ->add('personName', DisplayType::class,
                [
                    'label' => 'Person',
                    'help' => 'Reset the password for this person.',
                    'data' => $options['data']->formatName('Preferred'),
                    'mapped' => false,
                ]
            )
            ->add('raw', RepeatedType::class,
                [
                    'type' => PasswordGeneratorType::class,
                    'mapped' => false,
                    'first_options' => [
                        'label' => 'New Password',
                        'required' => true,
                    ],
                    'second_options' => [
                        'label' => 'Confirm New Password',
                        'required' => true,
                    ],
                    'constraints' => [
                        new Password(),
                    ],
                    'row_style' => 'transparent',
                    'invalid_message' => 'Your request failed due to non-matching passwords.',
                ]
            )
            ->add('passwordForceReset', ToggleType::class,
                [
                    'label' => 'Force Reset Password?',
                    'help' => 'The person will need to change their password at the next attempt to sign in.',
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
        $resolver->setRequired(
            [
                'action',
                'policy',
            ]
        );
        $resolver->setDefaults(
            [
                'data_class' => Person::class,
                'translation_domain' => 'Security',
                'default'
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