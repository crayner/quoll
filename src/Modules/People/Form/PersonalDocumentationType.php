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
 * Date: 20/07/2020
 * Time: 11:14
 */
namespace App\Modules\People\Form;

use App\Form\Type\EnumType;
use App\Form\Type\HeaderType;
use App\Form\Type\ReactDateType;
use App\Form\Type\ReactFileType;
use App\Form\Type\ReactFormType;
use App\Modules\People\Entity\PersonalDocumentation;
use App\Modules\System\Manager\SettingFactory;
use App\Util\ParameterBagHelper;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CountryType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\LanguageType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Intl\Countries;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class PersonalDocumentationType
 * @package App\Modules\People\Form
 * @author Craig Rayner <craig@craigrayner.com>
 */
class PersonalDocumentationType extends AbstractType
{
    /**
     * buildForm
     * @param FormBuilderInterface $builder
     * @param array $options
     * 20/07/2020 11:23
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $person = $options['data']->getPerson();
        $builder
            ->add('docoHeader', HeaderType::class,
                [
                    'label' => 'Personal Documentation',
                ]
            )
            ->add('personalImage', ReactFileType::class,
                [
                    'label' => 'Personal Image',
                    'help' => 'Maximum size of 750kB. Width 240-720px, Height 320-960px, Ratio 0.7/1 - 0.84/1 (Portrait)',
                    'file_prefix' => 'personal_' . $person->getSurname(),
                    'image_method' => 'getPersonalImage',
                    'entity' => $options['data'],
                    'required' => false,
                    'delete_route' => $options['remove_personal_image'],
                ]
            )
            ->add('languageFirst', LanguageType::class,
                [
                    'label' => 'First Language',
                    'placeholder' => ' ',
                    'required' => false,
                    'preferred_choices' => ParameterBagHelper::get('preferred_languages'),
                ]
            )
            ->add('languageSecond', LanguageType::class,
                [
                    'label' => 'Second Language',
                    'placeholder' => ' ',
                    'required' => false,
                    'preferred_choices' => ParameterBagHelper::get('preferred_languages'),
                ]
            )
            ->add('languageThird', LanguageType::class,
                [
                    'label' => 'Third Language',
                    'placeholder' => ' ',
                    'required' => false,
                    'preferred_choices' => ParameterBagHelper::get('preferred_languages'),
                ]
            )
            ->add('dob', ReactDateType::class,
                [
                    'label' => 'Date of Birth',
                    'input' => 'datetime_immutable',
                    'required' => false,
                ]
            )
            ->add('countryOfBirth', CountryType::class,
                [
                    'label' => 'Country of Birth',
                    'placeholder' => ' ',
                    'required' => false,
                    'alpha3' => true,
                    'preferred_choices' => ParameterBagHelper::get('preferred_countries'),
                ]
            )
            ->add('birthCertificateScan', ReactFileType::class,
                [
                    'label' => 'Birth Certificate Scan',
                    'help' => 'The scan can be an image or a pdf, up to 2MB in size.',
                    'file_prefix' => 'dob_cert_' . $person->getSurname(),
                    'image_method' => 'getBirthCertificateScan',
                    'entity' => $options['data'],
                    'required' => false,
                    'delete_route' => $options['remove_birth_certificate_scan'],
                ]
            )
            ->add('ethnicity', EnumType::class,
                [
                    'label' => 'Ethnicity',
                    'required' => false,
                    'placeholder' => ' ',
                ]
            )
            ->add('religion', EnumType::class,
                [
                    'label' => 'Religion',
                    'required' => false,
                ]
            )
            ->add('citizenship1', CountryType::class,
                [
                    'label' => 'Citizenship',
                    'help' => 'This citizenship will be used by the school if/when required.',
                    'placeholder' => ' ',
                    'alpha3' => true,
                    'required' => false,
                    'preferred_choices' => ParameterBagHelper::get('preferred_countries'),
                ]
            )
            ->add('citizenship1Passport', TextType::class,
                [
                    'label' => 'Citizenship Passport Number',
                    'required' => false,
                ]
            )
            ->add('citizenship1PassportScan', ReactFileType::class,
                [
                    'label' => 'Citizenship 1 Passport Scan',
                    'help' => 'The scan can be an image or a pdf, up to 2MB in size.',
                    'required' => false,
                    'file_prefix' => 'passport_' . $person->getSurname(),
                    'image_method' => 'getCitizenship1PassportScan',
                    'entity' => $options['data'],
                    'delete_route' => $options['remove_passport_scan'],
                ]
            )
            ->add('citizenship2', CountryType::class,
                [
                    'label' => '2nd Citizenship',
                    'placeholder' => ' ',
                    'alpha3' => true,
                    'required' => false,
                    'preferred_choices' => ParameterBagHelper::get('preferred_countries'),
                ]
            )
            ->add('citizenship2Passport', TextType::class,
                [
                    'label' => '2nd Citizenship Passport Number',
                    'required' => false,
                ]
            )
            ->add('nationalIDCardNumber', TextType::class,
                [
                    'label' => '{National} Identification Card Number',
                    'label_translation_parameters' => ['{National}' => Countries::getAlpha3Name(SettingFactory::getSettingManager()->get('System','country', 'AUS'))],
                    'required' => false,
                ]
            )
            ->add('nationalIDCardScan', ReactFileType::class,
                [
                    'label' => '{National} Identification Card Scan',
                    'label_translation_parameters' => ['{National}' => Countries::getAlpha3Name(SettingFactory::getSettingManager()->get('System','country', 'AUS'))],
                    'help' => 'The scan can be an image or a pdf, up to 2MB in size.',
                    'required' => false,
                    'file_prefix' => 'id_card_' . $person->getSurname(),
                    'image_method' => 'getNationalIDCardScan',
                    'entity' => $options['data'],
                    'delete_route' => $options['remove_id_card_scan'],
                ]
            )
            ->add('residencyStatus', TextType::class,
                [
                    'label' => '{National} Residency/Visa Type',
                    'label_translation_parameters' => ['{National}' => Countries::getAlpha3Name(SettingFactory::getSettingManager()->get('System','country', 'AUS'))],
                    'required' => false,
                ]
            )
            ->add('visaExpiryDate', DateType::class,
                [
                    'label' => '{National} Visa Expiry Date',
                    'label_translation_parameters' => ['{National}' => Countries::getAlpha3Name(SettingFactory::getSettingManager()->get('System','country', 'AUS'))],
                    'required' => false,
                    'input' => 'datetime_immutable',
                ]
            )
            ->add('submit', SubmitType::class)
        ;
    }

    /**
     * configureOptions
     * @param OptionsResolver $resolver
     * 20/07/2020 11:21
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'translation_domain' => 'People',
                'data_class' => PersonalDocumentation::class,
            ]
        );

        $resolver->setRequired(
            [
                'remove_birth_certificate_scan',
                'remove_passport_scan',
                'remove_id_card_scan',
                'remove_personal_image'
            ]
        );
    }

    /**
     * getParent
     * @return string|null
     * 20/07/2020 11:20
     */
    public function getParent()
    {
        return ReactFormType::class;
    }
}
