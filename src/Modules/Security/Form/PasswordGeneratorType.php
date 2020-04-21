<?php
/**
 * Created by PhpStorm.
 *
* Quoll
 * (c) 2019 Craig Rayner <craig@craigrayner.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * User: craig
 * Date: 2/09/2019
 * Time: 16:13
 */

namespace App\Modules\Security\Form;

use App\Modules\System\Entity\Setting;
use App\Provider\ProviderFactory;
use App\Util\TranslationHelper;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class PasswordGeneratorType
 * @package App\Modules\Security\Form
 */
class PasswordGeneratorType extends AbstractType
{
    /**
     * getParent
     * @return string|null
     */
    public function getParent()
    {
        return PasswordType::class;
    }

    /**
     * configureOptions
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $provider = ProviderFactory::create(Setting::class);
        $resolver->setDefault('generateButton', [
            'title' => TranslationHelper::translate('Generate', [], 'Security'),
            'class' => 'button generatePassword -ml-px button-right',
            'passwordPolicy' => [
                'alpha' => $provider->getSettingByScopeAsBoolean('System', 'passwordPolicyAlpha'),
                'numeric' => $provider->getSettingByScopeAsBoolean('System', 'passwordPolicyNumeric'),
                'punctuation' => $provider->getSettingByScopeAsBoolean('System', 'passwordPolicyNonAlphaNumeric'),
                "minLength" => $provider->getSettingByScopeAsInteger('System', 'passwordPolicyMinLength'),
            ],
            'onClick' => 'generateNewPassword',
            'alertPrompt' => TranslationHelper::translate('Copy this password if required', [],'Security'),
        ]);
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['generateButton'] = $options['generateButton'];
    }
}