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
 * Date: 28/10/2020
 * Time: 17:08
 */

namespace App\Modules\Attendance\Form;


use App\Form\Type\EnumType;
use App\Modules\Attendance\Entity\AttendanceCode;
use App\Modules\Attendance\Entity\AttendanceStudent;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AttendanceRollGroupChangeAllType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('code', EntityType::class,
                [
                    'class' => AttendanceCode::class,
                    'choice_label' => 'name',
                    'label' => false,
                    'placeholder' => 'Change all too...',
                    'query_builder' => function(EntityRepository $er) {
                        return $er->createQueryBuilder('c')
                            ->where('c.active = :true')
                            ->setParameter('true', true)
                            ->orderBy('c.sortOrder', 'ASC')
                        ;
                    },
                ]
            )
            ->add('reason', EnumType::class,
                [
                    'label' => false,
                    'choice_translation_domain' => false,
                    'placeholder' => ' ',
                    'choice_list_class' => AttendanceStudent::class,
                    'choice_list_method' => 'getReasonList',
                ]
            )
            ->add('comment', TextType::class,
                [
                    'label' => false,
                ]
            )
            ->add('changeAll', SubmitType::class)
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
                'data_class' => null,
                'special' => 'AttendanceRollGroupChangeAll',
                'mapped' => false,
                'row_style' => 'special',
                'special_name' => 'AttendanceRollGroupChangeAll',
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