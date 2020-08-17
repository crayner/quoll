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
 * Date: 17/01/2020
 * Time: 13:46
 */
namespace App\Modules\Attendance\Form;

use App\Form\Type\EnumType;
use App\Form\Type\ReactFormType;
use App\Form\Type\ToggleType;
use App\Modules\Attendance\Entity\AttendanceCode;
use App\Modules\Security\Manager\RoleHierarchy;
use App\Util\TranslationHelper;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class AttendanceCodeType
 * @package App\Modules\Attendance\Form
 * @author Craig Rayner <craig@craigrayner.com>
 */
class AttendanceCodeType extends AbstractType
{
    /**
     * @var array|string[]
     */
    private $choices;

    /**
     * AttendanceCodeType constructor.
     * @param RoleHierarchy $hierarchy
     */
    public function __construct(RoleHierarchy $hierarchy)
    {
        $this->choices = [];
        foreach ($hierarchy::getCategoryList() as $item) {
            if ($item === 'System') continue;
            $choices = [];
            foreach ($hierarchy::getCategoryRoles($item) as $role) {
                if ($role === 'ROLE_USER') continue;
                $choices[TranslationHelper::translate($role, [], 'Security')] = $role;
            }
            $this->choices[TranslationHelper::translate($item, [], 'Security')] = $choices;
        }
    }

    /**
     * buildForm
     * @param FormBuilderInterface $builder
     * @param array $options
     * 13/06/2020 08:49
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class,
                [
                    'label' => 'Name',
                    'help' => 'Must be unique',
                    'translation_domain' => 'messages',
                ]
            )
            ->add('code', TextType::class,
                [
                    'label' => 'Code',
                    'help' => 'Must be unique',
                ]
            )
            ->add('direction', EnumType::class,
                [
                    'label' => 'Direction',
                ]
            )
            ->add('Scope', EnumType::class,
                [
                    'label' => 'Scope',
                ]
            )
            ->add('active', ToggleType::class,
                [
                    'label' => 'Active',
                ]
            )
            ->add('reportable', ToggleType::class,
                [
                    'label' => 'Reportable',
                ]
            )
            ->add('future', ToggleType::class,
                [
                    'label' => 'Allow Future Use',
                    'help' => 'Can this code be used in Set Future Absence?'
                ]
            )
            ->add('securityRoles', ChoiceType::class,
                [
                    'label' => 'Available to Roles',
                    'help' => 'role_multiple_select',
                    'data' => $options['data']->getSecurityRoles(),
                    'choices' => $this->choices,
                    'multiple' => true,
                    'expanded' => true,
                    'choice_translation_domain' => 'Security',
                ]
            )
            ->add('submit', SubmitType::class)
        ;
    }

    /**
     * getParent
     * @return string|null
     */
    public function getParent()
    {
        return ReactFormType::class;
    }

    /**
     * configureOptions
     * @param OptionsResolver $resolver
     * 13/06/2020 08:49
     */
    public function configureOptions(OptionsResolver $resolver)
    {
       $resolver->setDefaults(
           [
               'translation_domain' => 'Attendance',
               'data_class' => AttendanceCode::class,
           ]
       );
    }
}