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
 * Date: 13/08/2019
 * Time: 10:45
 */

namespace App\Form\Type;

use App\Form\EventSubscriber\ReactCollectionSubscriber;
use App\Util\TranslationHelper;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\Exception\OptionDefinitionException;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class ReactCollectionType
 * @package App\Form\Type
 */
class ReactCollectionType extends AbstractType
{
    /**
     * configureOptions
     *
     * element_id_name  is used to inject a hidden form element based on the unique id
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(
            [
                'element_delete_route',
            ]
        );
        $resolver->setDefaults(
            [
                'element_id_name' => 'id',
                'element_delete_options' => ['__id__' => 'id'],
                'header_row' => false,
                'column_count' => false,
            ]
        );

        $resolver->setAllowedTypes('header_row', ['boolean', 'array']);
        $resolver->setAllowedTypes('column_count', ['boolean', 'integer']);
    }

    /**
     * getParent
     * @return string|null
     */
    public function getParent()
    {
        return CollectionType::class;
    }

    /**
     * getBlockPrefix
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'react_collection';
    }

    /**
     * buildForm
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventSubscriber(new ReactCollectionSubscriber($options));
    }

    /**
     * buildView
     * @param FormView $view
     * @param FormInterface $form
     * @param array $options
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $options['name'] = $form->getName();
        $child = clone $form;
        while ($options['translation_domain'] === null && ! $child->isRoot()) {
            $child = $child->getParent();
            $options['translation_domain'] = $child->getConfig()->getOption('translation_domain');
        }
        if ($options['translation_domain'] === null)
            $options['translation_domain'] = 'messages';
        $view->vars['allow_add'] = $options['allow_add'];
        $view->vars['allow_delete'] = $options['allow_delete'];
        $view->vars['element_delete_route'] = $options['element_delete_route'];
        $view->vars['element_delete_options'] = $options['element_delete_options'];
        $view->vars['header_row'] = $this->checkHeaderRow($options);
        $view->vars['label'] = $options['label'];
        $view->vars['column_count'] = $options['column_count'];
    }

    /**
     * checkHeaderRow
     * @param $headerRow
     */
    private function checkHeaderRow(array $options)
    {
        if (is_bool($options['header_row']))
            return $options['header_row'];

        if (empty($options['header_row']))
            throw new OptionDefinitionException('The header row must not be empty when set as an array. It requires one row per column header.');

        $headerRow = $options['header_row'];

        foreach($headerRow as $q=>$w) {
            $resolver = new OptionsResolver();
            $resolver->setRequired(
                [
                    'label',
                ]
            );
            $resolver->setDefaults(
                [
                    'help' => null,
                    'attr' => [],
                    'label_translation_parameters' => [],
                    'help_translation_parameters' => [],
                    'translation_domain' => null,
                ]
            );

            $resolver->setAllowedTypes('label', 'string');
            $resolver->setAllowedTypes('attr', 'array');
            $resolver->setAllowedTypes('label_translation_parameters', 'array');
            $resolver->setAllowedTypes('help_translation_parameters', 'array');
            $resolver->setAllowedTypes('help', ['null','string']);
            $resolver->setAllowedTypes('translation_domain', ['null','string']);

            $w = $resolver->resolve($w);
            $w['label'] = TranslationHelper::translate($w['label'], $w['label_translation_parameters'], $w['translation_domain'] ?: $options['translation_domain']);
            if ($w['help'] !== null)
                $w['help'] = TranslationHelper::translate($w['help'], $w['help_translation_parameters'], $w['translation_domain'] ?: $options['translation_domain']);
            $headerRow[$q]['label'] = $w['label'];
            $headerRow[$q]['attr'] = $w['attr'];
            $headerRow[$q]['help'] = $w['help'];
        }
        return $headerRow;
    }
}