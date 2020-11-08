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
 * Date: 27/10/2020
 * Time: 17:19
 */
namespace App\Modules\Attendance\Form;

use App\Form\Type\AutoSuggestEntityType;
use App\Form\Type\EnumType;
use App\Form\Type\HeaderType;
use App\Form\Type\ReactDateType;
use App\Form\Type\ReactFormType;
use App\Form\Type\SpecialType;
use App\Modules\Attendance\Entity\AttendanceCode;
use App\Modules\Attendance\Entity\AttendanceStudent;
use App\Modules\Attendance\Manager\AttendanceByRollGroupManager;
use App\Modules\School\Util\AcademicYearHelper;
use App\Modules\Student\Entity\Student;
use App\Modules\Timetable\Validator\SchoolDay;
use App\Provider\ProviderFactory;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Range;

/**
 * Class AttendanceByStudentType
 *
 * 27/10/2020 17:19
 * @package App\Modules\Attendance\Form
 * @author Craig Rayner <craig@craigrayner.com>
 */
class AttendanceByStudentType extends AbstractType
{
    /**
     * buildForm
     *
     * 27/10/2020 17:23
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('chooseStudent', HeaderType::class,
                [
                    'label' => 'Choose Student',
                ]
            )
            ->add('student', AutoSuggestEntityType::class,
                [
                    'label' => 'Student',
                    'class' => Student::class,
                    'choice_label' => 'fullNameReversedWithRollGroup',
                    'placeholder' => 'Please select...',
                    'query_builder' => function(EntityRepository $er) {
                        return $er->createQueryBuilder('s')
                            ->select(['s','p','rg','srg'])
                            ->leftJoin('s.studentRollGroups', 'srg')
                            ->leftJoin('srg.rollGroup', 'rg')
                            ->where('rg.academicYear = :current')
                            ->setParameter('current', AcademicYearHelper::getCurrentAcademicYear())
                            ->leftJoin('s.person', 'p')
                            ->orderBy('p.surname', 'ASC')
                            ->addOrderBy('p.firstName','ASC')
                        ;
                    },
                ]
            )
            ->add('date', ReactDateType::class,
                [
                    'label' => 'Date',
                    'attr' => [
                        'min' => AcademicYearHelper::getCurrentAcademicYear()->getFirstDay()->format('Y-m-d'),
                        'max' => AcademicYearHelper::getCurrentAcademicYear()->getLastDay()->format('Y-m-d'),
                    ],
                    'constraints' => [
                        new NotBlank(),
                        new Range(
                            [
                                'min' => AcademicYearHelper::getCurrentAcademicYear()->getFirstDay()->format('Y-m-d'),
                                'max' => AcademicYearHelper::getCurrentAcademicYear()->getLastDay()->format('Y-m-d'),
                            ]
                        ),
                        new SchoolDay(),
                    ],

                ]
            )
        ;
        $times = AttendanceByRollGroupManager::getDailyTimeList();
        if (count($times) > 1) {
            $builder
                ->add('dailyTime', EnumType::class,
                    [
                        'choice_list_class' => AttendanceByRollGroupManager::class,
                        'label' => 'Attendance (Daily) Record Time',
                        'choice_list_prefix' => false,
                        'placeholder' => 'Please select...',
                        'constraints' => [
                            new NotBlank(),
                            new Choice(['choices' => AttendanceByRollGroupManager::getDailyTimeList()]),
                        ],
                    ]
                )
            ;
        }

        if ($options['studentAccess'] && $options['data']->isValid()) {
            $builder
                ->add('takeAttendance', HeaderType::class,
                    [
                        'label' => 'Take Attendance',
                    ]
                )
                ->add('previousDays', SpecialType::class,
                    [
                        'label' => 'Attendance Summary',
                        'special_name' => 'AttendanceSummary',
                        'special_data' => ProviderFactory::create(AttendanceStudent::class)->getAttendanceDayStatus($options['data'], ['previous' => 5, 'future' => 3]),
                    ]
                )
                ->add('code', EntityType::class,
                    [
                        'class' => AttendanceCode::class,
                        'choice_label' => 'name',
                        'label' => 'Attendance Type',
                        'placeholder' => 'Please select...',
                        'query_builder' => function (EntityRepository $er) {
                            return $er->createQueryBuilder('a')
                                ->where('a.active = :true')
                                ->setParameter('true', true)
                                ->orderBy('a.sortOrder');
                        },
                    ]
                )
                ->add('reason', EnumType::class,
                    [
                        'label' => "Reason",
                        'placeholder' => ' ',
                        'choice_list_prefix' => 'attendance_student.reason',
                        'required' => false,
                    ]
                )
                ->add('comment', TextareaType::class,
                    [
                        'label' => 'Comment',
                        'attr' => [
                            'rows' => 3,
                        ],
                        'required' => false,
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
     * 6/11/2020 14:01
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults(
                [
                    'translation_domain' => 'Attendance',
                    'data_class' => AttendanceStudent::class,
                ]
            )
            ->setRequired(
                [
                    'studentAccess',
                ]
            )
            ->setAllowedTypes('studentAccess', ['boolean'])
        ;

    }

    /**
     * getParent
     *
     * 27/10/2020 17:19
     * @return string|null
     */
    public function getParent()
    {
        return ReactFormType::class;
    }
}
