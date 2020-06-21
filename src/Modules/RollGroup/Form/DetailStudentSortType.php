<?php
/**
 * Created by PhpStorm.
 *
 * kookaburra
 * (c) 2020 Craig Rayner <craig@craigrayner.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * User: craig
 * Date: 3/01/2020
 * Time: 20:06
 */
namespace App\Modules\RollGroup\Form;

use App\Form\Type\EnumType;
use App\Form\Type\ReactFormType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class DetailStudentSortType
 * @package App\Modules\RollGroup\Form
 * @author Craig Rayner <craig@craigrayner.com>
 */
class DetailStudentSortType extends AbstractType
{
    /**
     * buildForm
     * @param FormBuilderInterface $builder
     * @param array $options
     * 17/06/2020 13:24
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('sortBy', EnumType::class,
                [
                    'label' => 'Sort By',
                    'choice_list_class' => DetailStudentSortType::class,
                    'choice_list_method' => 'getSortList',
                    'submit_on_change' => true,
                ]
            )
            ->add('confidential', CheckboxType::class,
                [
                    'label' => 'Show Confidential Data',
                    'submit_on_change' => true,
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
                'translation_domain' => 'RollGroup',
                'data_class' => null,
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
     * getSortList
     * @return array
     */
    public static function getSortList()
    {
        return ['rollOrder', 'surname', 'preferredName'];
}
}