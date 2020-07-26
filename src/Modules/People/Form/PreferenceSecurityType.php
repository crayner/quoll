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
 * Date: 26/07/2020
 * Time: 11:16
 */
namespace App\Modules\People\Form;

use App\Form\Type\ReactFormType;
use App\Modules\Security\Entity\SecurityUser;
use App\Modules\System\Entity\Locale;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class SecurityPreferenceType
 * @package App\Modules\People\Form
 * @author Craig Rayner <craig@craigrayner.com>
 */
class PreferenceSecurityType extends AbstractType
{
    /**
     * buildForm
     * @param FormBuilderInterface $builder
     * @param array $options
     * 26/07/2020 11:20
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('locale', EntityType::class,
                [
                    'label' => 'Personal Language',
                    'class' => Locale::class,
                    'help' => 'Override the system default language.',
                    'placeholder' => 'System Default',
                    'choice_label' => 'name',
                    'query_builder' => function(EntityRepository $er) {
                        return $er->createQueryBuilder('l')
                            ->where('l.active = :yes')
                            ->setParameter('yes', true)
                            ->andWhere('l.systemDefault <> :yes')
                            ->orderBy('l.code', 'ASC')
                        ;
                    },
                    'required' => false,
                ]
            )
        ;
    }

    /**
     * configureOptions
     * @param OptionsResolver $resolver
     * 26/07/2020 11:17
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'translation_domain' => 'Security',
                'data_class' => SecurityUser::class,
                'row_style' => 'transparent',
            ]
        );
    }
}
