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
 * Date: 25/07/2020
 * Time: 13:06
 */
namespace App\Modules\People\Form;

use App\Form\Type\HeaderType;
use App\Form\Type\ReactFormType;
use App\Modules\People\Entity\Person;
use App\Modules\People\Manager\PersonNameManager;
use App\Modules\System\Form\SettingsType;
use App\Util\TranslationHelper;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class FormatNameSettingType
 * @package App\Modules\People\Form
 * @author Craig Rayner <craig@craigrayner.com>
 */
class FormatNameSettingType extends AbstractType
{
    /**
     * @var string
     */
    private $tabName;

    /**
     * buildForm
     * @param FormBuilderInterface $builder
     * @param array $options
     * 25/07/2020 13:16
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->tabName = $options['tabName'];
        $builder
            ->add('formatNameHeader', HeaderType::class,
                [
                    'label' => 'Format Name {type}',
                    'label_translation_parameters' => ['{type}' => TranslationHelper::translate($this->tabName, [], 'People')],
                    'help' => 'format_name_help',
                    'help_translation_parameters' => ['items' => '['.implode('],[',PersonNameManager::getNameParts()).']'],
                ]
            )
            ->add('formatName', SettingsType::class,
                [
                    'settings' => $this->getSettings($options['person']),
                ]
            )
            ->add('submit', SubmitType::class)
        ;
    }

    /**
     * configureOptions
     * @param OptionsResolver $resolver
     * 25/07/2020 13:07
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults(
                [
                    'translation_domain' => 'People',
                    'data_class' => null,
                ]
            )
        ;
        $resolver
            ->setRequired(
                [
                    'tabName',
                    'person',
                ]
            )
        ;
    }

    /**
     * getParent
     * @return string|null
     * 25/07/2020 13:07
     */
    public function getParent()
    {
        return ReactFormType::class;
    }

    /**
     * getSettings
     * @param Person $person
     * @return array
     * 25/07/2020 13:53
     */
    private function getSettings(Person $person): array
    {
        $settings = [];
        foreach(PersonNameManager::getStyleList() as $style)
        {
            $settings[] = [
                'scope' => 'People',
                'name' => 'formatName' . $this->tabName . $style,
                'entry_options' => [
                    'help_translation_parameters' => ['{name}' => PersonNameManager::formatName($person, $this->tabName, $style)],
                ],
            ];
        }
        return $settings;
    }

    /**
     * @return string
     */
    public function getTabName(): string
    {
        return $this->tabName;
    }

    /**
     * @param string $tabName
     * @return FormatNameSettingType
     */
    public function setTabName(string $tabName): FormatNameSettingType
    {
        $this->tabName = $tabName;
        return $this;
    }

    /**
     * buildView
     * @param FormView $view
     * @param FormInterface $form
     * @param array $options
     * 25/07/2020 14:42
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['unique_block_prefix'] = $this->getBlockPrefix() . '_' .  $this->getTabName();
    }
}
