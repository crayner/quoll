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
 * Date: 29/06/2020
 * Time: 12:24
 */
namespace App\Modules\Security\Form;

use App\Form\Type\EnumType;
use App\Form\Type\HeaderType;
use App\Form\Type\ParagraphType;
use App\Form\Type\ReactFormType;
use App\Form\Type\ToggleType;
use App\Modules\Security\Entity\SecurityRole;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class SecurityRoleType
 * @package App\Modules\Security\Form
 * @author Craig Rayner <craig@craigrayner.com>
 */
class SecurityRoleType extends AbstractType
{
    /**
     * buildForm
     * @param FormBuilderInterface $builder
     * @param array $options
     * 29/06/2020 12:26
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('role', TextType::class,
                [
                    'label' => 'Security Role',
                ]
            )
            ->add('label', TextType::class,
                [
                    'label' => 'Role Label',
                ]
            )
            ->add('category', EnumType::class,
                [
                    'label' => 'Role Category',
                    'help' => 'Group roles.',
                    'placeholder' => 'Please select...'
                ]
            )
            ->add('allowLogin', ToggleType::class,
                [
                    'label' => 'Can Access Site (Login)',
                    'use_boolean_values' => true,
                    'visible_by_choice' => 'allow_login',
                ]
            )
            ->add('allowPastYears', ToggleType::class,
                [
                    'label' => 'Work in Past Years',
                    'use_boolean_values' => true,
                    'visible_parent' => 'security_role_allowLogin',
                    'visible_values' => ['allow_login'],
                ]
            )
            ->add('allowFutureYears', ToggleType::class,
                [
                    'label' => 'Work in Future Years',
                    'use_boolean_values' => true,
                    'visible_parent' => 'security_role_allowLogin',
                    'visible_values' => ['allow_login'],
                ]
            )
            ->add('childRoles', EntityType::class,
                [
                    'label' => 'Child Security Roles',
                    'class' => SecurityRole::class,
                    'multiple' => true,
                    'choice_label' => 'role',
                    'query_builder' => function(EntityRepository $er) {
                        return $er->createQueryBuilder('r')
                            ->addOrderBy('r.role', 'ASC')
                            ->orderBy('r.category', 'ASC')
                        ;
                    },
                    'attr' => [
                        'size' => 8,
                    ],
                ]
            )
        ;
        if($options['data']->getId()) {
            $builder
                ->add('reachableRoles', ParagraphType::class,
                    [
                        'help' => 'reachable.roles',
                        'help_translation_parameters' => ['{roles}' => $options['data']->getChildRolesAsString(true)],
                        'wrapper_class' => 'info',
                    ]
                )
            ;
        }
        $builder
            ->add('submit', SubmitType::class)
        ;
    }

    /**
     * configureOptions
     * @param OptionsResolver $resolver
     * 29/06/2020 12:26
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'translation_domain' => 'Security',
                'data_class' => SecurityRole::class,
            ]
        );
    }

    /**
     * getParent
     * @return string|null
     * 29/06/2020 12:40
     */
    public function getParent()
    {
        return ReactFormType::class;
    }
}