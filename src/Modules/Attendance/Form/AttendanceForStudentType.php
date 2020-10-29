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
 * Date: 25/10/2020
 * Time: 08:18
 */
namespace App\Modules\Attendance\Form;

use App\Form\Type\DisplayType;
use App\Form\Type\EnumType;
use App\Form\Type\HiddenEntityType;
use App\Form\Type\SpecialType;
use App\Modules\Attendance\Entity\AttendanceCode;
use App\Modules\Attendance\Entity\AttendanceStudent;
use App\Modules\Student\Entity\Student;
use App\Provider\ProviderFactory;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class AttendanceForStudentType
 *
 * 25/10/2020 08:19
 * @package App\Modules\Attendance\Form
 * @author Craig Rayner <craig@craigrayner.com>
 */
class AttendanceForStudentType extends AbstractType
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
        $builder
            ->add('student', HiddenEntityType::class,
                [
                    'class' => Student::class,
                ]
            )
            ->add('studentName', DisplayType::class)
            ->add('personalImage', DisplayType::class)
            ->add('absenceCount', DisplayType::class)
            ->add('code', EntityType::class,
                [
                    'class' => AttendanceCode::class,
                    'choice_label' => 'name',
                    'label' => false,
                    'query_builder' => function(EntityRepository $er) {
                        return $er->createQueryBuilder('c')
                            ->where('c.active = :true')
                            ->setParameter('true', true)
                            ->orderBy('c.sortOrder', 'ASC')
                        ;
                    }
                ]
            )
            ->add('reason', EnumType::class,
                [
                    'label' => false,
                    'choice_translation_domain' => false,
                    'placeholder' => ' ',
                ]
            )
            ->add('comment', TextType::class,
                [
                    'label' => false,
                ]
            )
            ->add('previousDays',HiddenType::class,
                [
                    'label' => false,
                    'constraints' => [],
                    'required' => false,
                    'special_name' => 'AttendanceSummary',
                ]
            )
            ->add('inOrOut',HiddenType::class,
                [
                    'mapped' => false,
                    'data' => ProviderFactory::getRepository(AttendanceCode::class)->findInOrOut(),
                ]
            )
            ->add('submit', SubmitType::class ,
                [
                    'label' => 'Save Attendance',
                ]
            )
        ;
    }

    /**
     * configureOptions
     *
     * 25/10/2020 08:21
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'translation_domain' => 'Attendance',
                'data_class' => AttendanceStudent::class,
            ]
        );
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