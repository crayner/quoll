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
 * Date: 10/09/2019
 * Time: 14:45
 */
namespace App\Modules\Comms\Form;

use App\Form\Type\HiddenEntityType;
use App\Modules\Comms\Entity\NotificationEvent;
use App\Modules\Comms\Entity\NotificationListener;
use App\Modules\Comms\Validator\EventListener;
use App\Modules\People\Entity\Person;
use App\Modules\System\Entity\Action;
use App\Provider\ProviderFactory;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Role\RoleHierarchyInterface;

/**
 * Class NotificationListenerType
 * @package App\Modules\Comms\Form
 * @author Craig Rayner <craig@craigrayner.com>
 */
class NotificationListenerType extends AbstractType
{
    /**
     * @var RoleHierarchyInterface
     */
    private $hierarchy;

    /**
     * NotificationListenerType constructor.
     * @param RoleHierarchyInterface $hierarchy
     */
    public function __construct(RoleHierarchyInterface $hierarchy)
    {
        $this->hierarchy = $hierarchy;
    }

    /**
     * buildForm
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $event = $options['event'];
        $action = ProviderFactory::getRepository(Action::class)->findOneByName($event->getAction() ? $event->getAction()->getName() : null);

        $people = [];
        if ($action) {
            $people = ProviderFactory::create(Person::class)->findByRoles($action->getSecurityRoles());
        }

        $allScopes = NotificationListener::getScopeTypeList();
        $eventScopes = $event->getScopes();
        $availableScopes = [];
        foreach($eventScopes as $scope) {
            $key = array_search($scope,$allScopes);
            $availableScopes[$key] = $scope;
        }

        $builder
            ->add('person', EntityType::class,
                [
                    'class' => Person::class,
                    'choice_label' => 'fullNameReversed',
                    'choice_translation_domain' => false,
                    'label' => 'Name',
                    'help' => 'Available only to users with the required permission.',
                    'choices' => $people,
                    'placeholder' => 'Please select...',
                ]
            )
            ->add('scopeType', ChoiceType::class,
                [
                    'label' => 'Scope',
                    'placeholder' => 'Please select...',
                    'choices' => $availableScopes,
                    'chained_child' => 'scopeIdentifier',
                    'chained_values' => NotificationListener::getChainedValues(array_values($availableScopes)),
                ]
            )
            ->add('scopeIdentifier', ChoiceType::class,
                [
                    'label' => 'Scope Choices',
                    'placeholder' => ' ',
                    'choices' => NotificationListener::getChainedValues([]),
                    'choice_translation_domain' => false,
                ]
            )
            ->add('event', HiddenEntityType::class,
                [
                    'class' => NotificationEvent::class,
                ]
            )
            ->add('id', HiddenType::class)
        ;
    }

    /**
     * configureOptions
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(
            [
                'event',
            ]
        );
        $resolver->setDefaults(
            [
                'data_class' => NotificationListener::class,
                'translation_domain' => 'System',
                'constraints' => [
                    new EventListener(),
                ],
            ]
        );
    }
}
