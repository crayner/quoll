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
 * Date: 1/09/2020
 * Time: 11:26
 */
namespace App\Modules\Enrolment\Form;

use App\Form\Type\DisplayType;
use App\Form\Type\ReactFormType;
use App\Form\Type\ToggleType;
use App\Modules\Enrolment\Entity\CourseClass;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class CourseClassType
 * @package App\Modules\Enrolment\Form
 * @author Craig Rayner <craig@craigrayner.com>
 */
class CourseClassType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('courseDisplay', DisplayType::class,
                [
                    'label' => 'Course Name',
                    'mapped' => false,
                    'data' => $options['data']->getCourse()->getName(),
                ]
            )
            ->add('name', TextType::class,
                [
                    'label' => 'Name',
                    'help' => 'Must be unique for this course',
                ]
            )
            ->add('abbreviation', TextType::class,
                [
                    'label' => 'Abbreviation',
                    'help' => 'Must be unique for this course',
                ]
            )
            ->add('reportable', ToggleType::class,
                [
                    'label' => 'Reportable?',
                    'help' => 'Should this class show in reports?',
                ]
            )
            ->add('attendance', ToggleType::class,
                [
                    'label' => 'Track Attendance?',
                    'help' => 'Should this class allow attendance to be taken?',
                ]
            )
            ->add('submit', SubmitType::class);
    }

    /**
     * configureOptions
     *
     * 1/09/2020 11:28
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'translation_domain' => 'Enrolment',
                'data_class' => CourseClass::class,
            ]
        );
    }

    /**
     * getParent
     *
     * 1/09/2020 11:27
     * @return string|null
     */
    public function getParent()
    {
        return ReactFormType::class;
    }
}
