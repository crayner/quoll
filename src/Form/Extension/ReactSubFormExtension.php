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
 * Date: 28/08/2019
 * Time: 13:59
 */

namespace App\Form\Extension;

use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class ReactSubFormExtension
 * @package App\Form\Extension
 */
class ReactSubFormExtension extends AbstractTypeExtension
{
    /**
     * getExtendedTypes
     * @return array|iterable
     */
    public static function getExtendedTypes(): iterable
    {
        return [
            ButtonType::class,
            FormType::class,
            SubmitType::class,
        ];
    }

    /**
     * configureOptions
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'on_change'     => false,
                'on_click'      => false,
                'on_blur'       => false,
                'on_key_press'  => false,
                'panel'         => false,
                'row_style'     => 'standard',
                'column_attr'   => false,
                'submit_on_change' => false,
                'visible_values' => [],
                'visible_labels' => [],
                'visible_parent' => null,
                'parse_value'     => false,
            ]
        );

        $resolver->setAllowedTypes('panel', ['boolean', 'string']);
        $resolver->setAllowedTypes('on_click', ['boolean','string', 'array']);
        $resolver->setAllowedTypes('on_change', ['boolean','string']);
        $resolver->setAllowedTypes('on_blur', ['boolean','string']);
        $resolver->setAllowedTypes('on_key_press', ['boolean','string']);
        $resolver->setAllowedTypes('column_attr', ['boolean','array']);
        $resolver->setAllowedTypes('visible_values', ['array']);
        $resolver->setAllowedTypes('visible_labels', ['array']);
        $resolver->setAllowedTypes('submit_on_change', ['boolean']);
        $resolver->setAllowedTypes('parse_value', ['string', 'boolean']);
        $resolver->setAllowedTypes('visible_parent', ['string', 'null']);

        $resolver->setAllowedValues('row_style', ['standard', 'single', 'header', 'collection_column', 'collection', 'hidden', 'transparent', 'multiple_widget','simple_array']);
    }

    /**
     * buildView
     * @param FormView $view
     * @param FormInterface $form
     * @param array $options
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['on_change'] = $options['on_change'];
        $view->vars['on_click'] = $options['on_click'];
        $view->vars['on_blur'] = $options['on_blur'];
        $view->vars['on_key_press'] = $options['on_key_press'];
        $view->vars['panel'] = $options['panel'];
        $view->vars['row_style'] = $options['row_style'];
        $view->vars['column_attr'] = $options['column_attr'];
        $view->vars['submit_on_change'] = $options['submit_on_change'];
        $view->vars['data'] = isset($options['data']) ? $options['data'] : '';
        $view->vars['visible_values'] = $options['visible_values'];
        $view->vars['visible_labels'] = $options['visible_labels'];
        $view->vars['visible_parent'] = $options['visible_parent'];
        $view->vars['parse_value'] = $options['parse_value'];
    }
}