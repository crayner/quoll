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
 * Date: 4/09/2020
 * Time: 09:35
 */
namespace App\Modules\Enrolment\Form;

use App\Form\Type\AutoSuggestEntityType;
use App\Form\Type\DisplayType;
use App\Form\Type\EnumType;
use App\Form\Type\HiddenEntityType;
use App\Form\Type\ReactFormType;
use App\Modules\Enrolment\Entity\CourseClass;
use App\Modules\Enrolment\Entity\CourseClassTutor;
use App\Modules\People\Entity\Person;
use App\Modules\Staff\Entity\Staff;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class CourseClassStaffType
 * @package App\Modules\Enrolment\Form
 * @author Craig Rayner <craig@craigrayner.com>
 */
class CourseClassTutorType extends AbstractType
{
    /**
     * buildForm
     *
     * 4/09/2020 09:39
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('academicYear', DisplayType::class,
                [
                    'label' => 'Academic Year',
                    'help' => 'This value cannot be changed.',
                    'translation_domain' => 'School',
                    'data' => $options['data']->getCourseClass()->getCourse()->getAcademicYear()->getName(),
                    'mapped' => false,
                ]
            )
            ->add('course', DisplayType::class,
                [
                    'label' => 'Course',
                    'help' => 'This value cannot be changed.',
                    'translation_domain' => 'Curriculum',
                    'data' => $options['data']->getCourseClass()->getCourse()->getName(),
                    'mapped' => false,
                ]
            )
            ->add('courseClassName', DisplayType::class,
                [
                    'label' => 'Course Class',
                    'help' => 'This value cannot be changed.',
                    'data' => $options['data']->getCourseClass()->getName(),
                    'mapped' => false,
                ]
            )
            ->add('courseClass', HiddenEntityType::class,
                [
                    'class' => CourseClass::class,
                ]
            )
        ;
        if ($options['data']->getid() === null) {
            $builder
                ->add('staff', AutoSuggestEntityType::class,
                    [
                        'class' => Staff::class,
                        'label' => 'Participant',
                        'choice_label' => 'getFullNameReversed',
                        'placeholder' => 'Search for...',
                        'query_builder' => function(EntityRepository $er) {
                            return $er->createQueryBuilder('s')
                                ->select(['s','p'])
                                ->leftJoin('s.person', 'p')
                                ->where('p.status IN (:statusList)')
                                ->setParameter('statusList', ['Full','Expected'], Connection::PARAM_STR_ARRAY)
                                ->orderBy('p.surname','ASC')
                                ->addOrderBy('p.firstName','ASC')
                            ;
                        },
                    ]
                )
                ->add('role', EnumType::class,
                    [
                        'label' => 'Role',
                        'help' => 'Defaults to type of the selected staff member.',
                        'required' => false,
                        'choice_list_prefix' => 'staff.type',
                        'choice_translation_domain' => 'Staff',
                    ],
                )

            ;
        } else {
            $builder
                ->add('staff', HiddenEntityType::class,
                    [
                        'class' => Staff::class,
                    ]
                )
                ->add('personName', DisplayType::class,
                    [
                        'label' => 'Participant',
                        'help' => 'This value cannot be changed.',
                        'data' => $options['data']->getStaff()->getFullNameReversed(),
                        'mapped' => false,
                    ]
                )
                ->add('role', EnumType::class, 
                    [
                        'label' => 'Role',
                        'help' => 'Defaults to type of the selected staff member.',
                        'required' => false,
                        'choice_list_prefix' => 'staff.type',
                        'choice_translation_domain' => 'Staff',
                    ]
                )
            ;

        }

        $builder
            ->add('submit', SubmitType::class);
    }

    /**
     * configureOptions
     *
     * 4/09/2020 09:39
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'translation_domain' => 'Enrolment',
                'data_class' => CourseClassTutor::class,
            ]
        );
    }

    /**
     * getParent
     *
     * 4/09/2020 09:37
     * @return string|null
     */
    public function getParent()
    {
        return ReactFormType::class;
    }
}
