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
 * Date: 3/01/2020
 * Time: 12:34
 */
namespace App\Modules\School\Form;

use App\Form\Type\HeaderType;
use App\Form\Type\ReactFormType;
use App\Modules\People\Entity\Person;
use App\Modules\School\Entity\YearGroup;
use App\Provider\ProviderFactory;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class YearGroupType
 * @package App\Modules\School\Form
 * @author Craig Rayner <craig@craigrayner.com>
 */
class YearGroupType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $personRepository = ProviderFactory::getRepository(Person::class);
        $builder
            ->add('ygheader', HeaderType::class,
                [
                    'label' => $options['data']->getId() === null ? 'Add Year Group' : 'Edit Year Group',
                ]
            )
            ->add('name', TextType::class,
                [
                    'label' => 'Name',
                    'help' => 'Must be unique',
                    'translation_domain' => 'messages',
                ]
            )
            ->add('abbreviation', TextType::class,
                [
                    'label' => 'Abbreviation',
                    'help' => 'Must be unique',
                    'translation_domain' => 'messages',
                ]
            )
            ->add('sequenceNumber', HiddenType::class,
                [
                    'data' => intval($options['data']->getSequenceNumber()) > 0 ? $options['data']->getSequenceNumber() : YearGroup::getNextSequence(),
                ]
            )
            ->add('headOfYear', EntityType::class,
                [
                    'label' => 'Head of Year',
                    'required' => false,
                    'class' => Person::class,
                    'choice_label' => 'fullNameReversed',
                    'placeholder' => ' ',
                    'choice_translation_domain' => false,
                    'query_builder' => $personRepository->getStaffQueryBuilder(),
                ]
            )
            ->add('submit', SubmitType::class)
        ;
    }

    /**
     * configureOptions
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'translation_domain' => 'School',
                'data_class' => YearGroup::class,
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