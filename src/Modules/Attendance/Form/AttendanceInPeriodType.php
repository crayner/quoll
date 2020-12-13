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
 * Date: 20/11/2020
 * Time: 14:12
 */
namespace App\Modules\Attendance\Form;

use App\Modules\Timetable\Entity\TimetablePeriodClass;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class AttendanceInPeriodType
 *
 * 20/11/2020 14:13
 * @package App\Modules\Attendance\Form
 * @author Craig Rayner <craig@craigrayner.com>
 */
class AttendanceInPeriodType extends AbstractType
{
    /**
     * buildForm
     *
     * 25/10/2020 08:25
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
    }

    /**
     * configureOptions
     *
     * 25/10/2020 08:21
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults(
                [
                    'translation_domain' => 'Attendance',
                    'data_class' => TimetablePeriodClass::class,
                    'is_roll_group' => false,
                ]
            )
            ->setAllowedTypes('is_roll_group', ['boolean'])
        ;

    }

    /**
     * getParent
     *
     * 25/10/2020 08:19
     * @return string|null
     */
    public function getParent()
    {
        return FormType::class;
    }
}
