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
 * Date: 11/12/2019
 * Time: 13:21
 */
namespace App\Modules\People\Provider;

use App\Modules\People\Entity\Contact;
use App\Modules\People\Entity\Locality;
use App\Modules\People\Entity\Family;
use App\Modules\People\Entity\Person;
use App\Provider\AbstractProvider;
use Symfony\Component\Form\ChoiceList\View\ChoiceView;

/**
 * Class LocalityProvider
 * @package App\Modules\People\Provider
 */
class LocalityProvider extends AbstractProvider
{

    /**
     * @var string
     */
    protected $entityName = Locality::class;

    /**
     * countUsage
     * @param Locality $Locality
     * @return int
     */
    public function countUsage(Locality $Locality): int
    {
        $result = $this->getRepository(Contact::class)->countLocalityUse($Locality);
        $result += $this->getRepository(Family::class)->countLocalityUse($Locality);
        return $result;
    }

    /**
     * canDelete
     * @param Locality|null $Locality
     * @return bool
     */
    public function canDelete(?Locality $Locality = null): bool
    {
        $Locality = $Locality ?: $this->getEntity();
        return $this->countUsage($Locality) === 0;
    }

    /**
     * buildChoiceList
     * @return array
     */
    public function buildChoiceList(): array
    {
        $result = [];
        foreach($this->getRepository()->buildChoiceList() as $locality) {
            $result[] = new ChoiceView($locality,$locality['id'],$locality['name']);
        }
        return $result;
    }
}

