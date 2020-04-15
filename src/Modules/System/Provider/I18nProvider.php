<?php
/**
 * Created by PhpStorm.
 *
 * bilby
 * (c) 2019 Craig Rayner <craig@craigrayner.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * User: craig
 * Date: 1/07/2019
 * Time: 10:04
 */

namespace App\Modules\System\Provider;

use App\Manager\EntityInterface;
use App\Manager\Traits\EntityTrait;
use App\Provider\EntityProviderInterface;
use App\Util\GlobalHelper;
use App\Modules\System\Entity\I18n;
use App\Modules\People\Util\UserHelper;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * Class I18nProvider
 * @package App\Modules\System\Provider
 */
class I18nProvider implements EntityProviderInterface
{
    use EntityTrait;

    /**
     * @var string
     */
    private $entityName = I18n::class;

    /**
     * @var string|null
     */
    private $datePHPFormat;

    /**
     * setLanguageSession
     * @param SessionInterface $session
     * @param array $criteria
     * @param bool $defaultLanguage
     * @todo Legacy Removal
     */
    public function setLanguageSession(SessionInterface $session, array $criteria = ['systemDefault' => 'Y'], $defaultLanguage = true)
    {
        $result = $this->getRepository()->findOneBy($criteria);
        if (!$result instanceof I18n)
            $result = $this->getRepository()->findOneBy(['systemDefault' => 'Y']);

        $result->setDefaultLanguage($defaultLanguage);

        $session->set('i18n', $result);

        // Legacy stuff that need to be interpreted from entity.
        /*
        $data = [];
        $data['gibboni18nID'] = $result->getId();
        $data['id'] = $result->getId();
        $data['code'] = $result->getCode();
        $data['name'] = $result->getName();
        $data['dateFormat'] = $result->getDateFormat();
        $data['dateFormatRegEx'] = $result->getDateFormatRegEx();
        $data['dateFormatPHP'] = $result->getDateFormatPHP();
        $data['rtl'] = $result->getRtl();

        if ($defaultLanguage) {
            $data['default']['code'] = $result->getCode();
            $data['default']['name'] = $result->getName();
        }
        
        $session->set('i18n', $data);
        */
    }

    /**
     * selectI18n
     * @return array
     * @throws \Exception
     */
    public function selectI18n(): array
    {
        $result = [];
        foreach($this->findBy(['active' => 'Y'],['code' => 'ASC']) as $i18n)
            if ($i18n->isInstalled())
                $result[$i18n->getName()] = $i18n->getId();

        return $result;
    }

    /**
     * getDatePHPFormat
     */
    public function getDatePHPFormat()
    {
        if (null === $this->datePHPFormat)
        {
            $person = UserHelper::getCurrentUser();
            $i18n = $person->getI18nPersonal() ?: $this->getRepository()->findOneBy(['code' => GlobalHelper::hasParam('locale') ? GlobalHelper::getParam('locale', 'en_GB') : 'en_GB']);
            $this->datePHPFormat = $i18n ? $i18n->getDateFormatPHP() : $this->getRepository()->findOneBy(['code' => 'en_GB'])->getDateFormatPHP();
        }

        return $this->datePHPFormat ?: 'd M/Y';
    }

    /**
     * getSelectedLanguages
     * @return array
     */
    public function getSelectedLanguages(): array
    {
        $result = [];
        foreach($this->getRepository()->findByActive() as $lang)
            $result[$lang->getName()] = $lang->getId();
        return $result;
    }

    /**
     * isValidLocaleCode
     * @param string $locale
     * @return bool
     */
    public function isValidLocaleCode(string $locale): bool
    {
        return $this->getRepository()->findOneBy(['code' => $locale]) instanceof I18n;
    }
}