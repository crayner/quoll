<?php
/**
 * Created by PhpStorm.
 *
* Quoll
 * (c) 2019 Craig Rayner <craig@craigrayner.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * User: craig
 * Date: 12/12/2019
 * Time: 07:29
 */

namespace App\Form\Extension;

use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class ReactChoiceTypeExtension
 *
 * Allows the react choice to auto manage a choice list.
 * @package App\Form\Extension
 */
class ReactChoiceTypeExtension extends AbstractTypeExtension
{
    /**
     * getExtendedTypes
     * @return array|iterable
     */
    public static function getExtendedTypes(): iterable
    {
        return [
            ChoiceType::class,
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
                'auto_refresh'          => false,
                'auto_refresh_url'      => null,
                'add_url'               => null,
                'chained_child'         => null,
                'chained_values'        => [],
                'visible_by_choice'     => false,
            ]
        );

        $resolver->setAllowedTypes('auto_refresh', ['boolean']);
        $resolver->setAllowedTypes('auto_refresh_url', ['null','string']);
        $resolver->setAllowedTypes('add_url', ['null','string','array']);
        $resolver->setAllowedTypes('chained_child', ['null','string']);
        $resolver->setAllowedTypes('chained_values', ['array']);
        $resolver->setAllowedTypes('visible_by_choice', ['boolean','string','array']);
    }

    /**
     * buildView
     * @param FormView $view
     * @param FormInterface $form
     * @param array $options
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['auto_refresh'] = $options['auto_refresh'];
        $view->vars['auto_refresh_url'] = $options['auto_refresh_url'];
        $view->vars['add_url'] = $options['add_url'];
        $view->vars['chained_child'] = $options['chained_child'];
        $view->vars['chained_values'] = $options['chained_values'];
        $view->vars['visible_by_choice'] = $options['visible_by_choice'];
    }
}