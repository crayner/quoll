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
 * Date: 10/01/2020
 * Time: 07:58
 */
namespace App\Modules\Assess\Provider;

use App\Modules\Assess\Entity\Scale;
use App\Modules\Assess\Entity\ScaleGrade;
use App\Provider\AbstractProvider;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Class ScaleProvider
 * @package App\Modules\Assess\Provider
 * @author Craig Rayner <craig@craigrayner.com>
 */
class ScaleProvider extends AbstractProvider
{
    /**
     * @var ArrayCollection|null
     */
    private ?ArrayCollection $scaleList = null;

    /**
     * @var string
     */
    protected string $entityName = Scale::class;

    /**
     * canDelete
     *
     * 16/08/2020 15:00
     * @param Scale $scale
     * @return bool
     */
    public function canDelete(Scale $scale): bool
    {
        if ($this->getRepository(ScaleGrade::class)->countScaleUse($scale) === 0)
            return true;
        return false;
    }

    /**
     * findOneByAndStore
     *
     * 17/08/2020 15:36
     * @param string $name
     * @param $key
     * @return Scale|null
     */
    public function findOneByAndStore(string $name, $key): ?Scale
    {
        $criteria = [$name => $key];
        $scale = $this->getScaleFromList($name,$key) ?: $this->addScaleList($name, $key, $this->getRepository()->findOneBy($criteria));
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
     *
     * 17/08/2020 15:37
     * @param string $name
     * @param $key
     * @return Scale|null
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
     * addScaleList
     *
     * 17/08/2020 15:37
     * @param string $name
     * @param $key
     * @param $entity
     * @return Scale
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
