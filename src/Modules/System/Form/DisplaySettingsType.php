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
 * Time: 14:33
 */
namespace App\Modules\System\Form;

use App\Form\Type\ReactFileType;
use App\Form\Type\ReactFormType;
use App\Form\Type\SimpleArrayType;
use App\Modules\System\Manager\SettingFactory;
use App\Validator\ReactImage;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class DisplaySettingsType
 * @package App\Modules\System\Form
 */
class DisplaySettingsType extends AbstractType
{
    /**
     * buildForm
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('systemSettings', SettingsType::class,
                [
                    'settings' => [
                        [
                            'scope' => 'System',
                            'name' => 'mainMenuCategoryOrder',
                            'entry_type' => SimpleArrayType::class,
                        ],
                        [
                            'scope' => 'System',
                            'name' => 'organisationLogo',
                            'entry_type' => ReactFileType::class,
                            'entry_options' => [
                                'file_prefix' => 'org_logo',
                                'empty_data' => SettingFactory::getSettingManager()->getSetting('System','organisationLogo', '/build/static/DefaultLogo.png'),
                                'constraints' => [
                                    new ReactImage(['minWidth' => 400, 'maxWidth' => 400, 'minHeight' => 100, 'maxHeight' => 100]),
                                ],
                                'required' => false,
                            ],
                        ],
                        [
                            'scope' => 'System',
                            'name' => 'organisationBackground',
                            'entry_type' => ReactFileType::class,
                            'entry_options' => [
                                'file_prefix' => 'org_bg',
                                'required' => false,
                                'empty_data' => SettingFactory::getSettingManager()->getSetting('System','organisationBackground', '/build/static/backgroundPage.jpg'),
                                'constraints' => [
                                    new ReactImage(['maxSize' => '750k', 'minWidth' => '1500', 'minHeight' => '1200']),
                                ],
                            ],
                        ],
                    ],
                ]
            )
            ->add('submit', SubmitType::class);
    }

    /**
     * configureOptions
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'translation_domain' => 'System',
                'data_class' => null,
            ]
        );
    }

    /**
     * getParent
     * @return string|null
     */
    public function getParent()
    {
        return ReactFormType::class;
    }
}