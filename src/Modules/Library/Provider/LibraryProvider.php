<?php
/**
 * Created by PhpStorm.
 *
 * kookaburra
 * (c) 2019 Craig Rayner <craig@craigrayner.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * User: craig
 * Date: 18/11/2019
 * Time: 11:57
 */
namespace App\Modules\Library\Provider;

use App\Manager\EntityInterface;
use App\Modules\Library\Entity\Library;
use App\Modules\People\Entity\Person;
use App\Provider\AbstractProvider;
use Symfony\Component\Form\FormInterface;

/**
 * Class LibraryProvider
 * @package App\Modules\Library\Provider
 * @author Craig Rayner <craig@craigrayner.com>
 */
class LibraryProvider extends AbstractProvider
{
    /**
     * @var string
     */
    protected $entityName = Library::class;

    /**
     * findPeopleFormIdentifierReport
     * @param FormInterface $form
     * @return array
     */
    public function findPeopleFormIdentifierReport(FormInterface $form): array
    {
        $borrowerType = $form->get("borrowerType")->getData();
        $rollGroup = $form->get("rollGroup")->getData();

        switch ($borrowerType) {
            case 'Student':
                if ($rollGroup === null)
                    return [];
                return $this->getRepository(Person::class)->findStudentsByRollGroup($rollGroup, 'surname');
            case 'Staff':
                return $this->getRepository(Person::class)->findCurrentStaff();
            case 'Parent':
                return $this->getRepository(Person::class)->findCurrentParents();
            case 'Other':
                return $this->getRepository(Person::class)->findOthers();
        }
        return [];
    }

    /**
     * persistFlush
     * @param EntityInterface $entity
     * @param array $data
     * @param bool $flush
     * @return array
     * 8/06/2020 10:27
     */
    public function persistFlush(EntityInterface $entity, array $data = [], bool $flush = true): array
    {
        if ($entity->isMain()) {
            $main = $this->findOneBy(['main' => true]);
            if ($main) {
                if ($main->getId() !== $entity->getId()) {
                    $main->setMain(false);
                    $data = parent::persistFlush($main, $data, false);
                }
            }
        }
        return parent::persistFlush($entity, $data, $flush);
    }
}