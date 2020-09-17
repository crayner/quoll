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

use App\Form\Type\EnumType;
use App\Form\Type\ReactFormType;
use App\Modules\Enrolment\Entity\CourseClass;
use App\Modules\Enrolment\Entity\CourseClassStudent;
use App\Modules\People\Entity\Person;
use App\Provider\ProviderFactory;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Count;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Class CourseClassPersonType
 * @package App\Modules\Enrolment\Form
 * @author Craig Rayner <craig@craigrayner.com>
 */
class MultipleCourseClassPersonType extends AbstractType
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
            ->add('people', EntityType::class,
                [
                    'label' => 'Participants',
                    'help' => 'Use Control, Command and/or Shift to select multiple.',
                    'mapped' => false,
                    'multiple' => true,
                    'class' => Person::class,
                    'choice_label' => 'getFullNameReversedWithRollGroup',
                    'placeholder' => 'Please select...',
                    'choices' => ProviderFactory::create(Person::class)->getEnrolmentListByClass($options['data']),
                    'attr' => [
                        'size' => 10,
                    ],
                    'constraints' => [
                        new Count(['min' => 1]),
                    ],
                ]
            )
            ->add('role', EnumType::class,
                [
                    'label' => 'Role',
                    'choice_list_method' => 'getRoleList',
                    'choice_list_class' => CourseClassStudent::class,
                    'choice_list_prefix' => 'courseclassperson.role',
                    'placeholder' => 'Please select...',
                    'mapped' => false,
                    'constraints' => [
                        new NotBlank(),
                    ],
                ]
            )
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
                'data_class' => CourseClass::class,
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
