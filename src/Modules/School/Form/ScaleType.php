<?php
/**
 * Created by PhpStorm.
 *
 * Quoll
 * (c) 2020 Craig Rayner <craig@craigrayner.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * User: craig
 * Date: 10/01/2020
 * Time: 09:11
 */
namespace App\Modules\School\Form;

use App\Form\Type\ReactFormType;
use App\Form\Type\ToggleType;
use App\Modules\School\Entity\Scale;
use App\Modules\School\Entity\ScaleGrade;
use App\Util\TranslationHelper;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class ScaleType
 * @package Kookaburra\SchoolAdmin\Form
 */
class ScaleType extends AbstractType
{
    /**
     * buildForm
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $scale = $options['data'];
        $builder
            ->add('name', TextType::class,
                [
                    'label' => 'Name',
                    'help' => 'Must be unique',
                    'translation_domain' => 'messages',
                ]
            )
            ->add('abbreviation', TextType::class,
                [
                    'label' => 'Abbreviation',
                    'help' => 'Must be unique',
                    'translation_domain' => 'messages',
                ]
            )
            ->add('usageInfo', TextType::class,
                [
                    'label' => 'Usage',
                    'help' => 'Brief description of how scale is used.',
                ]
            )
            ->add('active', ToggleType::class,
                [
                    'label' => 'Active',
                ]
            )
            ->add('numericOnly', ToggleType::class,
                [
                    'label' => 'Numeric',
                    'help' => 'Does this scale use only numeric grades? Note, grade "Incomplete" is exempt.',
                ]
            )
        ;
        if ($scale->getId() !== null)
            $builder
                ->add('lowestAcceptable', EntityType::class,
                    [
                        'label' => 'Lowest Acceptable',
                        'help' => 'This is the lowest grade a student can get without being unsatisfactory.',
                        'class' => ScaleGrade::class,
                        'placeholder' => TranslationHelper::translate('Please select...',[],'messages'),
                        'choice_translation_domain' => false,
                        'data' => $scale->getLowestAcceptable(),
                        'choice_label' => 'value',
                        'query_builder' => function(EntityRepository $er) use ($scale) {
                            return $er->createQueryBuilder('g')
                                ->where('g.scale = :scale')
                                ->setParameter('scale', $scale)
                                ->orderBy('g.sequenceNumber', 'ASC')
                            ;
                        },
                    ]
                );
        $builder
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
                'data_class' => Scale::class,
            ]
        );
    }
}