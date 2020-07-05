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
 * Date: 3/09/2019
 * Time: 14:05
 */

namespace App\Modules\System\Form;

use App\Modules\System\Manager\SettingFactory;
use App\Provider\ProviderFactory;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Exception\MissingOptionsException;

/**
 * Class SettingsType
 * @package App\Modules\System\Form
 */
class SettingsType extends AbstractType
{
    /**
     * getParent
     * @return string|null
     */
    public function getParent()
    {
        return FormType::class;
    }

    /**
     * configureOptions
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'row_style' => 'transparent',
                'mapped' => false,
                'data_class' => null,
                'settings' => [],
                'panel' => false,
            ]
        );
    }

    /**
     * configureSetting
     * @param array $setting
     * @return array
     */
    private function configureSetting(array $setting): array
    {
        $resolver = new OptionsResolver();
        $resolver->setRequired(
            [
                'scope',
                'name',
                'setting',
            ]
        );
        $resolver->setDefaults(
            [
                'entry_type' => TextType::class,
                'entry_options' => [],
            ]
        );
        $resolver->setAllowedTypes('scope', 'string');
        $resolver->setAllowedTypes('name', 'string');
        $resolver->setAllowedTypes('entry_type', 'string');
        $resolver->setAllowedTypes('entry_options', 'array');
        $resolver->setAllowedTypes('setting', ['boolean', Setting::class]);

        $setting['setting'] = SettingFactory::getSettingManager()->getSettingByScope($setting['scope'], $setting['name'], true);
        $setting = $resolver->resolve($setting);
        if (false === $setting['setting'])
            throw new InvalidOptionsException(sprintf('The setting %s - %s was not found in the database.',$setting['scope'], $setting['name']));
        return $setting;
    }

    /**
     * buildForm
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if (count($options['settings']) === 0)
            throw new MissingOptionsException('The Settings have not been created.', $options);

        foreach($options['settings'] as $setting) {
            $setting = $this->configureSetting($setting);
            $name = str_replace(' ', '_', $setting['scope'].'__'.$setting['name']);
            $data = $setting['entry_type'] === ChoiceType::class && isset($setting['entry_options']['multiple']) && $setting['entry_options']['multiple'] ? explode(',',$setting['setting']->getValue()) : $setting['setting']->getValue();

            if ($setting['entry_type'] === 'App\Modules\System\Form\SettingCollectionType' && empty($setting['setting']->getValue()))
                $data = [];
            if ($setting['entry_type'] === 'App\Form\Type\SimpleArrayType') {
                if (empty($setting['setting']->getValue()))
                    $data = [];
                else
                    $data = explode(',', $setting['setting']->getValue());
            }

            if ($setting['entry_type'] === EntityType::class) {
                $entityName = $setting['entry_options']['class'];
                $data = $setting['setting']->getValue() ? ProviderFactory::getRepository($entityName)->find($setting['setting']->getValue()) : null;
            }

            $builder->add($name, $setting['entry_type'], array_merge(
                [
                    'data' => $data,
                    'help' => $setting['setting']->getDescription(),
                    'label' => $setting['setting']->getNameDisplay(),
                    'required' => false,
                    'setting_form' => true,
                ],
                $setting['entry_options'])
            );
        }
    }
}