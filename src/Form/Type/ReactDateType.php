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
 * Date: 7/11/2019
 * Time: 11:00
 */
namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class ReactDateType
 *
 * 17/10/2020 08:40
 * @package App\Form\Type
 * @author Craig Rayner <craig@craigrayner.com>
 */
class ReactDateType extends AbstractType
{
    /**
     * getParent
     * @return string|null
     */
    public function getParent()
    {
        return DateType::class;
    }

    /**
     * configureOptions
     *
     * 17/10/2020 08:40
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'widget' => 'single_text',
                'input' => 'datetime_immutable',
                'help' => 'Format dd/mm/yyyy',
            ]
        );
    }
}