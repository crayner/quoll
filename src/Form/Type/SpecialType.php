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
 * Date: 29/10/2020
 * Time: 10:08
 */
namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class SpecialType
 *
 * 29/10/2020 10:09
 * @package App\Form\Type
 * @author Craig Rayner <craig@craigrayner.com>
 */
class SpecialType extends AbstractType
{
    /**
     * configureOptions
     *
     * 29/10/2020 10:10
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults(
                [
                    'special_data' => [],
                    'row_style' => 'special',
                ]
            )
            ->setRequired(
                [
                    'special_name',
                ]
            )
        ;
    }

    /**
     * buildView
     *
     * 29/10/2020 10:12
     * @param FormView $view
     * @param FormInterface $form
     * @param array $options
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['special_data'] = $options['special_data'];
        $view->vars['special_name'] = $options['special_name'];
    }

    /**
     * getParent
     *
     * 29/10/2020 10:09
     * @return string|null
     */
    public function getParent()
    {
        return FormType::class;
    }
}
