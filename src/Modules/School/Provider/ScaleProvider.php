<?php
/**
 * Created by PhpStorm.
 *
* Quoll
 * (c) 2020 Craig Rayner <craig@craigrayner.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * User: craig
 * Date: 10/01/2020
 * Time: 07:58
 */

namespace App\Modules\School\Provider;

use App\Modules\School\Entity\Scale;
use App\Modules\School\Entity\ScaleGrade;
use App\Modules\School\Repository\ScaleRepository;
use App\Provider\AbstractProvider;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Class ScaleProvider
 * @package App\Modules\School\Provider
 */
class ScaleProvider extends AbstractProvider
{
    /**
     * @var ArrayCollection
     */
    private $scaleList;

    /**
     * @var string
     */
    protected $entityName = Scale::class;

    /**
     * canDelete
     * @param Scale $scale
     * @return bool
     */
    public function canDelete(Scale $scale)
    {
        if ($this->getRepository(ScaleGrade::class)->countScaleUse($scale) === 0)
            return true;
        return false;
    }

    /**
     * findOneByAndStore
     * @param string $name
     * @param $key
     * @param array|null $orderBy
     * @return Scale|ScaleRepository|null
     */
    public function findOneByAndStore(string $name, $key, ?array $orderBy = null)
    {
        $criteria = [$name => $key];
        $scale = $this->getScaleFromList($name,$key) ?: $this->addScaleList($name,$key,$this->getRepository()->findOneBy($criteria,$orderBy));
        return $scale;
    }

    /**
     * @return ArrayCollection
     */
    private function getScaleList(): ArrayCollection
    {
        if (null === $this->scaleList) {
            $this->scaleList = new ArrayCollection();
        }
        return $this->scaleList;
    }

    /**
     * getScaleFromList
     * @param string $name
     * @param $key
     */
    private function getScaleFromList(string $name, $key): ?Scale
    {
        if (!$this->getScaleList()->containsKey($name)) {
            return null;
        }
        if (!$this->getScaleList()->get($name)->containsKey($key)) {
            return null;
        }
        return $this->getScaleList()->get($name)->get($key);
    }

    /**
     * ScaleList.
     *
     * @param string $name
     * @param $key
     * @param $entity
     * @return ScaleRepository
     */
    private function addScaleList(string $name, $key, $entity): Scale
    {
        if (!$this->getScaleList()->containsKey($name)) {
            $this->scaleList->set($name, new ArrayCollection());
        }
        $list = $this->scaleList->get($name);
        if ($list->containsKey($key)) {
            return $entity;
        }
        $list->set($key, $entity);
        return $entity;
    }

}