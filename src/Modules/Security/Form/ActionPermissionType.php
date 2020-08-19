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
 * Date: 30/06/2020
 * Time: 09:48
 */
namespace App\Modules\Security\Form;

use App\Form\Transform\SimpleArrayTransformer;
use App\Form\Type\DisplayType;
use App\Form\Type\ParagraphType;
use App\Form\Type\ReactFormType;
use App\Modules\Security\Entity\SecurityRole;
use App\Modules\Security\Util\SecurityHelper;
use App\Modules\System\Entity\Action;
use App\Provider\ProviderFactory;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class ActionPermissionType
 * @package App\Modules\Security\Form
 * @author Craig Rayner <craig@craigrayner.com>
 */
class ActionPermissionType extends AbstractType
{
    /**
     * buildForm
     * @param FormBuilderInterface $builder
     * @param array $options
     * 30/06/2020 09:53
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $roles = SecurityHelper::getHierarchy()->getReachableRoleNames($options['data']->getSecurityRoles());
        $roles = implode(', ', array_unique($roles));


        $builder
            ->add('name', DisplayType::class,
                [
                    'label' => 'Action Name',
                ]
            )
            ->add('description', DisplayType::class,
                [
                    'label' => 'Description',
                ]
            )
            ->add('routeList', DisplayType::class,
                [
                    'label' => 'Route List',
                ]
            )
            ->add('restriction', DisplayType::class,
                [
                    'label' => 'Restriction',
                ]
            )
            ->add('securityRoles', ChoiceType::class,
                [
                    'label' => 'Security Roles',
                    'multiple' => true,
                    'expanded' => true,
                    'attr' => [
                        'size' => 8,
                    ],
                    'choices' => $this->getHierarchy(),
                ]
            )
            ->add('reachableRoles', ParagraphType::class,
                [
                    'help' => 'parent.roles',
                    'help_translation_parameters' => ['{roles}' => $roles],
                    'wrapper_class' => 'info',
                ]
            )
            ->add('submit', SubmitType::class)
        ;
        $builder->get('routeList')->addViewTransformer(new SimpleArrayTransformer(', '));
    }

    /**
     * configureOptions
     * @param OptionsResolver $resolver
     * 30/06/2020 09:49
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'translation_domain' => 'Security',
                'data_class' => Action::class,
            ]
        );
    }

    /**
     * getParent
     * @return string|null
     * 30/06/2020 09:49
     */
    public function getParent()
    {
        return ReactFormType::class;
    }

    /**
     * getHierarchy
     * @return array
     * 28/07/2020 16:12
     */
    public function getHierarchy(): array
    {
        $roles = [];
        foreach (ProviderFactory::getRepository(SecurityRole::class)->findBy([],['category' => 'ASC', 'role' => 'ASC']) as $role)
        {
            $roles[$role->getCategory()][$role->getRole()] = $role->getRole();
        }
        return $roles;
    }
}
