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
 * Date: 19/08/2019
 * Time: 17:44
 */

namespace App\Modules\Security\Form;

use App\Form\Type\HeaderType;
use App\Form\Type\ParagraphType;
use App\Form\Type\ReactFormType;
use App\Modules\Security\Form\Entity\ResetPassword;
use App\Modules\Security\Form\PasswordGeneratorType;
use App\Modules\Security\Validator\CurrentPassword;
use App\Modules\Security\Validator\Password;
use App\Modules\System\Entity\Setting;
use App\Provider\ProviderFactory;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class ResetPasswordType
 * @package App\Modules\Security\Form
 */
class ResetPasswordType extends AbstractType
{
    /**
     * buildForm
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $provider = ProviderFactory::create(Setting::class);
        $builder
            ->add('resetPassword', HeaderType::class,
                [
                    'label' => 'Reset Password',
                ]
            )
            ->add('policy', ParagraphType::class,
                [
                    'help' => $options['policy'],
                    'translation_domain' => false,
                    'wrapper_class' => 'warning',
                ]
            )
            ->add('current', PasswordType::class,
                [
                    'label' => 'Current Password',
                    'constraints' => [
                        new CurrentPassword()
                    ],
                ]
            )
            ->add('raw', RepeatedType::class,
                [
                    'type' => PasswordGeneratorType::class,
                    'first_options' => [
                        'label' => 'New Password',
                    ],
                    'second_options' => [
                        'label' => 'Confirm New Password',
                    ],
                    'constraints' => [
                        new Password(),
                    ],
                    'row_style' => 'transparent',
                    'invalid_message' => 'Your request failed due to non-matching passwords.',
                ]
            )
            ->add('submit', SubmitType::class)
            ->setAction($options['action'])
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
                'data_class' => ResetPassword::class,
                'translation_domain' => 'Security',
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