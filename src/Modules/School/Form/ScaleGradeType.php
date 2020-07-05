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
 * Date: 14/01/2020
 * Time: 15:29
 */
namespace App\Modules\School\Form;

use App\Form\Type\DisplayType;
use App\Form\Type\HiddenEntityType;
use App\Form\Type\ReactFormType;
use App\Form\Type\ToggleType;
use App\Modules\School\Entity\Scale;
use App\Modules\School\Entity\ScaleGrade;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class ScaleGradeType
 * @package App\Modules\School\Form
 * @author Craig Rayner <craig@craigrayner.com>
 */
class ScaleGradeType extends AbstractType
{
    /**
     * buildForm
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('scaleGrade', DisplayType::class,
                [
                    'label' => 'Scale',
                    'help' => 'This value cannot be changed.',
                    'mapped' => false,
                    'data' => $options['data']->getScale()->getName(),
                ]
            )
            ->add('sequenceNumber', HiddenType::class,
                [
                    'data' => $options['data']->getSequenceNumber() === null ? $options['data']->getScale()->getLastGradeSequence() + 1 : $options['data']->getSequenceNumber(),
                ]
            )
            ->add('scale', HiddenEntityType::class,
                [
                    'class' => Scale::class,
                ]
            )
            ->add('value', TextType::class,
                [
                    'label' => 'Value',
                    'help' => 'Must be unique for this Scale',
                ]
            )
            ->add('descriptor', TextType::class,
                [
                    'label' => 'Descriptor',
                ]
            )
            ->add('defaultGrade', ToggleType::class,
                [
                    'label' => 'Is Default Grade?',
                    'help' => 'Preselects this option when using this scale in appropriate contexts.',
                ]
            )
            ->add('submit', SubmitType::class)
        ;
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
     * configureOptions
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'translation_domain' => 'School',
                'data_class' => ScaleGrade::class,
            ]
        );
    }
}