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
 * Date: 2/10/2020
 * Time: 16:03
 */
namespace App\Modules\Attendance\Form;

use App\Form\Type\AutoSuggestEntityType;
use App\Form\Type\HeaderType;
use App\Form\Type\HiddenEntityType;
use App\Form\Type\ParagraphType;
use App\Form\Type\ReactCollectionType;
use App\Form\Type\ReactDateType;
use App\Form\Type\ReactFormType;
use App\Manager\StatusManager;
use App\Modules\Attendance\Entity\AttendanceCode;
use App\Modules\Attendance\Manager\AttendanceByClassManager;
use App\Modules\Attendance\Manager\TeacherManager;
use App\Modules\Enrolment\Entity\CourseClass;
use App\Modules\Enrolment\Validator\CourseClassAccess;
use App\Modules\School\Util\AcademicYearHelper;
use App\Modules\System\Manager\SettingFactory;
use App\Modules\Timetable\Entity\TimetablePeriodClass;
use App\Modules\Timetable\Validator\SchoolDay;
use App\Provider\ProviderFactory;
use App\Util\TranslationHelper;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Range;

/**
 * Class AttendanceByClassType
 * @package App\Modules\Attendance\Form
 * @author Craig Rayner <craig@craigrayner.com>
 */
class AttendanceByClassType extends AbstractType
{
    /**
     * @var StatusManager
     */
    private StatusManager $manager;

    /**
     * AttendanceByClassType constructor.
     *
     * @param TeacherManager $teacherManager
     * @param StatusManager $manager
     */
    public function __construct(TeacherManager $teacherManager, StatusManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * buildForm
     *
     * 3/11/2020 13:46
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if ($options['data']->isSelectionValid()) {
            if ($options['data']->isSchoolDate()) {
                if (!$options['data']->isClassDate()) {
                    $builder
                        ->add('notClassDate', ParagraphType::class,
                            [
                                'help' => TranslationHelper::translate('"course_class" is not timetabled to run on the "_date_". Attendance may still be taken for this class, however it currently falls outside the regular schedule for this class.', ['_date_' => $options['data']->getDate()->format('D, jS M/Y'), 'course_class' => $options['data']->getCourseClass()->getFullName()], 'Enrolment'),
                                'required' => false,
                                'wrapper_class' => 'warning',
                            ]
                        );
                }
                if (!$options['data']->isAttendanceTaken()) {
                    $builder
                        ->add('attendanceTaken', ParagraphType::class,
                            [
                                'help' => TranslationHelper::translate('Attendance has not been taken for "course_class" yet for the "_date_". The entries below are a best-guess based on defaults and information put into the system in advance, not actual data.', ['_date_' => $options['data']->getDate()->format('D, jS M/Y'), 'course_class' => $options['data']->getCourseClass()->getFullName()], 'Attendance'),
                                'required' => false,
                                'wrapper_class' => 'error',
                            ]
                        );
                } else if (!$options['data']->isAllAttendanceTaken()) {
                    $builder
                        ->add('attendanceTaken', ParagraphType::class,
                            [
                                'help' => TranslationHelper::translate('The class "course_class" occurs more that once in the timetable for "_date_". Attendance records are not available for all periods that "course_class" is held on this day. Data for missing periods is inferred by the data available.', ['_date_' => $options['data']->getDate()->format('D, jS M/Y'), 'course_class' => $options['data']->getCourseClass()->getFullName()], 'Attendance'),
                                'required' => false,
                                'wrapper_class' => 'warning',
                            ]
                        );
                }
                if ($options['data']->isAttendanceTaken()) {
                    $builder
                        ->add('attendanceHistory', ParagraphType::class,
                            [
                                'help' => TranslationHelper::translate('course_class_attendance_history', $options['data']->getAttendanceHistory(), 'Attendance'),
                                'required' => false,
                                'wrapper_class' => 'success',
                            ]
                        );
                }
                $defaultAttendanceCode = ProviderFactory::getRepository(AttendanceCode::class)->findOneByName(SettingFactory::getSettingManager()->get('Attendance', 'defaultClassAttendanceType', 'Present'));
                $builder
                    ->add('courseName', HeaderType::class,
                        [
                            'label' => $options['data']->getCourseClass()->getFullName(),
                        ]
                    )
                    ->add('students', ReactCollectionType::class,
                        [
                            'entry_type' => AttendanceForStudentType::class,
                            'element_delete_route' => false,
                            'special' => 'display_student_attendance',
                            'special_data' => [
                                'default_code' => $defaultAttendanceCode->getId(),
                                'inOrOut' => ProviderFactory::getRepository(AttendanceCode::class)->findInOrOut(),
                                'dateChoices' => $options['data']->getPeriodChoices(),
                                'dateChoiceName' => 'periodClass',
                            ],
                            'row_style' => 'single',
                            'entry_options' => [
                                'is_roll_group' => false,
                            ],
                        ]
                    )
                    ->add('changeAll', AttendanceRollGroupChangeAllType::class);
            } else {
                $builder
                    ->add('notSchoolDate', ParagraphType::class,
                        [
                            'help' => TranslationHelper::translate('not_a_school_day', ['{date}' => $options['data']->getDate()->format('D, jS M/Y')], 'Attendance'),
                            'required' => false,
                            'wrapper_class' => 'error',
                        ]
                    );
            }
        }
        $builder
            ->add('courseClass', AutoSuggestEntityType::class,
                [
                    'class' => CourseClass::class,
                    'label' => 'Course Class',
                    'choice_label' => 'getFullName',
                    'query_builder' => TeacherManager::getClassListQuery(),
                    'constraints' => [
                        new notBlank(),
                        new CourseClassAccess(),
                    ],
                ]
            )
            ->add('date', ReactDateType::class,
                [
                    'label' => 'Date',
                    'input' => 'datetime_immutable',
                    'attr' => [
                        'min' => AcademicYearHelper::getCurrentAcademicYear()->getFirstDay()->format('Y-m-d'),
                        'max' => date('Y-m-d'),
                    ],
                    'constraints' => [
                        new NotBlank(),
                        new SchoolDay(),
                        new Range(
                            [
                                'min' => AcademicYearHelper::getCurrentAcademicYear()->getFirstDay()->format('Y-m-d'),
                                'max' => date('Y-m-d'),
                            ]
                        ),
                    ],
                ]
            )
        ;
        if ($options['data']->isSelectionValid()) {
            if ($options['data']->countPeriodClasses() === 1) {
                $builder
                    ->add('periodClasses', EntityType::class,
                        [
                            'label' => 'Period Name',
                            'help' => 'This data is fixed as it is the only choice.',
                            'class' => TimetablePeriodClass::class,
                            'choice_label' => 'getPeriodName',
                            'choices' => $options['data']->findPeriodClasses(),
                            'multiple' => true,
                            'expanded' => true,
                            'disabled' => true,
                        ]
                    )
                ;
            } else if ($options['data']->countPeriodClasses() > 1) {
                $builder
                    ->add('periodClasses', EntityType::class,
                        [
                            'label' => 'Period Name',
                            'help' => 'Selecting none is the same as selecting all. Use Control, Command and/or Shift to select multiple.',
                            'class' => TimetablePeriodClass::class,
                            'choice_label' => 'getPeriodName',
                            'choices' => $options['data']->findPeriodClasses(),
                            'multiple' => true,
                            'expanded' => true,
                        ]
                    )
                ;
            } else {
                $builder
                    ->add('periodClasses', HiddenEntityType::class,
                        [
                            'class' => TimetablePeriodClass::class,
                        ]
                    )
                ;
            }
        }

        $builder
            ->add('submit',SubmitType::class);
    }

    /**
     * configureOptions
     *
     * 2/10/2020 16:11
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'translation_domain' => 'Attendance',
                'data_class' => AttendanceByClassManager::class,
            ]
        );
    }

    /**
     * getParent
     *
     * 2/10/2020 16:10
     * @return string|null
     */
    public function getParent()
    {
        return ReactFormType::class;
    }

    /**
     * getStatusManager
     *
     * 12/11/2020 11:19
     * @return StatusManager
     */
    public function getStatusManager(): StatusManager
    {
        return $this->manager;
    }

}
