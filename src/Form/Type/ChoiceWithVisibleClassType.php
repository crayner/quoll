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
 * Date: 24/12/2019
 * Time: 17:15
 */

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class ChoiceWithVisibleClassType
 * @package App\Form\Type
 */
class ChoiceWithVisibleClassType extends AbstractType
{
    public function getParent()
    {
        return ChoiceType::class;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                // Translations Prefix
                'visible_by_choice' => false,
                'visibleWhen' => null,
                'values' => [],
            ]
        );
        $resolver->setAllowedTypes('visible_by_choice', ['boolean', 'string']);
        $resolver->setAllowedTypes('visibleWhen', ['string','null']);
    }

    /**
     * buildView
     * @param FormView $view
     * @param FormInterface $form
     * @param array $options
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['visible_by_choice'] = $options['visible_by_choice'];
        $view->vars['visibleWhen'] = $options['visibleWhen'];
        $view->vars['values'] = $options['values'];
    }
}
