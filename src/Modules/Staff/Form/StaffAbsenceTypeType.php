<?php
/**
 * Created by PhpStorm.
 *
  * Project: Kookaburra
 * Build: Quoll
 *
 * (c) 2019 Craig Rayner <craig@craigrayner.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * User: craig
 * Date: 3/12/2019
 * Time: 13:05
 */
namespace App\Modules\Staff\Form;

use App\Form\Type\ReactFormType;
use App\Form\Type\SimpleArrayType;
use App\Form\Type\ToggleType;
use App\Modules\Staff\Entity\StaffAbsenceType;
use App\Modules\Staff\Form\Transform\AbsenceTypeTransform;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class StaffAbsenceTypeType
 * @package App\Modules\Staff\Form
 * @author Craig Rayner <craig@craigrayner.com>
 */
class StaffAbsenceTypeType extends AbstractType
{
    /**
     * configureOptions
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'translation_domain' => 'Staff',
                'data_class' => StaffAbsenceType::class,
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
     * buildForm
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class,
                [
                    'label' => 'Name',
                    'help' => 'Must be unique',
                ]
            )
            ->add('abbreviation', TextType::class,
                [
                    'label' => 'Abbreviation',
                    'help' => 'Must be unique',
                ]
            )
            ->add('active', ToggleType::class,
                [
                    'label' => 'Active',
                ]
            )
            ->add('requiresApproval', ToggleType::class,
                [
                    'label' => 'Requires Approval',
                    'help' => 'If enabled, absences of this type must be submitted for approval before they are accepted.',
                ]
            )
            ->add('reasons', SimpleArrayType::class,
                [
                    'label' => 'Reasons',
                    'help' => 'An optional list of reasons which are available when submitting this type of absence',
                ]
            )
            ->add('sequenceNumber', IntegerType::class,
                [
                    'label' => 'Sequence Number',
                    'help'  => 'Must be unique. Leave as zero (0) to let the system correctly allocate a sequence.',
                ]
            )
            ->add('submit', SubmitType::class,
                [
                    'label' => 'Submit',
                ]
            )
            ->addModelTransformer(new AbsenceTypeTransform())
        ;
    }
}