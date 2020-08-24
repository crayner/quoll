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
 * Date: 5/12/2019
 * Time: 15:22
 */

namespace App\Modules\People\Manager;

use App\Manager\SpecialInterface;
use App\Manager\StatusManager;
use App\Modules\People\Entity\Family;
use App\Modules\People\Entity\FamilyMemberCareGiver;
use App\Modules\People\Entity\FamilyMemberStudent;
use App\Modules\People\Entity\FamilyRelationship;
use App\Provider\ProviderFactory;
use App\Util\TranslationHelper;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\Util\StringUtil;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Class FamilyRelationshipManager
 * @package App\Modules\People\Manager
 */
class FamilyRelationshipManager implements SpecialInterface
{
    /**
     * @var ValidatorInterface
     */
    private ValidatorInterface $validator;

    /**
     * @var array
     */
    private array $form;

    /**
     * @var Family
     */
    private Family $family;

    /**
     * @var StatusManager
     */
    private StatusManager $statusManager;

    /**
     * FamilyRelationshipManager constructor.
     * @param ValidatorInterface $validator
     * @param StatusManager $statusManager
     */
    public function __construct(
        ValidatorInterface $validator,
        StatusManager $statusManager
    ) {
        $this->validator = $validator;
        $this->statusManager = $statusManager;
    }

    /**
     * handleRequest
     * @param array $content
     * @param Family $family
     * @param FormInterface $form
     */
    public function handleRequest(array $content, Family $family, FormInterface $form)
    {
        $this->setFamily($family);

        $provider = ProviderFactory::create(FamilyRelationship::class);
        $relationships = [];
        if (!key_exists('relationships', $content)) {
            $this->getStatusManager()->error(StatusManager::INVALID_INPUTS);
            return;
        }
        foreach($content['relationships'] as $q=>$item)
        {
            $fr = $provider->findOneRelationship($item);
            $fr->setFamily($family)
                ->setCareGiver($provider->getRepository(FamilyMemberCareGiver::class)->find($item['careGiver']))
                ->setStudent($provider->getRepository(FamilyMemberStudent::class)->find($item['student']))
                ->setRelationship($item['relationship'])
            ;

            $errors = $this->getValidator()->validate($fr);
            if ($errors->count() > 0)
            {
                $this->getStatusManager()->error(StatusManager::INVALID_INPUTS);
                $error = $errors->get(0);
                $form->get('relationships')->get($q)->get('relationship')->addError(new FormError($error->getMessage()));
                return;
            }
            $relationships[] = $fr;
        }

        foreach($relationships as $fr) {
            ProviderFactory::create(FamilyRelationship::class)->persistFlush($fr, false);
        }
        ProviderFactory::create(FamilyRelationship::class)->flush();
        $this->getStatusManager()->success();
    }

    /**
     * @return ValidatorInterface
     */
    public function getValidator(): ValidatorInterface
    {
        return $this->validator;
    }

    /**
     * getRelationships
     * @param Family|null $family
     * @return Collection
     */
    public function getRelationships(?Family $family = null): Collection
    {
        if (!$family)
            $family = $this->getFamily();
        $careGivers = FamilyManager::getCareGivers($family, false);
        $students = FamilyManager::getStudents($family, false);
        $relationships = $this->getExistingRelationships($family);
        if (count($careGivers) * count($students) === $relationships->count())
            return $relationships;

        foreach($careGivers as $careGiver)
            foreach($students as $student)
            {
                $relationship = new FamilyRelationship($family, $careGiver, $student);
                $save = true;
                foreach($relationships as $item) {
                    if ($relationship->isEqualTo($item)) {
                        $save = false;
                        break;
                    }
                }
                if ($save) $relationships->add($relationship);
            }

        return $relationships;
    }

    /**
     * getExistingRelationships
     *
     * 21/08/2020 08:06
     * @param Family $family
     * @return ArrayCollection
     * @noinspection PhpUndefinedMethodInspection
     */
    private function getExistingRelationships(Family $family): ArrayCollection
    {
        $result = ProviderFactory::getRepository(FamilyRelationship::class)->findByFamily($family);
        $result = new ArrayCollection($result);
        return $result;
    }

    /**
     * getName
     * @return string
     */
    public function getName(): string
    {
        return StringUtil::fqcnToBlockPrefix(static::class) ?: '';
    }

    /**
     * toArray
     * @return array
     */
    public function toArray(): array
    {
        $form = $this->getForm();
        $form['attr'] = [];
        return [
            'form' => $form,
            'name' => $this->getName(),
            'messages' => $this->getTranslations(),
            'relationships' => $this->getRelationshipsAsArray(),
        ];
    }

    /**
     * getRelationshipsAsArray
     *
     * 21/08/2020 09:03
     * @return array
     */
    private function getRelationshipsAsArray(): array
    {
        $result = [];
        foreach($this->getRelationships($this->getFamily())->toArray() as $relationship)
            $result[] = $relationship->toArray('form');

        return $result;
    }

    /**
     * @return array
     */
    public function getForm(): array
    {
        return $this->form;
    }

    /**
     * Form.
     *
     * @param array $form
     * @return FamilyRelationshipManager
     */
    public function setForm(array $form): FamilyRelationshipManager
    {
        $this->form = $form;
        return $this;
    }

    /**
     * getTranslations
     * @return array
     */
    private function getTranslations(): array
    {
        return [
            'Relationships' => TranslationHelper::translate('Relationships', [], 'People'),
            'loadingContent' => TranslationHelper::translate('Let me ponder your request', [], 'messages'),
        ];
    }

    /**
     * @return Family
     */
    public function getFamily(): Family
    {
        return $this->family;
    }

    /**
     * Family.
     *
     * @param Family $family
     * @return FamilyRelationshipManager
     */
    public function setFamily(Family $family): FamilyRelationshipManager
    {
        $this->family = $family;
        return $this;
    }

    /**
     * @return StatusManager
     */
    public function getStatusManager(): StatusManager
    {
        return $this->statusManager;
    }
}