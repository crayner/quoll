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
 * Date: 21/07/2020
 * Time: 14:12
 */
namespace App\Modules\Security\Form\Entity;

use App\Form\Type\HeaderType;
use App\Form\Type\HiddenEntityType;
use App\Form\Type\ParagraphType;
use App\Form\Type\ReactFormType;
use App\Form\Type\ToggleType;
use App\Modules\People\Entity\Person;
use App\Modules\Security\Entity\SecurityUser;
use App\Modules\System\Entity\Locale;
use App\Provider\ProviderFactory;
use App\Util\ParameterBagHelper;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Role\RoleHierarchyInterface;

/**
 * Class SecurityUserType
 * @package App\Modules\Security\Form\Entity
 * @author Craig Rayner <craig@craigrayner.com>
 */
class SecurityUserType extends AbstractType
{
    /**
     * @var array
     */
    private $hierarchy;

    /**
     * PersonType constructor.
     * @param RoleHierarchyInterface $hierarchy
     */
    public function __construct(RoleHierarchyInterface $hierarchy)
    {
        $this->hierarchy = $hierarchy;
    }

    /**
     * buildForm
     * @param FormBuilderInterface $builder
     * @param array $options
     * 21/07/2020 14:19
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $hierarchy = [];
        foreach($this->hierarchy->getAssignableRoleNames($options['user']->getRoles()) as $role)
        {
            $hierarchy[$role] = $role;
        }

        $roles = implode(', ', $this->hierarchy->getReachableRoleNames($options['data']->getSecurityRoles()));
        $locale = ProviderFactory::getRepository(Locale::class)->findOneByCode(ParameterBagHelper::get('locale'));

        $builder
            ->add('person', HiddenEntityType::class,
                [
                    'class' => Person::class,
                ]
            )
            ->add('userHeader', HeaderType::class,
                [
                    'label' => 'Security User Details',
                ]
            )
            ->add('canLogin', ToggleType::class,
                [
                    'label' => 'Can Login?',
                    'visible_by_choice' => 'can_login'
                ]
            )
            ->add('locale', EntityType::class,
                [
                    'label' => 'Personal Locale Override!',
                    'help' => 'The system default is "{name}" and is not available to select.',
                    'help_translation_parameters' => ['{name}' => $locale->getName()],
                    'class' => Locale::class,
                    'choice_label' => 'name',
                    'required' => false,
                    'placeholder' => 'Over ride Locale...',
                    'visible_values' => ['can_login'],
                    'visible_parent' => 'security_user_canLogin',
                    'query_builder' => function(EntityRepository $er) {
                        return $er->createQueryBuilder('l')
                            ->orderBy('l.name','ASC')
                            ->where('l.active = :true')
                            ->andWhere('l.systemDefault = :false')
                            ->setParameters(['true' => true, 'false' => false])
                            ;
                    },
                ]
            )
            ->add('username', TextType::class,
                [
                    'label' => 'User name',
                    'visible_values' => ['can_login'],
                    'required' => false,
                    'visible_parent' => 'security_user_canLogin',
                ]
            )
            ->add('passwordForceReset', ToggleType::class,
                [
                    'label' => 'Force Reset Password',
                    'help' => 'User will be prompted on next login.',
                    'required' => false,
                    'visible_values' => ['can_login'],
                    'visible_parent' => 'security_user_canLogin',
                ]
            )
            ->add('securityRoles', ChoiceType::class,
                [
                    'label' => 'Primary Role',
                    'choices' => $hierarchy,
                    'required' => false,
                    'help' => 'Controls what a user can do and see.',
                    'multiple' => true,
                    'expanded' => true,
                    'placeholder' => 'Please select...',
                    'visible_values' => ['can_login'],
                    'visible_parent' => 'security_user_canLogin',
                    'attr' => [
                        'size' => 4,
                    ],
                ]
            )
            ->add('reachableRoles', ParagraphType::class,
                [
                    'help' => 'parent.roles',
                    'help_translation_parameters' => ['{roles}' => $roles],
                    'visible_values' => ['can_login'],
                    'visible_parent' => 'security_user_canLogin',
                    'wrapper_class' => 'info',
                ]
            )
        ;
        if ($options['user']->isSuperUser() && $options['user']->getId() !== $options['data']->getId()) {
            $builder
                ->add('superUser', ToggleType::class,
                    [
                        'label' => 'Super User',
                        'visible_values' => ['can_login'],
                        'visible_parent' => 'security_user_canLogin',
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
     * 21/07/2020 14:14
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(
            [
                'user',
            ]
        );
        $resolver->setDefaults(
            [
                'translation_domain' => 'Security',
                'data_class' => SecurityUser::class,
            ]
        );
    }

    /**
     * getParent
     * @return string|null
     * 21/07/2020 14:13
     */
    public function getParent()
    {
        return ReactFormType::class;
    }
}
