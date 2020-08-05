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
 * Date: 5/08/2020
 * Time: 08:16
 */
namespace App\Modules\Timetable\Form;

use App\Form\Type\DisplayType;
use App\Form\Type\ReactFormType;
use App\Modules\Timetable\Entity\TimetableColumn;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class TimetableColumnDuplicatePeriodsType
 * @package App\Modules\Timetable\Form
 * @author Craig Rayner <craig@craigrayner.com>
 */
class TimetableColumnDuplicatePeriodsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('columnName', DisplayType::class,
                [
                    'label' => 'Source Timetable Column',
                    'data' => $options['data']->getName(),
                    'mapped' => false,
                ]
            )
            ->add('timetableColumn', EntityType::class,
                [
                    'label' => 'Duplicate to this Column',
                    'help' => 'Only timetable columns with no periods attached are available as targets to duplicate the source.',
                    'submit_on_change' => true,
                    'mapped' => false,
                    'class' => TimetableColumn::class,
                    'choice_label' => 'name',
                    'placeholder' => 'Please select...',
                    'query_builder' => function(EntityRepository $er) {
                        return $er->createQueryBuilder('c')
                            ->orderBy('c.name','ASC')
                            ->leftJoin('c.timetableColumnPeriods', 'r')
                            ->where('r.id IS NULL');
                    },
                ]
            )
        ;
    }

    /**
     * configureOptions
     * @param OptionsResolver $resolver
     * 5/08/2020 08:18
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'translation_domain' => 'Timetable',
                'data_class' => TimetableColumn::class,
            ]
        );
    }

    /**
     * getParent
     * @return string|null
     * 5/08/2020 08:16
     */
    public function getParent()
    {
        return ReactFormType::class;
    }
}
