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
 * Date: 6/04/2020
 * Time: 09:41
 */
namespace App\Modules\People\Manager\Hidden;

use App\Manager\PaginationSortInterface;
use App\Modules\People\Entity\FamilyMemberAdult;
use App\Modules\People\Pagination\FamilyAdultsPagination;
use App\Provider\ProviderFactory;
use App\Util\ErrorMessageHelper;

/**
 * Class FamilyAdultSort
 * @package App\Modules\People\Manager\Hidden
 */
class FamilyAdultSort implements PaginationSortInterface
{
    /**
     * @var FamilyMemberAdult
     */
    private $source;

    /**
     * @var FamilyMemberAdult
     */
    private $target;

    /**
     * @var FamilyAdultsPagination
     */
    private $pagination;

    /**
     * @var array
     */
    private $details = [];

    /**
     * @var array
     */
    private $content = [];

    /**
     * ScaleGradeSort constructor.
     *
     * @param FamilyMemberAdult $source
     * @param FamilyMemberAdult $target
     * @param FamilyAdultsPagination $pagination
     */
    public function __construct(FamilyMemberAdult $source, FamilyMemberAdult $target, FamilyAdultsPagination $pagination)
    {
        $this->source = $source;
        $this->target = $target;
        $this->pagination = $pagination;
        $this->details = [];

        if (!$this->source->getFamily()->isEqualTo($this->target->getFamily()))
        {
            $this->details = ErrorMessageHelper::getInvalidInputsMessage($this->details, true);
            return;
        }

        $provider = ProviderFactory::create(FamilyMemberAdult::class);

        $content = $provider->getRepository()->findBy(['family' => $source->getFamily()->getId()], ['contactPriority' => 'ASC']);

        $key = 1;
        $result = [];
        foreach($content as $q => $item)
        {
            if ($item === $source)
                continue;
            if ($item === $target) {
                $source->setContactPriority($key++);
                $result[] = $source;
            }
            $item->setContactPriority($key++);
            $result[] = $item;
        }

        $this->details = $provider->saveAdults($result, $this->details);
        $this->content = $result;

    }

    /**
     * @return array
     */
    public function getDetails(): array
    {
        if ($this->details['status'] === 'success') {
            $this->details['content'] = $this->pagination->setContent($this->content)->toArray()['content'];
        }
        return $this->details;
    }

    /**
     * Details.
     *
     * @param array $details
     * @return $this
     */
    public function setDetails(array $details)
    {
        $this->details = $details;
        return $this;
    }

    /**
     * @return array
     */
    public function getContent(): array
    {
        return $this->content;
    }

    /**
     * Content.
     *
     * @param array $content
     * @return FamilyAdultSort
     */
    public function setContent(array $content): FamilyAdultSort
    {
        $this->content = $content;
        return $this;
    }
}