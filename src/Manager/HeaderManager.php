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
 * Date: 21/02/2020
 * Time: 08:52
 */
namespace App\Manager;

use App\Manager\Hidden\WarningItem;
use App\Modules\School\Util\AcademicYearHelper;
use App\Twig\MainMenu;
use App\Util\ImageHelper;
use App\Util\TranslationHelper;
use App\Util\UrlGeneratorHelper;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Class HeaderManager
 * @package App\Manager
 * @author Craig Rayner <craig@craigrayner.com>
 */
class HeaderManager
{
    /**
     * @var Request
     */
    private $request;

    /**
     * @var AuthorizationCheckerInterface
     */
    private $checker;

    /**
     * @var TokenStorageInterface
     */
    private $storage;

    /**
     * @var MainMenu
     */
    private $mainMenu;

    /**
     * HeaderManager constructor.
     * @param Request $request
     * @param AuthorizationCheckerInterface $checker
     * @param TokenStorageInterface $storage
     * @param MainMenu $mainMenu
     */
    public function __construct(Request $request, AuthorizationCheckerInterface $checker, TokenStorageInterface $storage, MainMenu $mainMenu)
    {
        $this->request = $request;
        $this->checker = $checker;
        $this->mainMenu = $mainMenu;
        $this->storage = $storage;
    }

    /**
     * toArray
     * @return array
     */
    public function toArray(): array
    {
        $this->setTranslations();
        return [
            'homeURL' => UrlGeneratorHelper::getUrl('home'),
            'organisationName' => $this->getRequest()->getSession()->get('organisationName', 'Quoll'),
            'organisationLogo' => ImageHelper::getLogoImage(),
            'menu' => $this->getMainMenu(),
            'translations' => TranslationHelper::getTranslations(),
        ];
    }

    /**
     * @return Request
     */
    public function getRequest(): Request
    {
        return $this->request;
    }

    /**
     * Checks if the attributes are granted against the current authentication token and optionally supplied subject.
     *
     * @param $attributes
     * @param null $subject
     * @return bool
     */
    protected function isGranted($attributes, $subject = null): bool
    {
        if ($this->storage->getToken() === null)
            return false;
        return $this->checker->isGranted($attributes, $subject);
    }

    /**
     * getMainMenu
     * @return array
     */
    private function getMainMenu(): array
    {
        if (!$this->isGranted('IS_AUTHENTICATED_FULLY'))
            return [];

        $this->mainMenu->execute();

        if ($this->mainMenu->isValid() && $this->mainMenu->hasAttribute('menuMainItems'))
            return $this->mainMenu->getAttribute('menuMainItems') ?: [];

        return [];
    }

    /**
     * setTranslations
     * @return $this
     */
    private function setTranslations(): self
    {
        TranslationHelper::addTranslation('Home', [], 'messages');
        TranslationHelper::addTranslation('Kookaburra', [], 'messages');
        return $this;
    }

    /**
     * getWarnings
     *
     * 25/09/2020 15:53
     * @return array
     */
    public static function getWarnings(): array
    {
        $result = [];
        if (AcademicYearHelper::getCurrentAcademicYear()->getStatus() !== 'Current') {
            $warning = new WarningItem();
            $result['academicYear'] = $warning->setTitle(TranslationHelper::translate('The academic year {name} is not the current academic year.', ['{name}' => AcademicYearHelper::getCurrentAcademicYear()->getName()], 'School'))
                ->setLabel(TranslationHelper::translate('Academic Year: {name}', ['{name}' => AcademicYearHelper::getCurrentAcademicYear()->getName()], 'School'))
                ->setLink(UrlGeneratorHelper::getUrl('preferences'))
                ->toArray()
            ;
        }
        return $result;
    }
}