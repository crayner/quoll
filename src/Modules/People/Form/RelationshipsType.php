<?php
/**
 * Created by PhpStorm.
 *
 * kookaburra
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

use App\Modules\People\Form\FamilyRelationshipType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class RelationshipsType
 * @package App\Modules\People\Form
 */
class RelationshipsType extends AbstractType
{
    /**
     * configureOptions
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'translation_domain' => 'People',
                'data_class' => null,
            ]
        );
    }

    /**
     * buildForm
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('relationships', CollectionType::class,
                [
                    'entry_type' => FamilyRelationshipType::class,
                    'allow_add' => false,
                    'allow_delete' => false,
                    'data' => $options['data'],
                ]
            )
            ->add('submit', SubmitType::class,
                [
                    'label' => 'Submit',
                    'attr' => [
                        'style' => 'float: right;'
                    ],
                ]
            )
        ;
    }

    /**
     * getBlockPrefix
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'family_relationships';
    }
}