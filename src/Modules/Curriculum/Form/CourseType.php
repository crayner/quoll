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
 * Date: 31/08/2020
 * Time: 15:37
 */
namespace App\Modules\Curriculum\Form;

use App\Form\Type\DisplayType;
use App\Form\Type\ReactFormType;
use App\Form\Type\ToggleType;
use App\Modules\Curriculum\Entity\Course;
use App\Modules\Department\Entity\Department;
use App\Modules\School\Entity\YearGroup;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class CourseType
 * @package App\Modules\Curriculum\Form
 * @author Craig Rayner <craig@craigrayner.com>
 */
class CourseType extends AbstractType
{
    /**
     * buildForm
     *
     * 31/08/2020 16:14
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('displayAcademicYear', DisplayType::class,
                [
                    'label' => 'Academic Year',
                    'data' => $options['data']->getAcademicYear()->getName(),
                    'mapped' => false,
                ]
            )
            ->add('department', EntityType::class,
                [
                    'label' => 'Learning Area',
                    'choice_label' => 'name',
                    'placeholder' => 'Please select...',
                    'query_builder' => function(EntityRepository $er) {
                        return $er->createQueryBuilder('d')
                            ->where('d.type = :type')
                            ->setParameter('type', 'Learning Area')
                            ->orderBy('d.name', 'ASC')
                            ;
                    },
                    'class' => Department::class,
                ]
            )
            ->add('name', TextType::class,
                [
                    'label' => 'Name',
                    'help' => 'Must be unique in the academic year',
                ]
            )
            ->add('abbreviation', TextType::class,
                [
                    'label' => 'Abbreviation',
                    'help' => 'Must be unique in the academic year',
                ]
            )
            ->add('orderBy', TextType::class,
                [
                    'label' => 'Order',
                    'help' => 'Used to adjust arrangement of courses in reports.'
                ]
            )
            ->add('map', ToggleType::class,
                [
                    'label' => 'Include in Curriculum Map',
                ]
            )
            ->add('yearGroups', EntityType::class,
                [
                    'label' => 'Year Groups',
                    'help' => 'Allow enrolment in these Year Groups.',
                    'class' => YearGroup::class,
                    'choice_label' => 'name',
                    'multiple' => true,
                    'expanded' => true,
                    'query_builder' => function(EntityRepository $er) {
                        return $er->createQueryBuilder('yg')
                            ->orderBy('yg.sortOrder', 'ASC')
                        ;
                    },
                ]
            )
            ->add('submit', SubmitType::class);
    }

    /**
     * configureOptions
     *
     * 31/08/2020 15:39
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'translation_domain' => 'Curriculum',
                'data_class' => Course::class,
            ]
        );
    }

    /**
     * getParent
     *
     * 31/08/2020 15:38
     * @return string|null
     */
    public function getParent()
    {
        return ReactFormType::class;
    }
}
