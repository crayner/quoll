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

use App\Modules\System\Exception\SettingNotFoundException;
use App\Modules\System\Manager\SettingFactory;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
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
                'translation_domain' => 'Setting'
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
                'value',
                'setting_type',
            ]
        );
        $resolver->setDefaults(
            [
                'entry_type' => TextType::class,
                'entry_options' => [],
                'class' => null,
                'method' => null,
            ]
        );
        $resolver->setAllowedTypes('scope', 'string');
        $resolver->setAllowedTypes('name', 'string');
        $resolver->setAllowedTypes('entry_type', 'string');
        $resolver->setAllowedTypes('entry_options', 'array');
        $resolver->setAllowedTypes('setting_type', 'string');


        $manager = SettingFactory::getSettingManager();
        if (!$manager->has($setting['scope'], $setting['name'])) {
            throw new SettingNotFoundException($setting['scope'], $setting['name']);
        }
        $setting['value'] = $manager->get($setting['scope'], $setting['name']);
        $setting['setting_type'] = $manager->getSettingType($setting['scope'], $setting['name']);
        $setting['class'] = $manager->getSettingClass($setting['scope'], $setting['name']);
        $setting['method'] = $manager->getSettingMethod($setting['scope'], $setting['name']);
        return $resolver->resolve($setting);
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
            $data = $setting['value'];
            if ($setting['setting_type'] === 'boolean') {
                $data = $data ? '1' : '0';
            }

            if ($setting['setting_type'] === 'enum') {
                $setting['entry_options']['choice_list_class'] = $setting['class'];
                $setting['entry_options']['choice_list_method'] = $setting['method'];
            }

            $builder->add($name, $setting['entry_type'], array_merge(
                    [
                        'data' => $data,
                        'label' => $setting['scope'] . '.' . $setting['name'] . '.name',
                        'help' => $setting['scope'] . '.' . $setting['name'] . '.description',
                        'required' => false,
                        'setting_form' => true,
                    ],
                    $setting['entry_options'])
            );
        }
    }
}