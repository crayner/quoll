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
 * Date: 5/12/2019
 * Time: 11:22
 */
namespace App\Modules\People\Form;

use App\Form\Type\EnumType;
use App\Form\Type\HiddenEntityType;
use App\Modules\People\Entity\Family;
use App\Modules\People\Entity\FamilyMemberAdult;
use App\Modules\People\Entity\FamilyMemberStudent;
use App\Modules\People\Entity\FamilyRelationship;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class FamilyRelationshipType
 * @package App\Modules\People\Form
 */
class FamilyRelationshipType extends AbstractType
{
    /**
     * buildForm
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('relationship', EnumType::class,
                [
                    'label' => false,
                    'placeholder' => ' ',
                    'attr' => [
                        'class' => '',
                    ],
                    'choice_list_prefix' => 'family.relationship',
                ]
            )
            ->add('adult', HiddenEntityType::class,
                [
                    'label' => false,
                    'class' => FamilyMemberAdult::class,
                ]
            )
            ->add('child', HiddenEntityType::class,
                [
                    'label' => false,
                    'class' => FamilyMemberStudent::class,
                ]
            )
            ->add('family', HiddenEntityType::class,
                [
                    'label' => false,
                    'class' => Family::class,
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
                'translation_domain' => 'People',
                'data_class' => FamilyRelationship::class,
            ]
        );
    }
}