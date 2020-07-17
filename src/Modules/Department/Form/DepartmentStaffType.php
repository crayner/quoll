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
 * Date: 3/01/2020
 * Time: 09:16
 */
namespace App\Modules\Department\Form;

use App\Form\Type\AutoSuggestEntityType;
use App\Form\Type\DisplayType;
use App\Form\Type\EnumType;
use App\Form\Type\HeaderType;
use App\Form\Type\HiddenEntityType;
use App\Form\Type\ReactFormType;
use App\Modules\Department\Entity\Department;
use App\Modules\People\Entity\Person;
use App\Modules\Department\Entity\DepartmentStaff;
use App\Modules\People\Repository\PersonRepository;
use App\Modules\Staff\Entity\Staff;
use App\Provider\ProviderFactory;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class DepartmentStaffType
 * @package App\Modules\Department\Form
 * @author Craig Rayner <craig@craigrayner.com>
 */
class DepartmentStaffType extends AbstractType
{
    /**
     * buildForm
     * @param FormBuilderInterface $builder
     * @param array $options
     * 4/06/2020 16:07
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('staffTitle', HeaderType::class,
                [
                    'label' => $options['data']->getId() === null ? 'Add Staff to Department' : 'Edit Department Staff Role',
                ]
            )
            ->add('departmentDisplay', DisplayType::class,
                [
                    'label' => 'Department',
                    'help' => 'Value locked.',
                    'mapped' => false,
                    'data' => $options['data']->getDepartment()->getName(),
                ]
            )
            ->add('department', HiddenEntityType::class,
                [
                    'class' => Department::class,
                ]
            )
        ;
        if ($options['data']->getId() !== null) {
            $builder
                ->add('personDisplay', DisplayType::class,
                    [
                        'label' => 'Staff Name',
                        'help' => 'Value locked.',
                        'mapped' => false,
                        'data' => $options['data']->getStaff()->getPerson()->getFullNameReversed(),
                    ]
                )
                ->add('staff', HiddenEntityType::class,
                    [
                        'class' => Staff::class,
                    ]
                )
            ;
        } else {
            $builder
                ->add('staff', AutoSuggestEntityType::class,
                    [
                        'label' => 'Staff Name',
                        'class' => Staff::class,
                        'choice_label' => 'fullNameReversed',
                        'placeholder' => 'Type a name...',
                        'data' => $options['data']->getStaff() ?? null,
                        'query_builder' => ProviderFactory::getRepository(Staff::class)->getStaffQueryBuilder(),
                    ]
                )
            ;
        }
        $builder
            ->add('role', EnumType::class,
                [
                    'label' => 'Role',
                    'placeholder' => 'Please select...',
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
        $resolver->setDefaults(
            [
                'translation_domain' => 'Department',
                'data_class' => DepartmentStaff::class,
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