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
 * Date: 10/09/2019
 * Time: 14:29
 */

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class DisplayType
 * @package App\Form\Type
 */
class DisplayType extends AbstractType
{
    /**
     * getParent
     * @return string|null
     */
    public function getParent()
    {
        return HiddenType::class;
    }

    /**
     * getBlockPrefix
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'display';
    }

    /**
     * buildView
     * @param FormView $view
     * @param FormInterface $form
     * @param array $options
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        if (!$options['mapped'])
            $view->vars['value'] = $options['data'];
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('required', false);
    }
}