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
 * Date: 22/12/2019
 * Time: 17:59
 */
namespace App\Modules\School\Form;

use App\Form\Type\HeaderType;
use App\Form\Type\ReactFormType;
use App\Form\Type\ToggleType;
use App\Modules\School\Entity\DaysOfWeek;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\Util\StringUtil;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class DayOfTheWeekType
 * @package App\Modules\School\Form
 * @author Craig Rayner <craig@craigrayner.com>
 */
class DayOfTheWeekType extends AbstractType
{
    /**
     * configureOptions
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'translation_domain' => 'School',
                'data_class' => DaysOfWeek::class,
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
            ->add('name', HiddenType::class)
            ->add('abbreviation', HiddenType::class)
            ->add('id', HiddenType::class)
            ->add('sequenceNumber', HiddenType::class)
            ->add('dayName', HeaderType::class,
                [
                    'label' => $options['data']->getName(),
                    'help' => $options['data']->getAbbreviation(),
                ]
            )
            ->add('schoolDay', ToggleType::class,
                [
                    'label' => 'School Day',
                    'visible_by_choice' => 'id_school_day_' . $options['data']->getAbbreviation(),
                ]
            )
            ->add('schoolOpen', TimeType::class,
                [
                    'label' => 'School Opens',
                    'with_seconds' => false,
                    'input' => 'datetime_immutable',
                    'widget' => 'single_text',
                    'visible_values' => ['id_school_day_' . $options['data']->getAbbreviation()],
                    'visible_parent' => 'day_of_the_week_'.strtolower($options['data']->getName()).'_schoolDay'
                ]
            )
            ->add('schoolStart', TimeType::class,
                [
                    'label' => 'School Starts',
                    'with_seconds' => false,
                    'input' => 'datetime_immutable',
                    'widget' => 'single_text',
                    'visible_values' => ['id_school_day_' . $options['data']->getAbbreviation()],
                    'visible_parent' => 'day_of_the_week_'.strtolower($options['data']->getName()).'_schoolDay'
                ]
            )
            ->add('schoolEnd', TimeType::class,
                [
                    'label' => 'School Ends',
                    'with_seconds' => false,
                    'input' => 'datetime_immutable',
                    'widget' => 'single_text',
                    'visible_values' => ['id_school_day_' . $options['data']->getAbbreviation()],
                    'visible_parent' => 'day_of_the_week_'.strtolower($options['data']->getName()).'_schoolDay'
                ]
            )
            ->add('schoolClose', TimeType::class,
                [
                    'label' => 'School Closes',
                    'with_seconds' => false,
                    'input' => 'datetime_immutable',
                    'widget' => 'single_text',
                    'visible_values' => ['id_school_day_' . $options['data']->getAbbreviation()],
                    'visible_parent' => 'day_of_the_week_'.strtolower($options['data']->getName()).'_schoolDay'
                ]
            )
            ->add('submit', SubmitType::class)
        ;
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['id'] = 'day_of_the_week_' . strtolower($options['data']->getName());
        $view->vars['name'] = $view->vars['id'];
        $view->vars['full_name'] = $view->vars['id'];
        $view->vars['unique_block_prefix'] = '_' . $view->vars['id'];
    }
}