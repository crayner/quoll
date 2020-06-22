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
 * Date: 23/06/2020
 * Time: 08:59
 */
namespace App\Form\Extension;

use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;

/**
 * Class SubmitExtension
 * @package App\Form\Extension
 * @author Craig Rayner <craig@craigrayner.com>
 */
class SubmitExtension extends AbstractTypeExtension
{
    /**
     * getExtendedTypes
     * @return iterable
     * 23/06/2020 09:00
     */
    public static function getExtendedTypes(): iterable
    {
        return [
            SubmitType::class,
        ];
    }

    /**
     * buildView
     * @param FormView $view
     * @param FormInterface $form
     * @param array $options
     * 23/06/2020 09:04
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        if ($options['label_class'] === null) {
            $view->vars['label_class'] = 'use_save_button';
        }
    }
}