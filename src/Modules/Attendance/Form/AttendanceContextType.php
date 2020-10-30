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
 * Time: 09:47
 */
namespace App\Modules\Attendance\Form;

use App\Form\Type\EnumType;
use App\Form\Type\HeaderType;
use App\Form\Type\ReactFormType;
use App\Form\Type\SimpleArrayType;
use App\Form\Type\ToggleType;
use App\Modules\Attendance\Entity\AttendanceCode;
use App\Modules\System\Form\SettingsType;
use App\Modules\System\Manager\SettingFactory;
use App\Provider\ProviderFactory;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class AttendanceContextType
 * @package App\Modules\Attendance\Form
 * @author Craig Rayner <craig@craigrayner.com>
 */
class AttendanceContextType extends AbstractType
{
    /**
     * buildForm
     * @param FormBuilderInterface $builder
     * @param array $options
     * 12/06/2020 13:58
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('header', HeaderType::class,
                [
                    'label' => 'Context and Defaults',
                ]
            )
            ->add('settings', SettingsType::class,
                [
                    'settings' => [
                        [
                            'scope' => 'Attendance',
                            'name' => 'dailyAttendanceTimes',
                            'entry_type' => SimpleArrayType::class,
                        ],
                        [
                            'scope' => 'Attendance',
                            'name' => 'autoFillDailyTimes',
                            'entry_type' => ToggleType::class,
                        ],
                        [
                            'scope' => 'Attendance',
                            'name' => 'logAttendance',
                            'entry_type' => EnumType::class,
                        ],
                        [
                            'scope' => 'Attendance',
                            'name' => 'countClassAsSchool',
                            'entry_type' => ToggleType::class,
                        ],
                        [
                            'scope' => 'Attendance',
                            'name' => 'crossFillClasses',
                            'entry_type' => ToggleType::class,
                        ],
                        [
                            'scope' => 'Attendance',
                            'name' => 'defaultRollGroupAttendanceType',
                            'entry_type' => EnumType::class,
                        ],
                        [
                            'scope' => 'Attendance',
                            'name' => 'defaultClassAttendanceType',
                            'entry_type' => EnumType::class,
                            'entry_options' => [
                                'choice_list_prefix' => 'attendancecontexttype.attendance__defaultrollgroupattendancetype',
                            ],
                        ],
                    ],
                ]
            )
            ->add('submit', SubmitType::class,
                [
                    'label' => 'Submit',
                    'translation_domain' => 'messages',
                ]
            )
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
                'translation_domain' => 'Attendance',
                'data_class' => null,
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

    /**
     * getAttendanceTypeList
     * @return array
     */
    public static function getAttendanceTypeList(): array
    {
        $result = ProviderFactory::getRepository(AttendanceCode::class)->findAttendanceTypeList();
        $x = [];
        foreach($result as $w)
            $x[] = $w['name'];
        return $x;
    }
    /**
     * @var array
     */
    private static array $logAttendanceList = [
        'All',
        'Daily Only',
        'Class Only',
        'None',
    ];

    /**
     * getLogAttendanceList
     *
     * 30/10/2020 11:40
     * @return array
     */
    public static function getLogAttendanceList(): array
    {
        return self::$logAttendanceList;
    }

}