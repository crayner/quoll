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
 * Date: 11/09/2020
 * Time: 07:52
 */
namespace App\Modules\Enrolment\Form;

use App\Form\Type\EnumType;
use App\Form\Type\ReactFormType;
use App\Modules\Enrolment\Entity\CourseClass;
use App\Modules\Enrolment\Manager\Hidden\IndividualEnrolment;
use App\Provider\ProviderFactory;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\Constraints\Count;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Class IndividualEnrolmentClassListType
 * @package App\Modules\Enrolment\Form
 * @author Craig Rayner <craig@craigrayner.com>
 */
class IndividualEnrolmentClassListType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('classes', EntityType::class,
                [
                    'class' => CourseClass::class,
                    'label' => 'Classes',
                    'choice_label' => 'getClassNameWithCount',
                    'help' => 'Use Control, Command and/or Shift to select multiple.',
                    'attr' => [
                        'size' => 8,
                    ],
                    'multiple' => true,
                    'choices' => ProviderFactory::create(CourseClass::class)->getIndividualClassChoices($options['person']),
                    'constraints' => [
                        new Count(['min' => 1]),
                    ],
                ]
            )
            ->add('role', EnumType::class,
                [
                    'label' => 'Role',
                    'choice_list_prefix' => 'courseclassperson.role',
                    'placeholder' => 'Please select...',
                    'data' => $options['person']->isStudent() ? 'Student' : 'Teacher',
                    'constraints' => [
                        new Choice(['choices' => IndividualEnrolment::getRoleList()]),
                        new NotBlank(),
                    ],
                ]
            )
            ->add('submit', SubmitType::class);
    }

    /**
     * configureOptions
     *
     * 11/09/2020 08:00
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults(
                [
                    'translation_domain' => 'Enrolment',
                    'data_class' => IndividualEnrolment::class,
                ]
            )
            ->setRequired(
                [
                    'person',
                ]
            )
       ;
    }

    /**
     * getParent
     *
     * 11/09/2020 07:52
     * @return string|null
     */
    public function getParent()
    {
        return ReactFormType::class;
    }
}
